<?php

namespace App\Controller\Admin;

use App\Entity\Bundle;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class BundleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Bundle::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Bundle / Oferta')
            ->setEntityLabelInPlural('Bundles / Ofertas')
            ->setSearchFields(['title', 'type', 'products.name']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Título de la promo'),
            ChoiceField::new('type', 'Etiqueta Lateral (Al lado del nombre)')
                ->setChoices([
                    'Ninguna' => '',
                    'Oferta sorpresa!' => 'oferta_sorpresa',
                    'Últimos en stock!' => 'ultimos_stock',
                    'Apurate, quedan pocas unidades!' => 'pocas_unidades',
                ])
                ->setRequired(false),
            ChoiceField::new('topRightBadge', 'Etiqueta Superior Derecha')
                ->setChoices([
                    'Ninguna' => '',
                    'Envío gratis' => 'envio_gratis',
                    'Recomendado' => 'recomendado',
                    'Más vendido' => 'mas_vendido',
                ])
                ->setRequired(false),
            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Cuenta Regresiva'),
            TextField::new('countdownTitle', 'Ocasión (Título)')
                ->setHelp('Ej: Black Friday, Cyber Monday, Promoción Especial. Aparecerá con un emoji de fuego.')
                ->setMaxLength(30)
                ->setFormTypeOptions(['attr' => ['maxlength' => 30]])
                ->setRequired(false),
            TextField::new('countdownDescription', 'Descripción (Badge)')
                ->setHelp('Aparecerá debajo de la ocasión.')
                ->setMaxLength(150)
                ->setFormTypeOptions(['attr' => ['maxlength' => 150]])
                ->setRequired(false),
            \EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField::new('countdownHours', 'Tiempo (Horas)')
                ->setHelp('Agrega solo la cantidad de horas. El sistema lo convertirá a contador regresivo (hs, min, s).')
                ->setRequired(false),
                
            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Productos'),
            AssociationField::new('products', 'Productos afectados')
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->autocomplete()
                ->setHelp('Busca los productos a los que deseas aplicar esta etiqueta especial.'),
        ];
    }
}
