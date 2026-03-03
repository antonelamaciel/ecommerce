<?php

namespace App\Controller\Admin;

use App\Entity\Personalize;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
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
            Field\FormField::addPanel('Información General'),
            Field\TextField::new('companyName', 'Nombre de la empresa'),
            Field\ImageField::new('logo')
                ->setBasePath('uploads/logo/')
                ->setUploadDir('public/uploads/logo/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),

            Field\FormField::addPanel('Ubicación'),
            Field\ChoiceField::new('province', 'Provincia')
                ->setChoices($provinces)
                ->setFormTypeOption('placeholder', 'Selecciona una provincia'),
            
            Field\TextField::new('city', 'Localidad')
                ->setFormTypeOption('attr', [
                    'data-current-city' => true,
                    'placeholder' => 'Selecciona una localidad'
                ]),

            Field\TextField::new('address', 'Dirección Exacta'),

            Field\FormField::addPanel('Branding (Colores)'),
            Field\ColorField::new('primaryColor', 'Color primario'),
            Field\ColorField::new('secondaryColor', 'Color secundario'),
            Field\ColorField::new('tertiaryColor', 'Color terciario'),

            Field\FormField::addPanel('Contacto Principal y Pagos'),
            Field\EmailField::new('email', 'Email de contacto'),
            Field\TextField::new('whatsapp', 'WhatsApp (ej: +549...)'),
            Field\TextField::new('aliasCbu', 'Alias / CBU (para transferencias)')->setRequired(false),

            Field\FormField::addPanel('Redes Sociales'),
            Field\TextField::new('instagram', 'Instagram URL'),
            Field\TextField::new('twitter', 'Twitter / X URL'),
            Field\TextField::new('linkedin', 'LinkedIn URL'),
            Field\TextField::new('tiktok', 'TikTok URL'),
            Field\TextField::new('youtube', 'YouTube URL'),
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
                
                const url = 'https://apis.datos.gob.ar/georef/api/localidades?provincia=' + encodeURIComponent(provinceName) + '&campos=nombre&max=2000&orden=nombre';
                
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        const options = data.localidades.map(l => ({ nombre: l.nombre }));
                        tsCity.clearOptions();
                        tsCity.addOptions(options);
                        if (currentCity) {
                            tsCity.setValue(currentCity);
                        }
                    })
                    .catch(err => console.error("Error cargando localidades:", err));
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
