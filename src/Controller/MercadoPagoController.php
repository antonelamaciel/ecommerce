<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Mail;
use App\Service\MercadoPagoService;
use App\Model\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

class MercadoPagoController extends AbstractController
{
    #[Route('/mercadopago/checkout/{reference}', name: 'mercadopago_checkout')]
    public function checkout(string $reference, OrderRepository $orderRepository, MercadoPagoService $mpService, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        $order = $orderRepository->findOneByReference($reference);
        if (!$order || $order->getUser() != $this->getUser()) {
            $this->addFlash('error', 'Pedido no encontrado o acceso no autorizado.');
            return $this->redirectToRoute('cart');
        }

        try {
            $preference = $mpService->createPreference($order);
            
            if (!isset($preference['id']) || !isset($preference['init_point'])) {
                $logger->error('Mercado Pago Preference Error: ' . json_encode($preference));
                throw new \Exception('La respuesta de Mercado Pago no contiene los datos necesarios.');
            }

            $order->setStripeSession($preference['id']); 
            $order->setPaymentMethod('mercadopago');
            $em->flush();

            return $this->redirect($preference['init_point']);
        } catch (\Exception $e) {
            $logger->error('Mercado Pago Checkout error: ' . $e->getMessage());
            $this->addFlash('error', 'Ocurrió un error al procesar el pago: ' . $e->getMessage());
            return $this->redirectToRoute('order');
        }
    }

    #[Route('/mercadopago/preference/{reference}', name: 'mercadopago_preference')]
    public function getPreference(string $reference, OrderRepository $orderRepository, MercadoPagoService $mpService, EntityManagerInterface $em): JsonResponse
    {
        $order = $orderRepository->findOneByReference($reference);
        if (!$order || $order->getUser() != $this->getUser()) {
            return new JsonResponse(['error' => 'Order not found'], 404);
        }

        try {
            $preference = $mpService->createPreference($order);
            $order->setStripeSession($preference['id']); 
            $em->flush();

            return new JsonResponse([
                'id' => $preference['id'],
                'init_point' => $preference['init_point']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/mercadopago/webhook', name: 'mercadopago_webhook', methods: ['POST', 'GET'])]
    public function webhook(Request $request, OrderRepository $orderRepository, MercadoPagoService $mpService, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        // Identification of the payment
        $id = $request->query->get('id') ?? $request->request->get('id');
        $topic = $request->query->get('topic') ?? $request->request->get('topic');

        // Body content parsing for newer webhook formats
        $content = json_decode($request->getContent(), true);
        if ($content && isset($content['type']) && $content['type'] === 'payment') {
            $topic = 'payment';
            $id = $content['data']['id'] ?? $id;
        }

        if ($topic === 'payment' && $id) {
            try {
                $paymentInfo = $mpService->getPaymentStatus($id);
                $logger->info('Mercado Pago Webhook Received. Status: ' . ($paymentInfo['status'] ?? 'unknown') . ' for ID: ' . $id);
                
                $status = $paymentInfo['status'] ?? null;
                $reference = $paymentInfo['external_reference'] ?? null;

                if ($reference) {
                    $order = $orderRepository->findOneByReference($reference);
                    if ($order) {
                        switch ($status) {
                            case 'approved':
                                if ($order->getState() == 0 || $order->getState() == 4) {
                                    $order->setState(1);
                                    $em->flush();

                                    $user = $order->getUser();
                                    $mailContent = "Hola {$user->getFirstname()}, tu pago para el pedido {$order->getReference()} fue aprobado.";
                                    (new Mail)->send($user->getEmail(), $user->getFirstname(), "Pago Confirmado", $mailContent);
                                    $logger->info("Order {$reference} marked as PAID.");
                                }
                                break;
                            case 'rejected':
                            case 'cancelled':
                                // You could add a 'cancelled' state if you want
                                $logger->warning("Payment for order {$reference} was {$status}.");
                                break;
                            case 'pending':
                            case 'in_process':
                                $logger->info("Payment for order {$reference} is pending/in-process.");
                                break;
                        }
                    }
                }
            } catch (\Exception $e) {
                $logger->error('Mercado Pago Webhook Verification Error: ' . $e->getMessage());
            }
        }

        return new Response('OK', 200);
    }
}
