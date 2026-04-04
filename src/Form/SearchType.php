<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Subcategory;
use App\Model\Search;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('string', TextType::class, [
                'label' => 'Palabras clave:',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Su búsqueda'
                ]
            ])
            ->add('categories', EntityType::class, [
                'label' => false,
                'required' => false,
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('subcategories', EntityType::class, [
                'label' => false,
                'required' => false,
                'class' => Subcategory::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('sort', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'choices' => [
                    'Seleccionar orden' => 'default',
                    'Precio: Menor a Mayor' => 'price-asc',
                    'Precio: Mayor a Menor' => 'price-desc',
                    'Más nuevo a más viejo' => 'date-desc',
                    'Más viejo a más nuevo' => 'date-asc',
                ],
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }
}