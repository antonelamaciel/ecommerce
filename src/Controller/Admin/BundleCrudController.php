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
            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Configuración General')->setCssClass('padded-internal-panel'),
            TextField::new('title', 'Título de la promo')
            ->setHelp('Esto te ayudara a identificar la promo en el panel de administración.'),

            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Productos')->setCssClass('padded-internal-panel'),
            ChoiceField::new('type', 'Etiqueta Lateral')
                ->setChoices([
                    'Ninguna' => '',
                    'Oferta sorpresa!' => 'oferta_sorpresa',
                    'Últimos en stock!' => 'ultimos_stock',
                    'Apurate, quedan pocas unidades!' => 'pocas_unidades',
                ])
                ->setHelp('Aparecera del alado del nombre de cada producto seleccionado.')
                ->setRequired(false),
            ChoiceField::new('topRightBadge', 'Etiqueta Superior Derecha')
                ->setChoices([
                    'Ninguna' => '',
                    'Envío gratis' => 'envio_gratis',
                    'Recomendado' => 'recomendado',
                    'Más vendido' => 'mas_vendido',
                ])
                ->setHelp('Aparecera en la esquina superior derecha de cada producto seleccionado.')
                ->setRequired(false),
            \EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField::new('discountPercentage', 'descuento (%)')
                ->setHelp('Aplica un descuento automático a todos los productos seleccionados. Dejar vacío si no quieres hacer un descuento.')
                ->setRequired(false),
            AssociationField::new('products', 'Productos seleccionados (afectados)')
                ->setFormTypeOptions([
                    'by_reference' => false,
                ])
                ->autocomplete()
                ->setHelp('Busca los productos a los que deseas aplicar esta etiqueta especial.'),

            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Cuenta Regresiva')->setCssClass('padded-internal-panel'),
            TextField::new('countdownTitle', 'Ocasión cuenta regresiva')
                ->setHelp('Ej: Black Friday, Cyber Monday, Promoción Especial. Aparecerá con un emoji de fuego.')
                ->setMaxLength(30)
                ->setFormTypeOptions(['attr' => ['maxlength' => 30]])
                ->setRequired(false),
            TextField::new('countdownDescription', 'Descripción')
                ->setHelp('Aparecerá debajo de la ocasión.')
                ->setMaxLength(150)
                ->setFormTypeOptions(['attr' => ['maxlength' => 150]])
                ->setRequired(false)
                ->hideOnIndex(),
            \EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField::new('countdownHours', 'Tiempo (Horas)')
                ->setHelp('Agrega la cantidad de horas. Si cambias este valor, el contador SE REINICIARÁ desde ahora.')
                ->setRequired(false),
            \EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField::new('isPromoActive', 'Cuenta Regresiva Activa')
                ->setHelp('Activa o desactiva el banner de la promo en el inicio.'),

            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Banner Superior (Ancho Total)')->setCssClass('padded-internal-panel'),
            TextField::new('bannerText', 'Texto del Banner')
                ->setHelp('Ej: ¡Envío GRATIS en compras superiores a $50,000!'),
            \EasyCorp\Bundle\EasyAdminBundle\Field\ColorField::new('bannerColor', 'Color del Banner')
                ->setHelp('Selecciona el color de fondo para el banner.')
                ->setCssClass('wide-color-field'),
            \EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField::new('isBannerActive', 'Banner Activo')
                ->setHelp('Si se activa, aparecerá un cartel de ancho total debajo del contador.'),
        ];
    }
}
