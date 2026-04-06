<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class NotificationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->andWhere('entity.isRead = :isRead')
           ->setParameter('isRead', false);
           
        return $qb;
    }

    public function configureActions(Actions $actions): Actions 
    {
        $markAsSeen = Action::new('markAsSeen', 'Visto', 'fa fa-check')
            ->linkToCrudAction('markAsSeen')
            ->setCssClass('btn btn-outline-success btn-sm');

        $markAllAsSeen = Action::new('markAllAsSeen', 'Marcar todas como visto', 'fa fa-check-double')
            ->linkToCrudAction('markAllAsSeen')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-success');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $markAsSeen)
            ->add(Crud::PAGE_INDEX, $markAllAsSeen)
            ->add(Crud::PAGE_DETAIL, $markAsSeen)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }

    public function markAsSeen(AdminContext $context, EntityManagerInterface $em, AdminUrlGenerator $adminUrlGenerator): Response
    {
        /** @var Order $order */
        $order = $context->getEntity()->getInstance();
        $order->setIsRead(true);
        $em->flush();

        $this->addFlash('success', 'Pedido marcado como visto.');

        return $this->redirect($adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
    }

    public function markAllAsSeen(EntityManagerInterface $em, AdminUrlGenerator $adminUrlGenerator): Response
    {
        $unreadOrders = $em->getRepository(Order::class)->findBy(['isRead' => false]);
        
        foreach ($unreadOrders as $order) {
            $order->setIsRead(true);
        }
        
        $em->flush();

        $this->addFlash('success', count($unreadOrders) . ' pedidos marcados como vistos.');

        return $this->redirect($adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Notificación de Pedido')
            ->setEntityLabelInPlural('Notificaciones')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'reference', 'user.firstname', 'user.lastname']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex()->hideOnDetail(),
            TextField::new('reference', 'ID del pedido'),
            DateTimeField::new('createdAt', 'Fecha')->setFormat('short', 'short')->setSortable(true),
            TextField::new('user.fullName', 'Cliente'),
            TextField::new('productSummary', 'Resumen de Productos'),
            MoneyField::new('total', 'Total')->setCurrency('ARS')->setStoredAsCents(false)->setSortable(false),
            MoneyField::new('grossProfit', 'Ganancia Bruta')->setCurrency('ARS')->setStoredAsCents(false)->hideOnForm()->hideOnIndex()->hideOnDetail(),
            ChoiceField::new('state', 'Estado')->setChoices([
                'No pagado' => 0,
                'Pagado' => 1,
                'En preparación' => 2,
                'Enviado/Retirado' => 3,
                'Pendiente de pago' => 4,
                'Cancelado' => 5,
            ]
            ),
            CollectionField::new('orderDetails', 'Detalle de Productos')
                ->setTemplatePath('admin/field/order_details.html.twig')
                ->onlyOnDetail()
        ];
    }
}
