<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        $modify = Action::new('modify', 'Modificar', 'fa fa-edit')
            ->linkToCrudAction(Action::EDIT)
            ->setCssClass('btn btn-secondary');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $modify)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Pedido')
            ->setEntityLabelInPlural('Pedidos')
            ->setDefaultSort(['id' => 'DESC'])
            ->overrideTemplate('crud/index', 'admin/sales/orders.html.twig')
            // ->overrideTemplate('crud/edit', 'admin/product/edit.html.twig')
            ;
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if (Crud::PAGE_INDEX === $responseParameters->get('pageName')) {
            /** @var EntityManagerInterface $em */
            $em = $this->container->get('doctrine')->getManager();
            
            $total_paid = $em->getRepository(Order::class)->count(['state' => 1]);
            $total_pending = $em->getRepository(Order::class)->count(['state' => 0]);
            $total_pending_payment = $em->getRepository(Order::class)->count(['state' => 4]);
            
            $responseParameters->set('total_paid', $total_paid);
            $responseParameters->set('total_pending', $total_pending);
            $responseParameters->set('total_pending_payment', $total_pending_payment);
        }

        return $responseParameters;
    }
    
 
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('reference', 'ID del pedido')->setFormTypeOptions(['attr' => ['readonly' => true]]),
            DateTimeField::new('createdAt', 'Fecha del pedido')->hideOnIndex()->setFormTypeOptions(['attr' => ['readonly' => true]]),
            TextField::new('user.fullName', 'Cliente')->hideOnForm(),
            TextField::new('productSummary', 'Resumen Productos')->onlyOnIndex(),
            MoneyField::new('total', 'Total')->setCurrency('ARS'),
            MoneyField::new('grossProfit', 'Ganancia Bruta')->setCurrency('ARS'),
            MoneyField::new('carrierPrice', 'Costos de envío')->setCurrency('ARS')->setFormTypeOptions(['attr' => ['readonly' => true]]),
            ChoiceField::new('state', 'Estado')->setChoices([
                'No pagado' => 0,
                'Pagado' => 1,
                'En preparación' => 2,
                'Enviado/Retirado' => 3,
                'Pendiente de pago' => 4,
                'Cancelado' => 5,
            ]
            ),
            TextField::new('paymentMethod', 'Método de pago')->hideOnForm()->onlyOnDetail(),
            CollectionField::new('orderDetails', 'Resumen de Productos')
                ->setTemplatePath('admin/field/order_details.html.twig')
                ->onlyOnDetail()
        ];
    }

}
