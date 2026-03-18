<?php

namespace App\Controller;

use App\Model\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * Récupère un panier détaillé contenant des objets Products et les totaux de quantité et de prix 
     * 
     * @param Cart $cart
     * @return Response
     */
    #[Route('/cart', name: 'cart')]
    public function index(Cart $cart, \App\Repository\CarrierRepository $carrierRepository, \App\Repository\PersonalizeRepository $personalizeRepository): Response
    {
        $cartProducts = $cart->getDetails();
        $carriers = $carrierRepository->findAll();
        $personalize = $personalizeRepository->findOneBy([]);

        return $this->render('cart/index.html.twig', [
            'cart' => $cartProducts['products'],
            'totalQuantity' => $cartProducts['totals']['quantity'],
            'totalPrice' =>$cartProducts['totals']['price'],
            'carriers' => $carriers,
            'personalize' => $personalize
        ]);
    }

    /**
     * Ajoute un article au panier (id du produit) et incrémente la quantitée (voir classe Cart)
     * @param Cart $cart
     * @param int $id
     * @return Repsonse
     */
    #[Route('/cart/add/{id}', name: 'add_to_cart')]
    public function add(Cart $cart, int $id, Request $request): Response
    {
        $qty = $request->query->getInt('qty', 1);
        $variants = $request->query->get('variants'); // Ex: "Color: Rojo, Talle: L"
        
        $cart->add($id, $qty, $variants);

        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest' || $request->query->get('ajax')) {
            $details = $cart->getDetails();
            return $this->json([
                'cartCount' => $details['totals']['quantity'],
                'totalPrice' => $details['totals']['price'],
                'success' => true
            ]);
        }

        return $this->redirectToRoute('cart');
    }

    /**
     * Réduit de 1 la quantité pour un article du panier
     * @param Cart $cart
     * @param string $id (Composite key)
     * @return Response
     */
    #[Route('/cart/decrease/{id}', name: 'decrease_item')]
    public function decrease(Cart $cart, string $id, Request $request): Response
    {
        $cart->decreaseItem($id);

        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest' || $request->query->get('ajax')) {
            $details = $cart->getDetails();
            return $this->json([
                'cartCount' => $details['totals']['quantity'],
                'totalPrice' => $details['totals']['price'],
                'success' => true
            ]);
        }

        return $this->redirectToRoute('cart');
    }
    
    /**
     * Supprime une ligne d'articles du panier
     *
     * @param Cart $cart
     * @param string $id (Composite key)
     * @return Response
     */
    #[Route('/cart/remove/{id}', name: 'remove_cart_item')]
    public function removeItem(Cart $cart, string $id, Request $request): Response
    {
        $cart->removeItem($id);

        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest' || $request->query->get('ajax')) {
            $details = $cart->getDetails();
            return $this->json([
                'cartCount' => $details['totals']['quantity'],
                'totalPrice' => $details['totals']['price'],
                'success' => true
            ]);
        }

        return $this->redirectToRoute('cart');
    }

    /**
     * Vide le panier entièrement
     *
     * @param Cart $cart
     * @return Response
     */
    #[Route('/cart/clear/', name: 'remove_cart')]
    public function remove(Cart $cart): Response
    {
        $cart->remove();
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/shipping/estimate', name: 'cart_shipping_estimate', methods: ['POST'])]
    public function estimateShipping(Request $request, \App\Service\Shipping\ShippingCalculatorService $calculator, Cart $cart): Response
    {
        $payload = json_decode($request->getContent(), true);
        $cp = $payload['postalCode'] ?? '';

        if (empty($cp)) {
            return $this->json(['error' => 'Código postal inválido'], 400);
        }

        $cartDetails = $cart->getDetails();

        try {
            $options = $calculator->calculateShipping($cp, $cartDetails['totals']['price'] / 100);
            
            // Format prices for JSON response
            foreach ($options as &$opt) {
                $opt['formatted'] = ($opt['price'] > 0) ? '$ ' . number_format($opt['price'], 2, ',', '.') : 'Gratis';
            }

            return $this->json(['success' => true, 'options' => $options]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Error al calcular envío: ' . $e->getMessage()], 500);
        }
    }
}
