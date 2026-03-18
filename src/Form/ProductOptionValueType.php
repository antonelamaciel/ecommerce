<?php

namespace App\Form;

use App\Entity\ProductOptionValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Opción (ej: Rojo, XL, V1)',
                'attr' => ['placeholder' => 'Escribe la opción...']
            ])
            ->add('isAvailable', CheckboxType::class, [
                'label' => 'Disponible',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'row_attr' => ['class' => 'form-check form-switch'],
            ])
            ->add('image', TextType::class, [
                'label' => 'Imagen vinculada (ej: color-rojo.jpg)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nombre del archivo...',
                    'class' => 'variant-image-select'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOptionValue::class,
        ]);
    }
}
