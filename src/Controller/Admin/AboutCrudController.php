<?php

namespace App\Controller\Admin;

use App\Entity\About;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AboutCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return About::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Sección Nosotros')
            ->setEntityLabelInPlural('Secciones Nosotros')
            ->setDefaultSort(['priority' => 'ASC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Título'),
            TextEditorField::new('content', 'Contenido'),
            IntegerField::new('priority', 'Prioridad'),
            BooleanField::new('isPublished', 'Publicada'),
        ];
    }
}
