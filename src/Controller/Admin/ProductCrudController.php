<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        return $actions
            ->add('index', 'detail')
            ;
    }

  
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name','Nombre'),
            SlugField::new('slug')->setTargetFieldName('name'),
            ImageField::new('image', 'Imagen Principal')
                ->setBasePath('uploads/')
                ->setUploadDir('public/uploads/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
            ImageField::new('images', 'Otras Imágenes (hasta 10)')
                ->setBasePath('uploads/')
                ->setUploadDir('public/uploads/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setFormTypeOption('multiple', true)
                ->hideOnIndex()
                ->setRequired(false),
            TextField::new('subtitle', 'Subtítulo'),
            TextareaField::new('description')->hideOnIndex(),
            MoneyField::new('price', 'Precio')->setCurrency('ARS'),
            AssociationField::new('category', 'Categoría'),
            AssociationField::new('subcategories', 'Subcategorías')
                ->setFormTypeOptions(['by_reference' => false])
                ->hideOnIndex(),
            BooleanField::new('isInHome', 'Lo mas buscado')
        ];
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Producto')
            ->setEntityLabelInPlural('Productos')
        ;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addHtmlContentToHead(<<<HTML
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const catInput = document.querySelector('select[name="Product[category]"]');
        const subInput = document.querySelector('select[name="Product[subcategories][]"]');
        
        if (catInput && subInput && subInput.tomselect) {
            const ts = subInput.tomselect;
            
            // Function to load subcategories
            const loadSubcategories = function(catId, initialLoad = false) {
                if (!catId) {
                    ts.clear();
                    ts.clearOptions();
                    return;
                }
                
                fetch('/admin/ajax/subcategories/' + catId)
                    .then(r => r.json())
                    .then(data => {
                        const currentValues = ts.getValue();
                        ts.clearOptions();
                        ts.addOptions(data);
                        if (initialLoad) {
                            ts.setValue(currentValues);
                        }
                    });
            };

            // On change
            catInput.addEventListener('change', function() {
                ts.clear();
                loadSubcategories(this.value, false);
            });
            
            // On load
            if (catInput.value) {
                loadSubcategories(catInput.value, true);
            }
        }
    }, 500);
});
</script>
HTML
        );
    }
}
