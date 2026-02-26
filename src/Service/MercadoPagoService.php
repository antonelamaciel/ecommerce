<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MercadoPagoService
{
    private $client;
    private $router;
    private $accessToken;

    public function __construct(HttpClientInterface $client, UrlGeneratorInterface $router, string $accessToken)
    {
        $this->client = $client;
        $this->router = $router;
        $this->accessToken = $accessToken;
    }

    public function createPreference(Order $order): array
    {
        $items = [];
        foreach ($order->getOrderDetails() as $detail) {
            $items[] = [
                'title' => $detail->getProduct(),
                'quantity' => $detail->getQuantity(),
                'unit_price' => (float) ($detail->getPrice() / 100),
                'currency_id' => 'ARS'
            ];
        }

        // Add shipping
        $items[] = [
            'title' => 'Envío: ' . $order->getCarrierName(),
            'quantity' => 1,
            'unit_price' => (float) ($order->getCarrierPrice() / 100),
            'currency_id' => 'ARS'
        ];

        $response = $this->client->request('POST', 'https://api.mercadopago.com/checkout/preferences', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'items' => $items,
                'back_urls' => [
                    'success' => $this->router->generate('payment_success', ['stripeSession' => $order->getReference()], UrlGeneratorInterface::ABSOLUTE_URL),
                    'failure' => $this->router->generate('payment_fail', ['stripeSession' => $order->getReference()], UrlGeneratorInterface::ABSOLUTE_URL),
                    'pending' => $this->router->generate('payment_fail', ['stripeSession' => $order->getReference()], UrlGeneratorInterface::ABSOLUTE_URL),
                ],
                'auto_return' => 'approved',
                'external_reference' => $order->getReference(),
                'notification_url' => $this->router->generate('mercadopago_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ],
        ]);

        return $response->toArray();
    }

    public function getPaymentStatus(string $id): array
    {
        $response = $this->client->request('GET', 'https://api.mercadopago.com/v1/payments/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
        ]);

        return $response->toArray();
    }
}
