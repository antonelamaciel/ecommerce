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
            TextField::new('title', 'Título')
            ->setHelp('Titulo que se mostrara en la seccion, por ej: sobre nosotros, nuestra mision, nuestros comienzos, etc.'),
            TextField::new('content', 'Contenido')->onlyOnDetail()->renderAsHtml()->setRequired(false),
            TextEditorField::new('content', 'Contenido')->onlyOnForms()->setRequired(false),
            IntegerField::new('priority', 'Prioridad')->setRequired(false)
            ->setHelp('prioridad 1: se muestra primero, prioridad 2: se muestra segundo, etc.'),
            BooleanField::new('isPublished', 'Publicada')->setRequired(false),
        ];
    }
}