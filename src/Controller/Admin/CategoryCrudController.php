<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Categoría')
            ->setEntityLabelInPlural('Categorías')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nombre')
                ->setHelp('Titulo identificativo de la categoria (Ej: talles)'),
            ArrayField::new('subcategories', 'Subcategorías')
                ->hideOnForm(),
            CollectionField::new('subcategories', 'Subcategorías')
                ->setHelp('Subcategorias de la categoria (Ej: talle S, talle M, talle L)')
                ->setEntryType(\App\Form\SubcategoryType::class)
                ->showEntryLabel(false)
                ->setFormTypeOptions(['by_reference' => false])
                ->onlyOnForms(),
        ];
    }
}
