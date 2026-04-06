<?php

namespace App\Controller\Admin;

use App\Entity\Carrier;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

class CarrierCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Carrier::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nombre')
            ->setHelp('Nombre del transportista'),
            TextareaField::new('description', 'Descripción')
            ->setHelp('Especificaciones adicionales del transportista'),
            ChoiceField::new('type', 'Tipo de Envío')
                ->setChoices([
                    'Estándar (Costo Fijo)' => 'standard',
                    'Larga Distancia (Cálcular por CP)' => 'long_distance',
                    'Local / Moto (A convenir)' => 'special',
                ]),

            MoneyField::new('price', 'Precio')
                ->setCurrency('ARS')
                ->setStoredAsCents(false)
                ->setRequired(false)
                ->setHelp('Precio del envío')
                ->setFormTypeOption('attr', ['class' => 'field-carrier-price'])
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Transportista')
            ->setEntityLabelInPlural('Transportistas')
        ;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addHtmlContentToHead(<<<HTML
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const initPriceLogic = function() {
            const typeSelect = document.querySelector('select[name="Carrier[type]"]');
            const priceWrapper = document.querySelector('.field-money');
            
            if (typeSelect && priceWrapper) {
                const togglePrice = () => {
                    const val = typeSelect.value;
                    // Ocultar si es Larga Distancia, Especial/Moto o Retiro
                    if (val === 'long_distance' || val === 'special' || val === 'pickup') {
                        priceWrapper.style.display = 'none';
                    } else {
                        priceWrapper.style.display = 'block';
                    }
                };
                typeSelect.addEventListener('change', togglePrice);
                togglePrice();
            }
        };
        // Un pequeño delay para asegurar que EasyAdmin renderizó el formulario
        setTimeout(initPriceLogic, 500);
    });
</script>
HTML
        );
    }
}
