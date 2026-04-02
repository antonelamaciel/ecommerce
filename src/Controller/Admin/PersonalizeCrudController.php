<?php

namespace App\Controller\Admin;

use App\Entity\Personalize;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;


class PersonalizeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Personalize::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
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
        $provinces = [
            'Buenos Aires' => 'Buenos Aires',
            'Catamarca' => 'Catamarca',
            'Chaco' => 'Chaco',
            'Chubut' => 'Chubut',
            'Ciudad Autónoma de Buenos Aires' => 'Ciudad Autónoma de Buenos Aires',
            'Córdoba' => 'Córdoba',
            'Corrientes' => 'Corrientes',
            'Entre Ríos' => 'Entre Ríos',
            'Formosa' => 'Formosa',
            'Jujuy' => 'Jujuy',
            'La Pampa' => 'La Pampa',
            'La Rioja' => 'La Rioja',
            'Mendoza' => 'Mendoza',
            'Misiones' => 'Misiones',
            'Neuquén' => 'Neuquén',
            'Río Negro' => 'Río Negro',
            'Salta' => 'Salta',
            'San Juan' => 'San Juan',
            'San Luis' => 'San Luis',
            'Santa Cruz' => 'Santa Cruz',
            'Santa Fe' => 'Santa Fe',
            'Santiago del Estero' => 'Santiago del Estero',
            'Tierra del Fuego' => 'Tierra del Fuego',
            'Tucumán' => 'Tucumán',
        ];

        return [
            Field\FormField::addPanel('Información General')->setCssClass('padded-internal-panel')->hideOnDetail(),
            Field\TextField::new('companyName', 'Nombre de la empresa')->setRequired(true),
            Field\TextareaField::new('description', 'Descripción corta de la empresa')
                ->setHelp('Aparecerá en los buscadores de Google (SEO) al compartir la página web.')
                ->setMaxLength(160)
                ->hideOnIndex(),
            Field\ImageField::new('logo', "Logo de la empresa")
                ->setBasePath('uploads/logo/')
                ->setUploadDir('public/uploads/logo/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),

            Field\FormField::addPanel('Ubicación de la empresa')->setCssClass('padded-internal-panel')->hideOnDetail(),
            Field\ChoiceField::new('province', 'Provincia')
                ->setChoices($provinces)
                ->setFormTypeOption('placeholder', 'Busca la provincia de la empresa ...') 
                ->hideOnIndex(),
            
            Field\TextField::new('city', 'Localidad')
                ->setFormTypeOption('attr', [
                    'data-current-city' => true,
                    'placeholder' => 'Busca la localidad de la empresa ...'
                ])
                ->hideOnIndex(),
            Field\TextField::new('postal', 'C.P. (Código Postal)')
                ->hideOnIndex(),    
            Field\TextField::new('address', 'Dirección Exacta del local'),

            Field\FormField::addPanel('Branding (Colores)')->setCssClass('padded-internal-panel')->hideOnDetail(),
            Field\ColorField::new('primaryColor', 'Color claro')
                ->setHelp('Color principal de la empresa, sera el color de fondo del menu y otros items'),
            Field\ColorField::new('tertiaryColor', 'Color oscuro') ->setHelp('Sera el color principal de los textos y botones'),
            Field\ColorField::new('secondaryColor', 'Color del footer') ->setHelp('Sera el color del footer'),

            Field\FormField::addPanel('Contacto Principal de la Empresa y Pagos')->setCssClass('padded-internal-panel')->hideOnDetail(),
            Field\TextField::new('whatsapp', 'WhatsApp de la Empresa (ej: +549...)'),
            Field\EmailField::new('email', 'Email de la Empresa')
                ->hideOnIndex(),
            Field\TextField::new('aliasCbu', 'Alias o CBU')->setRequired(true)->setHelp('Alias o CBU de la cuenta en la que los clientes haran el deposito del dinero.'),

            Field\FormField::addPanel('Redes Sociales de la empresa')->setCssClass('padded-internal-panel')->hideOnDetail(),
            Field\TextField::new('instagram', 'Instagram URL')
                ->hideOnIndex(),
            Field\TextField::new('twitter', 'Twitter / X URL')
                ->hideOnIndex(),
            Field\TextField::new('linkedin', 'LinkedIn URL')
                ->hideOnIndex(),
            Field\TextField::new('tiktok', 'TikTok URL')
                ->hideOnIndex(),
            Field\TextField::new('youtube', 'YouTube URL')
                ->hideOnIndex(),
        ];
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addHtmlContentToHead(<<<HTML
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const provinceSelect = document.querySelector('select[name="Personalize[province]"]');
            const cityInput = document.querySelector('input[name="Personalize[city]"]');
            
            if (!provinceSelect || !cityInput) return;

            // --- 1. Inicializar TomSelect para Localidades ---
            const tsCity = new TomSelect(cityInput, {
                valueField: 'nombre',
                labelField: 'nombre',
                searchField: 'nombre',
                create: true,
                maxItems: 1,
                placeholder: 'Selecciona una localidad...',
                maxOptions: 2000,
                render: {
                    option: function(item, escape) {
                        return '<div>' + escape(item.nombre) + '</div>';
                    }
                }
            });

            const loadCities = function(provinceName, currentCity = '') {
                if (!provinceName) {
                    tsCity.clear();
                    tsCity.clearOptions();
                    return;
                }
                
                const url = 'https://apis.datos.gob.ar/georef/api/localidades?provincia=' + encodeURIComponent(provinceName) + '&campos=nombre,id&max=2000&orden=nombre';
                
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        const options = data.localidades.map(l => ({ 
                            nombre: l.nombre,
                            id: l.id
                        }));
                        tsCity.clearOptions();
                        tsCity.addOptions(options);
                        if (currentCity) {
                            tsCity.setValue(currentCity);
                        }
                    })
                    .catch(err => console.error("Error cargando localidades:", err));
            };

            const postalInput = document.querySelector('input[name="Personalize[postal]"]');

            tsCity.on('change', function(value) {
                if (!value) return;
                const option = tsCity.options[value];
                if (option && option.id && option.id.length >= 4) {
                    // In AR, often the first 4 or some part of the ID correlates to the old CP
                    // But we'll just show it to the user.
                    // If the postal field is empty, we suggest it or just set it if it's clearly a CP
                    // For now, let's just show it in the dropdown rendering first.
                }
            });

            // Re-render to show CP in dropdown
            tsCity.settings.render.option = function(item, escape) {
                const cp = item.id ? ' <small class="text-muted">(ID: ' + escape(item.id) + ')</small>' : '';
                return '<div>' + escape(item.nombre) + cp + '</div>';
            };
            tsCity.settings.render.item = function(item, escape) {
                return '<div>' + escape(item.nombre) + '</div>';
            };

            provinceSelect.addEventListener('change', function() {
                tsCity.clear();
                loadCities(this.value);
            });

            // Carga inicial
            if (provinceSelect.value) {
                loadCities(provinceSelect.value, cityInput.value);
            }

        }, 800);
    });
</script>
<style>
    .ts-control { border-radius: 8px !important; padding: 10px !important; border: 1px solid #d1d5db !important; }
    .ts-dropdown { border-radius: 8px !important; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important; }
</style>
HTML
        );
    }
}
