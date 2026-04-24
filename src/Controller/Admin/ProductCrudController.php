<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductCrudController extends AbstractCrudController
{
    private string $uploadDir;

    public function __construct(KernelInterface $kernel)
    {
        $this->uploadDir = $kernel->getProjectDir() . '/public/uploads/';
    }

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Información Básica')
                ->addCssClass('padded-internal-panel'),
            TextField::new('name', 'Nombre')->setRequired(true)
                ->setHelp('Nombre del producto visible en la tienda'),
            SlugField::new('slug')->setTargetFieldName('name')->hideOnForm()->hideOnIndex()->hideOnDetail(),

            // --- PORTADA ---
            ImageField::new('image', 'Subir/Cambiar Portada')
                ->setBasePath('uploads/')
                ->setUploadDir('public/uploads/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->onlyOnForms()
                ->setHelp('Portada del producto visible en la tienda')
                ->setCssClass('mb-3'),

            TextField::new('image_preview', 'Portada actual')
                ->onlyOnForms()
                ->setTemplatePath('admin/fields/banner_image.html.twig')
                ->setFormTypeOption('mapped', false)
                ->setCssClass('mb-3'),

            ImageField::new('image', 'Portada')
                ->setTemplatePath('admin/fields/banner_image.html.twig')
                ->hideOnForm()
                ->setSortable(false),

            // --- GALERÍA (Colección dinámica de EasyAdmin) ---
            Field::new('imagesUpload', 'Galería de imágenes')
                ->setFormType(FileType::class)
                ->setHelp('Galería de imágenes del producto visible en la tienda (Máximo 10 fotos)')
                ->setFormTypeOptions([
                    'multiple' => true,
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'accept' => 'image/*',
                        'id' => 'image-upload-input'
                    ]
                ])
                ->onlyOnForms()
                ->setCssClass('mb-3'),

            TextField::new('gallery_preview', 'Imágenes cargadas actualmente')
                ->onlyOnForms()
                ->setTemplatePath('admin/fields/product_images_gallery.html.twig')
                ->setFormTypeOption('mapped', false)
                ->setCssClass('mb-3'),

            ArrayField::new('images', 'Imágenes cargadas')
                ->hideOnForm()
                ->hideOnIndex()
                ->setTemplatePath('admin/fields/product_images_gallery.html.twig'),

            TextField::new('subtitle', 'Subtítulo')->hideOnIndex()->setRequired(false)
                ->setHelp('Subtítulo del producto visible en la tienda'),
            TextareaField::new('description', 'Descripción')->hideOnIndex()->setRequired(false)
                ->setHelp('Descripción del producto visible en la tienda'),
            AssociationField::new('category', 'Categoría')->setRequired(true)
                ->setHelp('Categoría principal del producto. Ej: INVIERNO, PARTES DE ARRIBA, ACCESORIOS, etc. <br><a href="/admin?crudAction=new&crudControllerFqcn=App\\Controller\\Admin\\CategoryCrudController" class="btn btn-sm btn-primary mt-2 shadow-sm text-white btn-confirm-exit" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none; padding: 8px 20px; margin-bottom: 12px; border-radius: 10px; text-align:center; min-width: 180px;"><i class="fas fa-plus-circle me-2"></i> Crear Nueva Categoría</a>'),
            AssociationField::new('subcategories', 'Subcategorías')
                ->setHelp('Categorias mas especificas del producto. Ej: BUZOS, COLLARES, TOPS, etc.')
                ->setFormTypeOptions(['by_reference' => false])
                ->hideOnIndex(),
            NumberField::new('price', 'Precio (ARS)')->setRequired(true)
                ->setHelp('Precio del producto visible en la tienda (Ej: 1500,50)'),
            NumberField::new('oldPrice', 'Precio Tachado (ARS)')
                ->setHelp('Precio anterior que aparecerá tachado (no obligatorio).')
                ->setSortable(false),

            BooleanField::new('isInHome', 'Producto Destacado')
                ->setHelp('El producto aparecera entre los primeros en la pagina de inicio? si/no.')
                ->setFormTypeOption('disabled', $pageName === Crud::PAGE_INDEX)
                ->setSortable(false),

            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Opciones del Producto'),
            CollectionField::new('options', 'Talles, Colores o Variantes')
                ->setEntryType(\App\Form\ProductOptionType::class)
                ->setTemplatePath('admin/fields/product_options_detail.html.twig')
                ->allowAdd()
                ->allowDelete()
                ->setHelp('Ej: Escribe "Color: Rojo", y marca si está disponible. Puedes agregar varios.')
                ->hideOnIndex()
                ->setCssClass('padded-options-collection'),

            IntegerField::new('stock', 'Cantidad de Stock (opcional)')
                ->setHelp('Unidades disponibles en inventario (no obligatorio).')
                ->setRequired(false)
                ->setHelp('Unidades disponibles en inventario.'),

            \EasyCorp\Bundle\EasyAdminBundle\Field\FormField::addPanel('Administración (Solo Interno)')
                ->setIcon('fas fa-user-shield')
                ->addCssClass('padded-internal-panel'),
            AssociationField::new('supplier', 'Proveedor')
                ->setRequired(false)
                ->hideOnIndex(),
            NumberField::new('purchaseCost', 'Costo de compra (ARS)')
                ->setRequired(false)
                ->setHelp('Costo aproximado de compra para cálculo de ganancias.')
                ->hideOnIndex(),
            DateTimeField::new('purchaseDate', 'Fecha de compra')
                ->setRequired(false)
                ->hideOnIndex(),
        ];
    }

    // ─── CRUD configuration ───────────────────────────────────────────────────

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Producto')
            ->setEntityLabelInPlural('Productos')
            ->setSearchFields(['id', 'name', 'subtitle', 'description', 'price', 'category.name', 'stock'])
            ->overrideTemplate('crud/index', 'admin/sales/products.html.twig')
            ->setFormThemes(['admin/forms/product_images_theme.html.twig', '@EasyAdmin/crud/form_theme.html.twig']);
    }

    private function handleImages($request, Product $product): void
    {
        $files = $request->files->all()['Product']['imagesUpload'] ?? [];
        if (!is_array($files)) {
            $files = [$files];
        }

        $currentImages = $product->getImages() ?? [];

        // Procesar eliminación de imágenes manual
        $deleteImages = $request->request->all()['delete_images'] ?? [];
        if (!empty($deleteImages) && is_array($deleteImages)) {
            $currentImages = array_filter($currentImages, function ($img) use ($deleteImages) {
                return !in_array($img, $deleteImages);
            });
            // Eliminar archivos físicos
            foreach ($deleteImages as $imgToDelete) {
                if (is_string($imgToDelete) && file_exists($this->uploadDir . $imgToDelete)) {
                    @unlink($this->uploadDir . $imgToDelete);
                }
            }
        }


        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $fileName = bin2hex(random_bytes(10)) . '.' . $file->guessExtension();

                $file->move($this->uploadDir, $fileName);

                $currentImages[] = $fileName;
            }
        }

        $product->setImages(array_values($currentImages));
    }

    public function persistEntity(EntityManagerInterface $em, $entity): void
    {
        if ($entity instanceof Product) {
            $this->handleImages($this->getContext()->getRequest(), $entity);
        }

        parent::persistEntity($em, $entity);
    }

    public function updateEntity(EntityManagerInterface $em, $entity): void
    {
        if ($entity instanceof Product) {
            $this->handleImages($this->getContext()->getRequest(), $entity);
        }

        parent::updateEntity($em, $entity);
    }



    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile('assets/js/image-preview.js')
            ->addHtmlContentToHead(<<<'HTML'
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const initSubcategories = () => {
            const catInput = document.querySelector('select[name*="[category]"]');
            const subInput = document.querySelector('select[name*="[subcategories]"]');
            if (catInput && subInput && subInput.tomselect) {
                const ts = subInput.tomselect;
                const originalSelectedValues = Array.from(subInput.options).filter(opt => opt.selected).map(opt => opt.value);
                const loadSubcategories = async (catId, isInitial = false) => {
                    if (!catId) { ts.clear(); ts.clearOptions(); return; }
                    let valuesToRestore = isInitial ? originalSelectedValues : ts.getValue();
                    if (!Array.isArray(valuesToRestore)) valuesToRestore = valuesToRestore ? [valuesToRestore] : [];
                    try {
                        const response = await fetch('/admin/ajax/subcategories/' + catId);
                        const data = await response.json();
                        ts.clearOptions(); ts.addOptions(data);
                        if (valuesToRestore.length > 0) ts.setValue(valuesToRestore);
                    } catch (e) { console.error(e); }
                };
                catInput.addEventListener('change', function() {
                    ts.clear(); loadSubcategories(this.value, false);
                });
                if (catInput.value) loadSubcategories(catInput.value, true);
            } else if (catInput && subInput && !subInput.tomselect) {
                setTimeout(initSubcategories, 100);
            }
        };

        setTimeout(initSubcategories, 500);

        // ── Image Picker Logic (Visual Dropdown) ──
        const initImagePickers = () => {
            const getImagesFromDOM = () => {
                const images = new Set();
                const allImgs = document.querySelectorAll('img');
                
                allImgs.forEach(img => {
                    const src = img.getAttribute('src');
                    if (!src || src.startsWith('data:')) return;
                    
                    const fileName = src.split('/').pop().split('?')[0];
                    
                    if (fileName.match(/\.(jpg|jpeg|png|webp|gif)$/i)) {
                        if (img.closest('.v-dropdown-menu')) return;
                        if (img.classList.contains('v-trigger-img')) return;
                        
                        if (img.width > 30 || img.height > 30 || img.closest('.product-gallery-container, .ea-img-preview, .d-inline-block')) {
                            images.add(fileName);
                        }
                    }
                });
                return Array.from(images);
            };

            const inputs = document.querySelectorAll('.variant-image-select');
            
            inputs.forEach(input => {
                if (input.dataset.pickerInitialized) return;
                input.dataset.pickerInitialized = 'true';

                input.style.display = 'none';
                const parent = input.parentElement;

                const wrapper = document.createElement('div');
                wrapper.className = 'custom-v-dropdown';
                
                const trigger = document.createElement('button');
                trigger.type = 'button';
                trigger.className = 'v-dropdown-trigger';
                
                const updateTriggerContent = () => {
                    if (input.value) {
                        trigger.innerHTML = `
                            <div class="d-flex align-items-center">
                                <img src="/uploads/${input.value}" class="v-trigger-img">
                                <i class="fas fa-chevron-down ml-auto"></i>
                            </div>
                        `;
                    } else {
                        trigger.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="v-trigger-placeholder"><i class="fas fa-image"></i></div>
                                <span class="v-trigger-text text-muted">Vincular imagen...</span>
                                <i class="fas fa-chevron-down ml-auto"></i>
                            </div>
                        `;
                    }
                };
                updateTriggerContent();
                
                const menu = document.createElement('div');
                menu.className = 'v-dropdown-menu d-none';
                
                const refreshMenu = () => {
                    const currentImages = getImagesFromDOM();
                    menu.innerHTML = '';
                    
                    if (currentImages.length > 0) {
                        const clearOpt = document.createElement('div');
                        clearOpt.className = 'v-dropdown-item v-clear-opt';
                        clearOpt.innerHTML = '<i class="fas fa-times-circle mr-2"></i> Sin imagen';
                        clearOpt.onclick = () => {
                            input.value = '';
                            updateTriggerContent();
                            menu.classList.add('d-none');
                        };
                        menu.appendChild(clearOpt);

                        currentImages.forEach(imgName => {
                            const item = document.createElement('div');
                            item.className = 'v-dropdown-item';
                            if (input.value === imgName) item.classList.add('active');
                            
                            item.innerHTML = `
                                <img src="/uploads/${imgName}" class="v-item-img">
                            `;
                            
                            item.onclick = () => {
                                input.value = imgName;
                                updateTriggerContent();
                                menu.classList.add('d-none');
                            };
                            menu.appendChild(item);
                        });
                    } else {
                        menu.innerHTML = '<div class="p-3 text-muted small">Carga imágenes en la galería para verlas aquí.</div>';
                    }
                };

                wrapper.appendChild(trigger);
                wrapper.appendChild(menu);
                parent.appendChild(wrapper);

                trigger.onclick = (e) => {
                    e.preventDefault();
                    refreshMenu();
                    menu.classList.toggle('d-none');
                };

                document.addEventListener('click', (e) => {
                    if (!wrapper.contains(e.target)) menu.classList.add('d-none');
                });
            });
        };

        setTimeout(initImagePickers, 800);
        document.addEventListener('ea.collection.item-added', () => setTimeout(initImagePickers, 200));
    });
</script>
<style>
    /* ── Enhanced Image Dropdown Styling ── */
    .custom-v-dropdown {
        position: relative;
        width: 100%;
        margin-top: 5px;
    }
    .v-dropdown-trigger {
        width: 100%;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 8px 12px;
        text-align: left;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .v-dropdown-trigger:hover {
        border-color: #3b82f6;
    }
    .v-trigger-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 12px;
    }
    .v-trigger-placeholder {
        width: 50px;
        height: 50px;
        background: #f3f4f6;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        color: #9ca3af;
    }
    .v-trigger-text {
        font-size: 0.9rem;
        font-weight: 500;
        max-width: 200px;
    }
    .v-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        margin-top: 4px;
        max-height: 300px;
        width: auto;
        min-width: 100%;
        display: flex;
        flex-wrap: wrap;
        padding: 5px;
        gap: 5px;
        overflow-y: auto;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .v-dropdown-item {
        padding: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s, background 0.2s;
        border-radius: 4px;
        border: 1px solid transparent;
    }
    .v-dropdown-item:hover {
        background: #eff6ff;
        transform: scale(1.05);
        border-color: #3b82f6;
    }
    .v-dropdown-item.active {
        background: #f0f7ff;
        border-color: #3b82f6;
    }
    .v-item-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
    .v-clear-opt {
        width: 100%;
        color: #dc2626;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 8px !important;
        justify-content: flex-start !important;
        background: #fff5f5;
        border-bottom: 1px solid #fee2e2 !important;
    }
    .v-clear-opt:hover {
        background: #fee2e2;
    }
    @media (max-width: 768px) {
        .content-wrapper,
        .field-form_panel{
            width: 330px !important;
            margin-left: 0 !important;
            padding-left: 0 !important;
        }
    }
</style>
HTML
            );
    }
}
