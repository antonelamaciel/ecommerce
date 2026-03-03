<?php

namespace App\Controller\Admin;

use App\Entity\Headers;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class HeadersCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Headers::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Banner')
            ->setEntityLabelInPlural('Banners')
            ->setDefaultSort(['id' => 'DESC'])
            ->setFormThemes(['admin/forms/product_images_theme.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }
    
    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Título'),
            TextareaField::new('content', 'Contenido'),
            TextField::new('btnTitle', 'Texto del botón'),
            TextField::new('btnUrl', 'Enlace del botón'),
            
            ImageField::new('image', 'Subir/Cambiar Banner')
                ->setBasePath('uploads/')
                ->setUploadDir('public/uploads/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->onlyOnForms(),

            TextField::new('image_preview', 'Banner actual')
                ->onlyOnForms()
                ->setFormTypeOption('mapped', false),

            ImageField::new('image', 'Banner')
                ->setTemplatePath('admin/fields/banner_image.html.twig')
                ->hideOnForm(),
        ];
    }
    
}
