<?php

namespace App\Form;

use App\Entity\OrderDetails;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class OrderDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productObject', EntityType::class, [
                'class' => Product::class,
                'choice_label' => function (Product $product) {
                    return $product->getName() . ' ($' . number_format($product->getPrice() / 100, 2, ',', '.') . ')';
                },
                'choice_attr' => function (Product $product) {
                    $img = $product->getImage() ? '/uploads/' . $product->getImage() : '/assets/img/placeholder.png';
                    return [
                        'data-img' => $img,
                    ];
                },
                'placeholder' => 'Busque o seleccione un producto...',
                'label' => 'Producto',
                'attr' => [
                    'class' => 'select-product-with-img custom-tomselect'
                ]
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Cantidad',
                'attr' => ['min' => 1]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Precio Unitario',
                'help' => 'Se auto-completará si se deja en 0'
            ])
        ;

        // Auto-complete price if not provided
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var OrderDetails $detail */
            $detail = $event->getData();
            if ($detail && $detail->getProductObject()) {
                if (!$detail->getPrice()) {
                    $detail->setPrice($detail->getProductObject()->getPrice());
                }
                if (!$detail->getProduct()) {
                    $detail->setProduct($detail->getProductObject()->getName());
                }
                if (!$detail->getPurchaseCost()) {
                    $detail->setPurchaseCost($detail->getProductObject()->getPurchaseCost());
                }
                $detail->setTotal($detail->getPrice() * $detail->getQuantity());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderDetails::class,
        ]);
    }
}
