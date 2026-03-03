<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class CancellationController extends AbstractController
{
    #[Route('/arrepentimiento-compra', name: 'app_cancellation', methods: ['GET', 'POST'])]
    public function cancellation(Request $request, MailerInterface $mailer, \App\Repository\PersonalizeRepository $personalizeRepository): Response
    {
        $personalize = $personalizeRepository->findOneBy([]);
        $companyEmail = ($personalize && $personalize->getEmail()) ? $personalize->getEmail() : 'antonelamaciel2024@gmail.com';
        $companyName = ($personalize && $personalize->getCompanyName()) ? $personalize->getCompanyName() : 'Tu Tienda';

        // Si es GET, mostrar el formulario
        if ($request->isMethod('GET')) {
            return $this->render('cancellation/index.html.twig', [
                'personalize' => $personalize
            ]);
        }

        // Si es POST, procesar el formulario
        try {
            // Obtener datos del formulario
            $name = $request->request->get('name');
            $phone = $request->request->get('phone');
            $email = $request->request->get('email');
            $orderId = $request->request->get('order_id');
            $reason = $request->request->get('reason', 'No especificado');

            // Validación básica
            if (!$name || !$phone || !$email || !$orderId) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Por favor completa todos los campos requeridos.'
                ], 400);
            }

            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'El correo electrónico no es válido.'
                ], 400);
            }

            // Preparar el email
            $emailMessage = (new Email())
                ->from(new Address($companyEmail, 'Sistema de Arrepentimiento'))
                ->to($companyEmail)
                ->replyTo(new Address($email, $name))
                ->subject('Nueva Solicitud de Arrepentimiento de Compra - Pedido ' . $orderId)
                ->html($this->renderView('emails/cancellation.html.twig', [
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $email,
                    'orderId' => $orderId,
                    'reason' => $reason,
                    'date' => new \DateTime(),
                ]));

            // Enviar el email
            $mailer->send($emailMessage);

            // Email de confirmación al cliente
            $confirmationEmail = (new Email())
                ->from(new Address($companyEmail, $companyName))
                ->to(new Address($email, $name))
                ->subject('Confirmación de Solicitud de Arrepentimiento - Pedido ' . $orderId)
                ->html($this->renderView('emails/cancellation_confirmation.html.twig', [
                    'name' => $name,
                    'orderId' => $orderId,
                    'date' => new \DateTime(),
                ]));

            $mailer->send($confirmationEmail);

            return new JsonResponse([
                'success' => true,
                'message' => 'Tu solicitud ha sido enviada exitosamente.',
                'redirect' => $this->generateUrl('home')
            ]);

        } catch (\Exception $e) {
            // Log del error (opcional)
            // $this->logger->error('Error en formulario de arrepentimiento: ' . $e->getMessage());

            return new JsonResponse([
                'success' => false,
                'message' => 'Hubo un error al procesar tu solicitud. Por favor, intenta nuevamente.'
            ], 500);
        }
    }
}