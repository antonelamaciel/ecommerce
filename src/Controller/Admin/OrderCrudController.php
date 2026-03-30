<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use App\Form\OrderDetailType;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        $modify = Action::new('modify', 'Modificar', 'fa fa-edit')
            ->linkToCrudAction(Action::EDIT)
            ->setCssClass('btn btn-secondary');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $modify)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }

    public function createEntity(string $entityFqcn): Order
    {
        $order = new Order();
        $order->setState(0); // No pagado
        $order->setCarrierName('Retiro en tienda / Presencial');
        $order->setCarrierPrice('0');
        $order->setDelivery('Venta Presencial');
        $order->setPaymentMethod('Efectivo');
        
        // Find 'Presencial' user if exists
        $entityManager = $this->container->get('doctrine')->getManager();
        $presencialUser = $entityManager->getRepository(\App\Entity\User::class)->findOneBy(['firstname' => 'Presencial']);
        if ($presencialUser) {
            $order->setUser($presencialUser);
        }

        return $order;
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Pedido')
            ->setEntityLabelInPlural('Pedidos')
            ->setDefaultSort(['id' => 'DESC'])
            ->overrideTemplate('crud/index', 'admin/sales/orders.html.twig')
            ;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            // Include TomSelect CDN to ensure the search bar and product images work correctly
            ->addCssFile('https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css')
            ->addJsFile('https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js')
            ->addHtmlContentToHead('
                <style>
                    /* Premium Styling for Product Selector */
                    .ts-wrapper.custom-tomselect .ts-dropdown .option {
                        display: flex !important;
                        align-items: center !important;
                        padding: 10px 15px !important;
                        gap: 15px !important;
                        border-bottom: 1px solid #f3f4f6 !important;
                    }
                    .ts-wrapper.custom-tomselect .option img {
                        width: 45px;
                        height: 45px;
                        object-fit: cover;
                        border-radius: 8px;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                    }
                    .ts-wrapper.custom-tomselect .ts-control {
                        border-radius: 10px !important;
                        padding: 12px 16px !important;
                        border: 1px solid #d1d5db !important;
                        font-weight: 500;
                    }
                </style>
            ')
            ->addHtmlContentToBody('
                <script>
                    (function() {
                        const initCustomOrdersTS = () => {
                            const selects = document.querySelectorAll(".custom-tomselect:not(.ts-setup)");
                            selects.forEach(el => {
                                if (window.TomSelect) {
                                    new TomSelect(el, {
                                        plugins: ["dropdown_input"],
                                        onInitialize: function() {
                                            const options = Array.from(el.options).map(opt => ({
                                                value: opt.value,
                                                text: opt.innerText,
                                                img: opt.getAttribute("data-img") || "/assets/img/placeholder.png"
                                            }));
                                            this.clearOptions();
                                            this.addOptions(options);
                                        },
                                        render: {
                                            option: (data, escape) => `
                                                <div class="option">
                                                    <img src="${data.img}" />
                                                    <div class="d-flex flex-column">
                                                        <span style="font-weight: 700; color: #111;">${escape(data.text)}</span>
                                                    </div>
                                                </div>
                                            `
                                        }
                                    });
                                    el.classList.add("ts-setup");
                                }
                            });
                        };
                        
                        document.addEventListener("DOMContentLoaded", initCustomOrdersTS);
                        document.addEventListener("ea.collection.item-added", () => setTimeout(initCustomOrdersTS, 300));
                        
                        // Safety interval
                        setInterval(initCustomOrdersTS, 2000);
                    })();
                </script>
            ')
        ;
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if (Crud::PAGE_INDEX === $responseParameters->get('pageName')) {
            /** @var EntityManagerInterface $em */
            $em = $this->container->get('doctrine')->getManager();
            
            $total_paid = $em->getRepository(Order::class)->count(['state' => 1]);
            $total_pending = $em->getRepository(Order::class)->count(['state' => 0]);
            $total_pending_payment = $em->getRepository(Order::class)->count(['state' => 4]);
            
            $responseParameters->set('total_paid', $total_paid);
            $responseParameters->set('total_pending', $total_pending);
            $responseParameters->set('total_pending_payment', $total_pending_payment);
        }

        return $responseParameters;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Order) {
            if (!$entityInstance->getCreatedAt()) {
                $entityInstance->setCreatedAt(new \DateTime());
            }
            if (!$entityInstance->getReference()) {
                $date = new \DateTime();
                $entityInstance->setReference($date->format('dmy') . '-' . uniqid());
            }
        }
        parent::persistEntity($entityManager, $entityInstance);
    }
    
 
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnDetail(), 
            TextField::new('reference', 'ID del Pedido'),
            DateTimeField::new('createdAt', 'Fecha')->setFormat('short', 'short'),
            AssociationField::new('user', 'Cliente'),
            
            // Administrative fields (Form/Detail mostly)
            CollectionField::new('orderDetails', 'Productos del Pedido')
                ->allowAdd()
                ->allowDelete()
                ->setEntryType(OrderDetailType::class)
                ->setFormTypeOption('by_reference', false)
                ->setTemplatePath('admin/field/order_details.html.twig')
                ->hideOnIndex(),
            
            // This is for the index list summary
            TextField::new('productSummary', 'Productos')->onlyOnIndex(),
            
            MoneyField::new('total', 'Total')->setCurrency('ARS')->hideOnForm(),
            MoneyField::new('grossProfit', 'Ganancia Bruta')->setCurrency('ARS')->hideOnForm()->hideOnIndex()->onlyOnDetail(),
            
            ChoiceField::new('state', 'Estado')->setChoices([
                'No pagado' => 0,
                'Pagado' => 1,
                'En preparación' => 2,
                'Enviado/Retirado' => 3,
                'Pendiente de pago' => 4,
                'Cancelado' => 5,
            ])->renderAsBadges([
                0 => 'warning',
                1 => 'success',
                2 => 'primary',
                3 => 'secondary',
                4 => 'info',
                5 => 'danger',
            ]),
            
            TextField::new('paymentMethod', 'Método de Pago')->onlyOnDetail(),
            TextField::new('carrierName', 'Transporte'),
            MoneyField::new('carrierPrice', 'Costo Envío')->setCurrency('ARS'),
            TextField::new('delivery', 'Detalle de Entrega / Dirección')->hideOnIndex()->renderAsHtml(),
        ];
    }

}
