<?php

namespace App\Controller\Admin;

use App\Entity\Subcategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SubcategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Subcategory::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nombre de Subcategoría'),
            AssociationField::new('category', 'Categoría Padre')->hideOnForm(),
        ];
    }
}
