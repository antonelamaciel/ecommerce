<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use App\Model\Cart;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * Récupération du panier, choix de l'adresse et du transporteur
     *
     * @param SessionInterface $session
     * @param Cart $cart
     * @return Response
     */
    #[Route('/order', name: 'order')]
    public function index(SessionInterface $session, Cart $cart): Response
    {
        $user = $this->getUser();
        $cartProducts = $cart->getDetails();

        // Redirection si panier vide
        if (empty($cartProducts['products'])) {   
            return $this->redirectToRoute('product');
        }
        
        //Redirection si utilisateur n'a pas encore d'adresse
        if (!$user->getAddresses()->getValues()) {      //getValues() Récupère directement les valeurs d'une collection d'objet
            $session->set('order', 1);
            return $this->redirectToRoute('account_address_new');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $user     //Permet de passer l'utilisateur courant dans le tableau d'options du OrderType
        ]); 

        $addressesJson = [];
        foreach ($user->getAddresses() as $address) {
            $addressesJson[$address->getId()] = [
                'city' => $address->getCity(),
                'postal' => $address->getPostal(),
                'address' => $address->getAddress()
            ];
        }

        return $this->renderForm('order/index.html.twig', [
            'form' => $form,
            'cart' => $cartProducts,
            'totalPrice' =>$cartProducts['totals']['price'],
            'addressesJson' => json_encode($addressesJson)
        ]);
    }

    /**
     * Enregistrement des données "en dur" de la commande contenant adresse, transporteur et produits
     * Les relations ne sont pas directement utilisées pour la persistance des données dans les entités Order et OrderDetails
     * pour éviter des incohérences dans le cas ou des modifications seraient faites sur les autres entités par la suite
     *
     * @param Cart $cart
     * @param Request $request
     * @return Response
     */
    #[Route('/order/summary', name: 'order_add', methods: 'POST')]
    public function summary(Cart $cart, Request $request, EntityManagerInterface $em, \App\Service\Shipping\ShippingCalculatorService $shippingCalc): Response
    {
         //Récupération du panier en session
        $cartProducts = $cart->getDetails();   

        //Vérification qu'un formulaire a bien été envoyé précédemment
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()     
        ]); 
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $address = $form->get('addresses')->getData();

            $delivery_string = $address->getFirstname() . ' ' . $address->getLastname();
            $delivery_string .= '<br>' . $address->getPhone();
            $delivery_string .= '<br>' . ($address->getCompany() ?? '');
            $delivery_string .= '<br>' . $address->getAddress();
            $delivery_string .= '<br>' . $address->getPostal();
            $delivery_string .= '<br>' . $address->getCity();
            $delivery_string .= '<br>' . $address->getCountry();

            $cartProducts = $cart->getDetails();

            //Création de la commande avec les infos formulario (sin transportista aún)
            $order = new Order;
            $date = new \DateTime;
            $order
                ->setUser($this->getUser())
                ->setCreatedAt($date)
                ->setCarrierName('Pendiente de selección')
                ->setCarrierPrice(0)
                ->setDelivery($delivery_string)
                ->setState(0)
                ->setReference($date->format('YmdHis') . '-' . uniqid())
            ;
            $em->persist($order);

            //Création des lignes de détails pour chacun des produits de la commande
            foreach ($cartProducts['products'] as $item) {
                $orderDetails = new OrderDetails();
                $orderDetails
                    ->setBindedOrder($order)
                    ->setProduct($item['product']->getName())
                    ->setProductObject($item['product'])
                    ->setVariants($item['variants'])
                    ->setQuantity($item['quantity'])
                    ->setPrice($item['product']->getPrice())
                    ->setPurchaseCost($item['product']->getPurchaseCost())
                    ->setTotal($item['product']->getPrice() * $item['quantity'])
                ;
                $em->persist($orderDetails);
            }

            // Calcul de la ganancia bruta
            $order->calculateGrossProfit();
            
            $em->flush();

            $cartProducts = $cart->getDetails();
            $destCp = $address->getPostal();
            $shippingOptions = $shippingCalc->calculateShipping($destCp, $cartProducts['totals']['price']);

            // Affichage récap
            return $this->renderForm('order/add.html.twig', [
                'cart' => $cartProducts,
                'totalPrice' => $cartProducts['totals']['price'],
                'order' => $order,
                'carriers' => $shippingOptions
            ]);
        }
        //Si pas de formulaire, page non accessible, et donc redirection vers le panier
        return $this->redirectToRoute('cart');
    }

    #[Route('/order/update-carrier/{reference}/{id}', name: 'order_update_carrier', methods: ['POST'])]
    public function updateCarrier(string $reference, string $id, OrderRepository $orderRepository, EntityManagerInterface $em, \App\Service\Shipping\ShippingCalculatorService $shippingCalc, Cart $cart): Response
    {
        $order = $orderRepository->findOneByReference($reference);
        $carrier = $em->getRepository(\App\Entity\Carrier::class)->find($id);

        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->json(['success' => false], 404);
        }

        // Get CP from order delivery string (line 5 usually, index 4)
        $deliveryParts = explode('<br>', $order->getDelivery());
        $destCp = trim($deliveryParts[4] ?? '1000');

        $cartProducts = $cart->getDetails();
        $options = $shippingCalc->calculateShipping($destCp, $order->getTotal());
        
        $selectedOption = null;
        foreach ($options as $opt) {
            if ($opt['id'] === $id) {
                $selectedOption = $opt;
                break;
            }
        }

        if (!$selectedOption) {
            return $this->json(['success' => false, 'message' => 'Opción de envío no válida'], 400);
        }

        $price = (float)$selectedOption['price'];
        $order->setCarrierName($selectedOption['name']);
        $order->setCarrierPrice($price);
        $em->flush();

        return $this->json([
            'success' => true,
            'carrierPrice' => $price,
            'totalPrice' => $order->getTotal() + $price
        ]);
    }

    #[Route('/order/confirm-efectivo/{reference}', name: 'order_confirm_cash')]
    public function confirmCash(string $reference, OrderRepository $orderRepository, Cart $cart, EntityManagerInterface $em, \App\Service\ReceiptGenerator $receiptGenerator, \App\Service\WhatsAppNotifier $whatsappNotifier): Response
    {
        $order = $orderRepository->findOneByReference($reference);
        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('cart');
        }

        // Si l'order est déjà validé (ou autre état que 0), on redirige
        if ($order->getState() !== 0) {
            return $this->redirectToRoute('account_order', ['reference' => $order->getReference()]);
        }

        // Marquer comme en attente de paiement
        $order->setState(4);
        $order->setPaymentMethod('cash');

        // Reservar stock
        foreach ($order->getOrderDetails() as $detail) {
            $product = $detail->getProductObject();
            if ($product && $product->getStock() !== null) {
                $newStock = $product->getStock() - $detail->getQuantity();
                $product->setStock(max(0, $newStock));
            }
        }
        
        // 1. Generate Receipt
        $receiptFilename = $receiptGenerator->generate($order);
        $order->setReceiptFilename($receiptFilename);

        $em->flush();

        // 2. Notify User
        $whatsappNotifier->sendReceipt($order);

        // On vide le panier
        $cart->remove();

        return $this->render('order/success_cash.html.twig', [
            'order' => $order
        ]);
    }

    #[Route('/order/confirm-transferencia/{reference}', name: 'order_confirm_transfer')]
    public function confirmTransfer(string $reference, OrderRepository $orderRepository, Cart $cart, EntityManagerInterface $em, \App\Service\ReceiptGenerator $receiptGenerator, \App\Service\WhatsAppNotifier $whatsappNotifier): Response
    {
        $order = $orderRepository->findOneByReference($reference);
        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('cart');
        }

        // Si l'order est déjà validé (ou autre état que 0), on redirige
        if ($order->getState() !== 0) {
            return $this->redirectToRoute('account_order', ['reference' => $order->getReference()]);
        }

        // Marquer comme en attente de paiement
        $order->setState(4);
        $order->setPaymentMethod('transfer');

        // Reservar stock
        foreach ($order->getOrderDetails() as $detail) {
            $product = $detail->getProductObject();
            if ($product && $product->getStock() !== null) {
                $newStock = $product->getStock() - $detail->getQuantity();
                $product->setStock(max(0, $newStock));
            }
        }
        
        // 1. Generate Receipt
        $receiptFilename = $receiptGenerator->generate($order);
        $order->setReceiptFilename($receiptFilename);

        $em->flush();

        // 2. Notify User via WhatsApp (Backend Integration structure ready)
        $whatsappNotifier->sendReceipt($order);

        // On vide le panier
        $cart->remove();

        return $this->render('order/success_transfer.html.twig', [
            'order' => $order
        ]);
    }
}
