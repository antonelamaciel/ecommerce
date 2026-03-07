<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        return $actions
            ->add('index', 'detail')
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ;
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
            
            $responseParameters->set('total_paid', $total_paid);
            $responseParameters->set('total_pending', $total_pending);
        }

        return $responseParameters;
    }
    
 
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID del pedido')->hideOnForm(),
            DateTimeField::new('createdAt', 'Fecha del pedido')->hideOnIndex(),
            TextField::new('user.fullName', 'Cliente')->hideOnForm(),
            MoneyField::new('total')->setCurrency('ARS')->hideOnForm(),
            MoneyField::new('carrierPrice', 'Costos de envío')->setCurrency('ARS'),
            ChoiceField::new('state', 'Estado del pedido')->setChoices([
                'No pagado' => 0,
                'Pagado' => 1,
                'En preparación' => 2,
                'Enviado' => 3,
            ]
            ),
            ArrayField::new('orderDetails', 'Productos comprados')->hideOnIndex()->hideOnForm()
        ];
    }

}
