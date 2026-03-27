<?php

namespace App\Form;

use App\Entity\ProductOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ProductOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Título de Variantes (Ej: Colores, Talles, Versión)',
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock (opcional)',
                'required' => false,
                'attr' => [
                    'placeholder' => '∞ (Infinito)',
                    'class' => 'variant-stock-input',
                    'min' => 0
                ]
            ])
            ->add('image', TextType::class, [
                'label' => 'Imagen vinculada (opcional)',
                'required' => false,
                'help' => 'Selecciona la imagen que desea vincular a esta opcion.',
                'attr' => ['class' => 'variant-image-select']
            ])
            ->add('isAvailable', CheckboxType::class, [
                'label' => 'Mostrar este grupo',
                'required' => false,
            ])
            ->add('productOptionValues', CollectionType::class, [
                'entry_type' => ProductOptionValueType::class,
                'label' => 'Opciones hijas (Sub opciones del producto)',
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => ['label' => false],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOption::class,
        ]);
    }
}
