<?php

namespace App\Controller;

use App\Entity\Order;
use App\Model\Cart;
use App\Repository\OrderRepository;
use App\Service\Mail;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    /**
     * Etape de vérification avant confirmation du paiement
     */
    #[Route('/order/checkout/{reference}', name: 'checkout')]
    public function payment(string $reference): Response
    {
        return $this->redirectToRoute('mercadopago_checkout', ['reference' => $reference]);
    }

    /**
     * Méthode appelée lorsque le paiement est validé
     */
    #[Route('/order/success/{stripeSession}', name: 'payment_success')]
    public function paymentSuccess(OrderRepository $repository, $stripeSession, EntityManagerInterface $em, Cart $cart) 
    {
        // For MP, stripeSession is actually the reference or preference_id. 
        // We'll check by reference if preference_id fails.
        $order = $repository->findOneByStripeSession($stripeSession);
        if (!$order) {
            $order = $repository->findOneByReference($stripeSession);
        }

        if (!$order || $order->getUser() != $this->getUser()) {
            throw $this->createNotFoundException('Commande innaccessible');
        }
        if ($order->getState() == 0) {
            $order->setState(1);
            $em->flush();
        }

        // Envoi mail de Confirmation
        $user = $this->getUser();

        $content = "Hola {$user->getFirstname()}, te agradecemos por tu compra en nuestra tienda.";
        (new Mail)->send(
            $user->getEmail(), 
            $user->getFirstname(), 
            "Confirmación de pedido {$order->getReference()}", 
            $content
        );

        // Suppression du panier une fois la commande validée
        $cart->remove();    
        return $this->render('payment/success.html.twig', [
            'order' => $order
        ]);
    }

    /**
     * Commande annullée (clic sur retour dans la fenêtre)
     */
    #[Route('/order/fail/{stripeSession}', name: 'payment_fail')]
    public function paymentFail(OrderRepository $repository, $stripeSession) 
    {
        $order = $repository->findOneByStripeSession($stripeSession);
        if (!$order) {
            $order = $repository->findOneByReference($stripeSession);
        }
        
        if (!$order || $order->getUser() != $this->getUser()) {
            throw $this->createNotFoundException('Commande innaccessible');
        }

        return $this->render('payment/fail.html.twig', [
            'order' => $order
        ]);
    }
}
