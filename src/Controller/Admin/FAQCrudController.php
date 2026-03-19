<?php

namespace App\Controller\Admin;

use App\Entity\FAQ;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FAQCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FAQ::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Pregunta Frecuente')
            ->setEntityLabelInPlural('Preguntas Frecuentes')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('question', 'Pregunta'),
            TextEditorField::new('answer', 'Respuesta')->hideOnDetail()->hideOnIndex(),
            TextEditorField::new('answer', 'Respuesta')->hideOnDetail()->hideOnForm()->hideOnIndex(),
            TextField::new('answer', 'Respuesta')->hideOnForm()->hideOnIndex()->renderAsHtml(),
            BooleanField::new('isPublished', 'Publicada')->setHelp('Si esta marcado, esta pregunta se mostrara en la pagina de preguntas frecuentes'),
        ];
    }
}
