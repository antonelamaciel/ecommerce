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
            SlugField::new('slug')->setTargetFieldName('name'),
            
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
                ->hideOnForm(),

            TextField::new('subtitle', 'Subtítulo'),
            TextareaField::new('description', 'Descripción')->hideOnIndex(),
            MoneyField::new('price', 'Precio')->setCurrency('ARS'),
            AssociationField::new('category', 'Categoría'),
            AssociationField::new('subcategories', 'Subcategorías')
                ->setFormTypeOptions(['by_reference' => false])
                ->hideOnIndex(),
            BooleanField::new('isInHome', 'Lo mas buscado'),
            
            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Opciones del Producto'),
            CollectionField::new('options', 'Talles, Colores o Variantes')
                ->setEntryType(\App\Form\ProductOptionType::class)
                ->allowAdd()
                ->allowDelete()
                ->setHelp('Ej: Escribe "Color: Rojo", y marca si está disponible. Puedes agregar varios.')
                ->hideOnIndex()
        ];
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Producto')
            ->setEntityLabelInPlural('Productos')
            ->setFormThemes(['admin/forms/product_images_theme.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addHtmlContentToHead(<<<HTML
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Lógica de Subcategorías ---
        setTimeout(function() {
            const catInput = document.querySelector('select[name="Product[category]"]');
            const subInput = document.querySelector('select[name="Product[subcategories][]"]');
            
            if (catInput && subInput && subInput.tomselect) {
                const ts = subInput.tomselect;
                const loadSubcategories = function(catId, initialLoad = false) {
                    if (!catId) { ts.clear(); ts.clearOptions(); return; }
                    fetch('/admin/ajax/subcategories/' + catId)
                        .then(r => r.json())
                        .then(data => {
                            const currentValues = ts.getValue();
                            ts.clearOptions(); ts.addOptions(data);
                            if (initialLoad) ts.setValue(currentValues);
                        });
                };
                catInput.addEventListener('change', function() { ts.clear(); loadSubcategories(this.value, false); });
                if (catInput.value) loadSubcategories(catInput.value, true);
            }
        }, 500);
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
