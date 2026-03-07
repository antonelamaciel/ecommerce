<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Doctrine\ORM\EntityManagerInterface;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        return $actions
            ->add('index', 'detail');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nombre'),
            SlugField::new('slug')->setTargetFieldName('name')->hideOnIndex(),
            
            // --- PORTADA ---
            ImageField::new('image', 'Subir/Cambiar Portada')
                ->setBasePath('uploads/')
                ->setUploadDir('public/uploads/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->onlyOnForms(),

            TextField::new('image_preview', 'Portada actual')
                ->onlyOnForms()
                ->setTemplatePath('admin/fields/banner_image.html.twig')
                ->setFormTypeOption('mapped', false),

            ImageField::new('image', 'Portada')
                ->setTemplatePath('admin/fields/banner_image.html.twig')
                ->hideOnForm(),
            
            // --- GALERÍA ---
            ImageField::new('images', 'Subir muchas imágenes (Selecciona varias con Ctrl)')
                ->setBasePath('uploads/')
                ->setUploadDir('public/uploads/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFormTypeOptions([
                    'multiple' => true,
                    'attr' => ['multiple' => 'multiple']
                ])
                ->onlyOnForms()
                ->setRequired(false),

            TextField::new('images_gallery', 'Fotos actuales en galería')
                ->onlyOnForms()
                ->setTemplatePath('admin/fields/product_gallery.html.twig')
                ->setFormTypeOption('mapped', false),
            
            CollectionField::new('images', 'Galería completa')
                ->setTemplatePath('admin/fields/product_gallery.html.twig')
                ->hideOnForm()->hideOnIndex(),

            TextField::new('subtitle', 'Subtítulo')->hideOnIndex(),
            TextareaField::new('description', 'Descripción')->hideOnIndex(),
            MoneyField::new('price', 'Precio')->setCurrency('ARS'),
            MoneyField::new('oldPrice', 'Precio Tachado (ARS)')
                ->setCurrency('ARS')
                ->setRequired(false)
                ->setHelp('Precio anterior que aparecerá tachado.'),
            AssociationField::new('category', 'Categoría'),
            AssociationField::new('subcategories', 'Subcategorías')
                ->setFormTypeOptions(['by_reference' => false])->hideOnIndex(),
            BooleanField::new('isInHome', 'Lo mas buscado'),
            
            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Opciones del Producto'),
            CollectionField::new('options', 'Talles, Colores o Variantes')
                ->setEntryType(\App\Form\ProductOptionType::class)
                ->setTemplatePath('admin/fields/product_options_detail.html.twig')
                ->allowAdd()
                ->allowDelete()
                ->setHelp('Ej: Escribe "Color: Rojo", y marca si está disponible. Puedes agregar varios.')
                ->hideOnIndex()
                ->setCssClass('padded-options-collection')
        ];
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Producto')
            ->setEntityLabelInPlural('Productos')
            ->setFormThemes(['admin/forms/product_images_theme.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
            ->overrideTemplate('crud/index', 'admin/sales/products.html.twig')
        ;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addHtmlContentToHead(<<<HTML
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const initSubcategories = () => {
            // Usamos selectores más flexibles por si cambia el nombre del formulario
            const catInput = document.querySelector('select[name*="[category]"]');
            const subInput = document.querySelector('select[name*="[subcategories]"]');
            
            if (catInput && subInput && subInput.tomselect) {
                const ts = subInput.tomselect;
                
                // CRÍTICO: Capturamos los valores seleccionados directamente del HTML al cargar
                // Esto asegura que no dependamos del estado interno de TomSelect que puede estar inicializándose
                const originalSelectedValues = Array.from(subInput.options)
                    .filter(opt => opt.selected)
                    .map(opt => opt.value);

                const loadSubcategories = async (catId, isInitial = false) => {
                    if (!catId) {
                        ts.clear();
                        ts.clearOptions();
                        return;
                    }

                    // Determinar qué valores queremos mantener/restaurar
                    let valuesToRestore = isInitial ? originalSelectedValues : ts.getValue();
                    if (!Array.isArray(valuesToRestore)) {
                        valuesToRestore = valuesToRestore ? [valuesToRestore] : [];
                    }

                    try {
                        const response = await fetch('/admin/ajax/subcategories/' + catId);
                        const data = await response.json();
                        
                        // Limpiamos y cargamos las nuevas opciones filtradas por categoría
                        ts.clearOptions();
                        ts.addOptions(data);
                        
                        // Restauramos los valores. TomSelect solo mostrará aquellos que estén en 'data'
                        // lo cual es correcto ya que filtramos por categoría.
                        if (valuesToRestore.length > 0) {
                            ts.setValue(valuesToRestore);
                        }
                    } catch (e) {
                        console.error("Error en la carga de subcategorías:", e);
                    }
                };

                catInput.addEventListener('change', function() {
                    // Si el usuario cambia manualmente la categoría, limpiamos las subcategorías anteriores
                    ts.clear();
                    loadSubcategories(this.value, false);
                });

                // Si hay una categoría ya seleccionada (modo edición), cargamos sus subcategorías
                if (catInput.value) {
                    loadSubcategories(catInput.value, true);
                }
            } else if (catInput && subInput && !subInput.tomselect) {
                // Si TomSelect todavía no se ha acoplado al <select>, reintentamos
                setTimeout(initSubcategories, 100);
            }
        };

        // Damos un margen para la inicialización nativa de EasyAdmin
        setTimeout(initSubcategories, 500);
    });
</script>
<style>
    .product-gallery-item img { transition: transform 0.2s; }
    .product-gallery-item:hover img { transform: scale(1.1); z-index: 10; position: relative; }
</style>
HTML
        );
    }
}
