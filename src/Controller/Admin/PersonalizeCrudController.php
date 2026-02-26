<?php

namespace App\Controller\Admin;

use App\Entity\Personalize;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;


class PersonalizeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Personalize::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Personalizacion')
            ->setEntityLabelInPlural('Personalizaciones')
            ->setDefaultSort(['id' => 'DESC'])
        ;
    }
    
    
    public function configureFields(string $pageName): iterable
    {
        return [
            Field\TextField::new('companyName', 'Nombre de la empresa'),

            Field\ImageField::new('logo')
                ->setBasePath('uploads/logo/')
                ->setUploadDir('public/uploads/logo/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),

            Field\ColorField::new('primaryColor', 'Color primario'),
            Field\ColorField::new('secondaryColor', 'Color secundario'),
            Field\ColorField::new('tertiaryColor', 'Color terciario'),
        ];
    }
    
}
