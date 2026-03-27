<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

class ReceiptCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Comprobante PDF')
            ->setEntityLabelInPlural('Comprobantes Emitidos')
            ->setPageTitle('index', 'Historial de Comprobantes')
            ->setDefaultSort(['id' => 'DESC'])
            // Hides row actions like Show/Edit/Delete directly to force focused view
            ->showEntityActionsInlined();
    }

    // Filtramos la tabla de Pedidos para que muestre SOLO los que ya generaron un comprobante PDF físico
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->andWhere('entity.receiptFilename IS NOT NULL');
        
        return $qb;
    }

    public function configureActions(Actions $actions): Actions
    {
        // Deshabilitar la edición/borrado aquí, esto es puramente un visor de PDF.
        $actions->disable(Action::NEW, Action::EDIT, Action::DELETE);

        // Crear una acción visual destacada para descargar
        $downloadPdf = Action::new('downloadReceipt', 'Descargar PDF', 'fas fa-file-pdf')
            ->linkToRoute('download_receipt', function (Order $order): array {
                return [
                    'reference' => $order->getReference(),
                ];
            })
            ->setHtmlAttributes(['target' => '_blank'])
            // Agregando estilo al boton en EasyAdmin
            ->addCssClass('text-danger fw-bold');

        return $actions
            ->add(Crud::PAGE_INDEX, $downloadPdf);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnIndex(),
            TextField::new('reference', 'Referencia /ID del pedido'),
            AssociationField::new('user', 'Cliente asociado'),
            TextField::new('paymentMethod', 'Método Reportado')->setRequired(false),
            DateTimeField::new('createdAt', 'Fecha de Generación')->setFormat('dd/MM/yyyy HH:mm'),
            TextField::new('receiptFilename', 'Archivo PDF generado')
                ->formatValue(function ($value) {
                    return '<i class="fas fa-check-circle text-success pe-1"></i> Disponible';
                })
                ->setHtmlAttributes(['class' => 'text-muted small']),
        ];
    }
}
