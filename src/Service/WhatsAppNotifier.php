<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WhatsAppNotifier
{
    private $router;
    private $httpClient;

    public function __construct(UrlGeneratorInterface $router, HttpClientInterface $httpClient)
    {
        $this->router = $router;
        $this->httpClient = $httpClient;
    }

    public function sendReceipt(Order $order): void
    {
        // NOTA PARA USUARIO: El teléfono del cliente no siempre existe nativamente en User,
        // pero podemos probar extrayendo la info del Delivery. 
        // Normalmente el cliente tiene un número en su perfil o dirección.
        $deliveryParts = explode('<br>', $order->getDelivery() ?? '');
        $phone = $deliveryParts[1] ?? ''; // La 2da línea suele guardar el teléfono.
        
        // Limpiamos formato
        $rawPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($rawPhone)) {
            return; 
        }

        $receiptUrl = $this->router->generate('download_receipt', ['reference' => $order->getReference()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $message = sprintf(
            "¡Hola %s! Gracias por tu compra en nuestro sitio. Tu pedido #%s ha sido confirmado. Puedes ver y descargar tu comprobante de pago oficial desde este enlace: %s",
            $order->getUser()->getFirstname() ?? 'Cliente',
            $order->getReference(),
            $receiptUrl
        );

        /*
        // TODO: LOGICA PARA INTEGRAR TU API DE WHATSAPP (Ultramsg, Evolution API, Meta API, etc.)
        // EJEMPLO:
        try {
            $this->httpClient->request('POST', 'https://tu-api.proveedor.com/send/message', [
                'json' => [
                    'phone' => '549' . $rawPhone, // Adaptar prefijo al pais
                    'message' => $message,
                ]
            ]);
        } catch (\Exception $e) {
            // Manejar error silenciosamente
        }
        */
    }
}
