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
            
            <style>
                .select2-results__option { padding: 0 !important; }
                .hover-surface:hover { background-color: #f8fafc !important; }
                .bg-soft-primary { background-color: rgba(99, 102, 241, 0.1); }
            </style>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    function initUniversalProductPicker() {
                        var select = document.querySelector(".bundle-products-selector select");
                        if (!select) return false;

                        // 1. Mapa Universal Constante: Asegurar que extraemos todas las rutas de imagen del DOM
                        var imageMap = {};
                        var options = Array.from(select.options);
                        options.forEach(function(opt) {
                            if (opt.value) {
                                imageMap[opt.value] = opt.dataset.image || "/assets/images/default-product.png";
                            }
                        });

                        // 2. Soporte para TomSelect (Motor Nativo EasyAdmin 4)
                        if (select.tomselect && !select.dataset.visualTs) {
                            select.dataset.visualTs = "true";
                            var ts = select.tomselect;
                            
                            // Reinyectar IDs en el objeto interno
                            Object.keys(imageMap).forEach(function(key) {
                                if (ts.options[key]) {
                                    ts.updateOption(key, Object.assign({}, ts.options[key], { src: imageMap[key] }));
                                } else {
                                    ts.addOption({ value: key, text: "Producto " + key, src: imageMap[key] });
                                }
                            });

                            ts.settings.render.option = function(data, escape) {
                                var src = data.src || imageMap[data.value] || "/assets/images/default-product.png";
                                return \'<div class="d-flex align-items-center p-3 border-bottom hover-surface">\' +
                                    \'<img src="\' + src + \'" class="rounded-3 shadow-sm border border-black-10 me-3" style="width: 50px; height: 50px; object-fit: cover;">\' +
                                    \'<div class="d-flex flex-column"><span class="fw-bolder text-dark h6 mb-1">\' + escape(data.text) + \'</span></div></div>\';};
                            ts.settings.render.item = function(data, escape) {
                                var src = data.src || imageMap[data.value] || "/assets/images/default-product.png";
                                return \'<div class="d-flex align-items-center gap-2">\' +
                                    \'<img src="\' + src + \'" class="rounded-1" style="width: 24px; height: 24px; object-fit: cover;">\' +
                                    \'<span class="small fw-semibold text-dark">\' + escape(data.text) + \'</span></div>\';
                            };
                            ts.refreshOptions(false);
                            ts.refreshItems();
                            return true;
                        }

                        // 3. Soporte para Select2 (Motor Clásico)
                        if (typeof $ !== "undefined" && $.fn && $.fn.select2 && !select.dataset.visualS2) {
                            select.dataset.visualS2 = "true";
                            var $s = $(select);
                            if ($s.hasClass("select2-hidden-accessible")) $s.select2("destroy");
                            
                            $s.select2({
                                width: "100%",
                                dropdownParent: $(document.body),
                                templateResult: function(product) {
                                    if (!product.id) return product.text;
                                    var src = imageMap[product.id] || "/assets/images/default-product.png";
                                    return $(
                                        \'<div class="d-flex align-items-center p-3 border-bottom hover-surface">\' +
                                        \'<img src="\' + src + \'" class="rounded-3 shadow-sm border border-black-10 me-3" style="width: 50px; height: 50px; object-fit: cover;">\' +
                                        \'<div class="d-flex flex-column"><span class="fw-bolder h6 mb-1 text-dark">\' + product.text + \'</span>\' +
                                        \'<span class="badge bg-soft-primary text-primary px-2 py-1 rounded-pill" style="font-size:0.75rem;"><i class="fas fa-check-circle me-1"></i>Catálogo Completo</span></div></div>\'
                                    );
                                },
                                templateSelection: function(product) {
                                    if (!product.id) return product.text;
                                    var src = imageMap[product.id] || "/assets/images/default-product.png";
                                    return $(\'<div class="d-flex align-items-center gap-2"><img src="\' + src + \'" class="rounded-1" style="width: 24px; height: 24px; object-fit: cover;"><span class="small fw-semibold text-dark">\' + product.text + \'</span></div>\');
                                }
                            });
                            return true;
                        }

                        return false;
                    }

                    // Forzar escaneo constante durante los primeros 10 segundos
                    var attempts = 0;
                    var interv = setInterval(function() {
                        var success = initUniversalProductPicker();
                        if (success || attempts > 40) clearInterval(interv);
                        attempts++;
                    }, 250);
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


            AssociationField::new('products', 'Productos seleccionados (afectados)')
                ->autocomplete(false)
                ->setCssClass('bundle-products-selector')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'choice_attr' => function($choice, $key, $value) {
                        // Pasamos la imagen en un atributo data-image para que el JS lo use
                        return ['data-image' => '/uploads/' . ($choice->getImage() ?: 'default.png')];
                    },
                ])
                ->setHelp('Busca los productos a los que deseas aplicar estas etiquetas y descuento.'),

            \EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField::new('discountPercentage', 'descuento (%)')
                ->setHelp('Aplica un descuento automático a todos los "productos seleccionados". Dejar vacío si no quieres hacer un descuento.')
                ->setRequired(false),
                
            ChoiceField::new('type', 'Etiqueta Lateral')
                ->setChoices([
                    'Ninguna' => '',
                    'Oferta sorpresa!' => 'oferta_sorpresa',
                    'Últimos en stock!' => 'ultimos_stock',
                    'Apurate, quedan pocas unidades!' => 'pocas_unidades',
                ])
                ->setHelp('Aparecerá al lado del nombre de cada "producto seleccionado".')
                ->setRequired(false)
                ->setColumns(6),

            ChoiceField::new('topRightBadge', 'Etiqueta Superior Derecha')
                ->setChoices([
                    'Ninguna' => '',
                    'Envío gratis' => 'envio_gratis',
                    'Recomendado' => 'recomendado',
                    'Más vendido' => 'mas_vendido',
                ])
                ->setHelp('Aparecerá en la esquina superior derecha de cada producto seleccionado.')
                ->setRequired(false)
                ->setColumns(6),
            
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

