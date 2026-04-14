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
    public function paymentSuccess(OrderRepository $repository, $stripeSession, EntityManagerInterface $em, Cart $cart, Mail $mail) 
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
        if ($order->getState() == 0 || $order->getState() == 4) {
            $order->setState(1);
            if (!$order->getPaymentMethod()) {
                $order->setPaymentMethod('mercadopago');
            }
            $em->flush();
        }

        // Removal of duplicate email as requested - the detailed email is sent elsewhere.


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
