<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre de la dirección',
                'attr' => [
                    'placeholder' => 'ej: Casa'
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Nombre'
            ])
            ->add('lastname', TextType::class, [
                'label' =>'Apellido'
            ])
            ->add('company', TextType::class, [
                'label' => 'Empresa (opcional)',
                'required' => false,
            ])
            ->add('address', TextType::class, [
                'label' => 'Dirección postal',
                'attr' => [
                    'placeholder' => 'ej: Calle Falsa 123'
                ]
            ])
            ->add('postal', TextType::class, [
                'label' => 'Código postal'
            ])
            ->add('city', TextType::class, [
                'label' => 'Ciudad'
            ])
            ->add('country', CountryType::class, [
                'label' => 'País'
            ])
            ->add('phone', TelType::class, [
                'label' => 'Teléfono'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Guardar dirección', 
                'attr' => [
                    'class' => 'btn btn-info btn-block mt-2'
                ]
                
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
