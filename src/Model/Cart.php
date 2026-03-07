<?php
namespace App\Model;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Permet de gérer un panier en session plutot que de tout implémenter dans le controller
 * Modifié pour supporter les variantes (opciones e hijas)
 */
class Cart 
{
    private SessionInterface $session;
    private ProductRepository $repository;

    public function __construct(SessionInterface $session, ProductRepository $repository)
    {
        $this->session = $session;
        $this->repository = $repository;
    }

    /**
     * Ajoute un produit au panier avec ses variantes
     *
     * @param int $id
     * @param int $qty
     * @param string|null $variants
     * @return void
     */
    public function add(int $id, int $qty = 1, ?string $variants = null): void
    {
        $cart = $this->session->get('cart_v2', []);
        
        // Trim and normalize variants string
        $variants = $variants ? trim($variants) : null;
        if ($variants === '') $variants = null;
        
        // Créer une clé unique basée sur l'ID du produit et ses variantes
        $compositeId = $variants ? $id . '-' . md5($variants) : (string)$id;

        if (empty($cart[$compositeId])) {
            $cart[$compositeId] = [
                'id' => $id,
                'qty' => $qty,
                'variants' => $variants
            ];
        } else {
            $cart[$compositeId]['qty'] += $qty;
        }

        $this->session->set('cart_v2', $cart);
    }

    /**
     * Récupère le panier
     */
    public function get(): array
    {
        return $this->session->get('cart_v2', []);
    }

    /**
     * Supprime entièrement le panier
     */
    public function remove(): void
    {
        $this->session->remove('cart_v2');
    }

    /**
     * Supprime un item du panier via sa clé composite
     *
     * @param string $compositeId
     * @return void
     */
    public function removeItem(string $compositeId): void
    {
        $cart = $this->session->get('cart_v2', []);
        unset($cart[$compositeId]);
        $this->session->set('cart_v2', $cart);
    }

    /**
     * Diminue la quantité d'un item
     *
     * @param string $compositeId
     * @return void
     */
    public function decreaseItem(string $compositeId): void
    {
        $cart = $this->session->get('cart_v2', []);
        if (isset($cart[$compositeId])) {
            if ($cart[$compositeId]['qty'] < 2) {
                unset($cart[$compositeId]);
            } else {
                $cart[$compositeId]['qty']--;
            }
        }
        $this->session->set('cart_v2', $cart);
    }

    /**
     * Quantité totale d'articles
     */
    public function getFullQuantity(): int
    {
        $cart = $this->session->get('cart_v2', []);
        $quantity = 0;

        foreach ($cart as $item) {
            $quantity += $item['qty'];
        }

        return $quantity;
    }

    /**
     * Détails complets du panier pour affichage
     */
    public function getDetails(): array
    {
        $cartProducts = [
            'products' => [],
            'totals' => [
                'quantity' => 0,
                'price' => 0,
            ],
        ];

        $cart = $this->session->get('cart_v2', []);
        if ($cart) {
            foreach ($cart as $compositeId => $item) {
                $currentProduct = $this->repository->find($item['id']);
                if ($currentProduct) {
                    $discountPercentage = $currentProduct->getMaxDiscount();
                    $unitPrice = $currentProduct->getPrice();
                    if ($discountPercentage > 0) {
                        $unitPrice = $unitPrice * (1 - $discountPercentage / 100);
                    }
                    
                    $cartProducts['products'][] = [
                        'product' => $currentProduct,
                        'quantity' => $item['qty'],
                        'variants' => $item['variants'],
                        'compositeId' => $compositeId,
                        'discountedPrice' => $unitPrice,
                        'discountPercentage' => $discountPercentage
                    ];
                    $cartProducts['totals']['quantity'] += $item['qty'];
                    $cartProducts['totals']['price'] += $item['qty'] * $unitPrice;
                }
            }
        }
        return $cartProducts;
    }
}