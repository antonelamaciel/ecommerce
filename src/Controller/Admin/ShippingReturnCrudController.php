<?php

namespace App\Controller\Admin;

use App\Entity\ShippingReturn;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ShippingReturnCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ShippingReturn::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Política de Envío/Devolución')
            ->setEntityLabelInPlural('Políticas de Envío y Devoluciones')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Título'),
            TextField::new('icon', 'Icono Bootstrap bi (ej: bi-truck, bi-box-seam)'),
            TextEditorField::new('content', 'Contenido'),
            BooleanField::new('isPublished', 'Publicada'),
        ];
    }
}
