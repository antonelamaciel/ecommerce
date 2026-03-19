<?php

namespace App\Controller\Admin;

use App\Entity\Bundle;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

class BundleCrudController extends AbstractCrudController
{
    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addHtmlContentToHead('
            <style>
                /* --- SOLUCIÓN DEFINITIVA DE VISIBILIDAD DE DESPLEGABLES --- */

                /* 1. Prevenir que CUALQUIER contenedor padre oculte los hijos */
                .padded-internal-panel, 
                .ea-complex-field-panel, 
                .content-panel, 
                .form-widget,
                .form-group,
                fieldset,
                .row,
                .content-wrapper,
                .main-content,
                section.content { 
                    overflow: visible !important; 
                }

                /* 2. Z-INDEX SUPERIOR PARA TODOS LOS POSIBLES LIBRERÍAS DE SELECT */
                .select2-container, 
                .select2-dropdown,
                .ts-dropdown,
                .ts-wrapper,
                .ea-autocomplete {
                    z-index: 1044 !important;
                }

                .ts-control {
                    z-index: 1 !important;
                }

                .ts-dropdown.single.plugin-dropdown_input.plugin-clear_button {
                    z-index: 1044 !important;
                }

                /* 3. ¡AGRANDAR FÍSICAMENTE EL CONTENEDOR DEL ELEMENTO! */
                /* Al hacer que el campo de asociación sea gigante, el menú siempre tiene espacio y NADA lo puede cortar */
                .field-association {
                    min-height: 230px !important;
                    padding-bottom: 230px !important;
                    position: relative !important;
                    z-index: 999 !important;
                    padding-top: 10px !important;
                }

                @media (max-width: 768px) {
                    .content-wrapper{
                        width: 330px !important;
                        margin-left: 0 !important;
                        margin-right: 0 !important;
                        padding-left: 0 !important;
                        padding-right: 0 !important;
                    }

                    .padded-internal-panel{
                        padding-left: 0 !important;
                        padding-right: 0 !important;
                        margin-left: 0 !important;
                        margin-right: 0 !important;
                    }
                } 

                .content-header{
                z-index: 1200 !important;
                }
                    
            </style>
            
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Forzar que Select2 se adjunte al body de la página en lugar del panel interno
                    if (typeof $ !== "undefined" && $.fn.select2) {
                        setTimeout(function() {
                            $("select[data-widget=\"select2\"], .select2").each(function() {
                                $(this).select2({ dropdownParent: $(document.body) });
                            });
                        }, 500);
                    }
                });
            </script>
        ');
    }



    public static function getEntityFqcn(): string
    {
        return Bundle::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
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
            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Configuración General')->setCssClass('padded-internal-panel')->hideOnDetail(),
            TextField::new('title', 'Título de la promo')
            ->setHelp('Esto te ayudara a identificar la promo en el panel de administración.'),

            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('1. Productos')->setCssClass('padded-internal-panel')->hideOnDetail(),
            ChoiceField::new('type', 'Etiqueta Lateral')
                ->setChoices([
                    'Ninguna' => '',
                    'Oferta sorpresa!' => 'oferta_sorpresa',
                    'Últimos en stock!' => 'ultimos_stock',
                    'Apurate, quedan pocas unidades!' => 'pocas_unidades',
                ])
                ->setHelp('Aparecera del alado del nombre de cada "producto seleccionado".')
                ->setRequired(false),

            \EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField::new('discountPercentage', 'descuento (%)')
                ->setHelp('Aplica un descuento automático a todos los "productos seleccionados". Dejar vacío si no quieres hacer un descuento.')
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
            
            AssociationField::new('products', 'Productos seleccionados (afectados)')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'choice_attr' => function($choice, $key, $value) {
                        // Pasamos la imagen en un atributo data-image para que el JS lo use
                        return ['data-image' => '/uploads/' . ($choice->getImage() ?: 'default.png')];
                    },
                ])
                ->setHelp('Busca los productos a los que deseas aplicar estas etiquetas y descuento.'),

            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('2. Cuenta Regresiva')->setCssClass('padded-internal-panel')->hideOnDetail(),
            TextField::new('countdownTitle', 'Ocasión cuenta regresiva')
                ->setHelp('Ej: Black Friday, Cyber Monday, Promoción Especial. Aparecerá primero en el inicio.')
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
                ->setHelp('Agrega la cantidad de horas que dura la cuenta regresiva. Si cambias este valor, el contador SE REINICIARÁ desde ahora.')
                ->setRequired(false),
            \EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField::new('isPromoActive', 'Cuenta Regresiva Activa')
                ->setHelp('Activa o desactiva el banner de la promo en el inicio.'),

            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('3. Banner Superior (Ancho Total)')->setCssClass('padded-internal-panel')->hideOnDetail(),
            TextField::new('bannerText', 'Texto del Banner')
                ->setHelp('Aparecera justo debajo del menu y se ira desplazando. Ej: ¡Envío GRATIS en compras superiores a $50,000!'),
            \EasyCorp\Bundle\EasyAdminBundle\Field\ColorField::new('bannerColor', 'Color del Banner')
                ->setHelp('Selecciona el color de fondo para el banner.')
                ->setCssClass('wide-color-field'),
            \EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField::new('isBannerActive', 'Banner Activo')
                ->setHelp('Si se activa sera visible para el cliente, sino, no lo sera.'),
        ];
    }
}

