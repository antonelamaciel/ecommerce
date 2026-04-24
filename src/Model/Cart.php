<?php
namespace App\Model;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Permet de gérer un panier en session plutot que de tout implémenter dans le controller
 * Modifié pour conserver le panier dans la BDD pour un utilisateur connecté
 */
class Cart 
{
    private SessionInterface $session;
    private ProductRepository $repository;
    private Security $security;
    private EntityManagerInterface $em;
    private ?array $cartArray = null;

    public function __construct(SessionInterface $session, ProductRepository $repository, Security $security, EntityManagerInterface $em)
    {
        $this->session = $session;
        $this->repository = $repository;
        $this->security = $security;
        $this->em = $em;
    }

    private function getCartArray(): array
    {
        if ($this->cartArray !== null) {
            return $this->cartArray;
        }

        $sessionCart = $this->session->get('cart_v2', []);
        $user = $this->security->getUser();
        
        if ($user && method_exists($user, 'getCartData')) {
            $dbCart = $user->getCartData() ?? [];
            
            // Si hay algo en la sesión (carrito anónimo), lo mezclamos con la DB una sola vez
            if (!empty($sessionCart) && !$this->session->get('cart_db_merged')) {
                foreach ($sessionCart as $key => $item) {
                    if (isset($dbCart[$key])) {
                        // Si ya existe el producto en el carrito de la DB, sumamos cantidades 
                        // pero solo para el merge inicial.
                        $dbCart[$key]['qty'] += $item['qty'];
                    } else {
                        $dbCart[$key] = $item;
                    }
                }
                
                if (method_exists($user, 'setCartData')) {
                    $user->setCartData($dbCart);
                    $this->em->flush();
                }
                
                // Marcamos como mezclado y LIMPIAMOS el carrito de sesión
                // para que no se vuelva a sumar en la próxima carga de página.
                $this->session->set('cart_db_merged', true);
                $this->session->remove('cart_v2');
            }
            
            $this->cartArray = $dbCart;
            return $dbCart;
        }
        
        $this->cartArray = $sessionCart;
        return $sessionCart;
    }

    private function saveCartArray(array $cart): void
    {
        $this->cartArray = $cart;
        $user = $this->security->getUser();
        
        if ($user && method_exists($user, 'setCartData')) {
            // Para usuarios logueados, guardamos en DB y limpiamos la sesión
            // Evitamos usar 'cart_v2' como caché para no caer en duplicaciones de merge
            $user->setCartData($cart);
            $this->em->flush();
            $this->session->remove('cart_v2'); 
        } else {
            // Para usuarios anónimos, usamos la sesión habitual
            $this->session->set('cart_v2', $cart);
        }
    }
    public function add(int $id, int $qty = 1, ?string $variants = null, bool $exclusive = false): void
    {
        $product = $this->repository->find($id);
        if (!$product) {
            return;
        }

        $stock = $product->getStock();
        $cart = $this->getCartArray();
        
        $variants = $variants ? trim($variants) : null;
        if ($variants === '') $variants = null;
        
        $compositeId = $variants ? $id . '-' . md5($variants) : (string)$id;

        // Si el stock no es nulo, validamos
        if ($stock !== null) {
            if ($exclusive) {
                // Si es modo exclusivo, validamos directamente contra el stock
                if ($qty > $stock) {
                    $qty = $stock;
                }
            } else {
                // Si es modo incremental, validamos el total acumulado
                $totalInCartForThisProduct = 0;
                foreach ($cart as $item) {
                    if ($item['id'] === $id) {
                        $totalInCartForThisProduct += $item['qty'];
                    }
                }
                
                if ($totalInCartForThisProduct + $qty > $stock) {
                    $qty = max(0, $stock - $totalInCartForThisProduct);
                }
            }
        }

        if ($qty <= 0) {
            if ($exclusive && !empty($cart[$compositeId])) {
                unset($cart[$compositeId]);
                $this->saveCartArray($cart);
            }
            return;
        }

        if (empty($cart[$compositeId])) {
            $cart[$compositeId] = [
                'id' => $id,
                'qty' => $qty,
                'variants' => $variants
            ];
        } else {
            if ($exclusive) {
                $cart[$compositeId]['qty'] = $qty;
            } else {
                $cart[$compositeId]['qty'] += $qty;
            }
        }

        $this->saveCartArray($cart);
    }

    public function get(): array
    {
        return $this->getCartArray();
    }

    public function remove(): void
    {
        $this->saveCartArray([]);
    }

    public function removeItem(string $compositeId): void
    {
        $cart = $this->getCartArray();
        unset($cart[$compositeId]);
        $this->saveCartArray($cart);
    }

    public function decreaseItem(string $compositeId): void
    {
        $cart = $this->getCartArray();
        if (isset($cart[$compositeId])) {
            if ($cart[$compositeId]['qty'] < 2) {
                unset($cart[$compositeId]);
            } else {
                $cart[$compositeId]['qty']--;
            }
        }
        $this->saveCartArray($cart);
    }

    public function getFullQuantity(): int
    {
        $cart = $this->getCartArray();
        $quantity = 0;

        foreach ($cart as $item) {
            $quantity += $item['qty'];
        }

        return $quantity;
    }

    public function getDetails(): array
    {
        $cartProducts = [
            'products' => [],
            'totals' => [
                'quantity' => 0,
                'price' => 0,
            ],
        ];

        $cart = $this->getCartArray();
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