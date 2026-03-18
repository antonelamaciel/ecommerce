<?php

namespace App\Service\Shipping\Carrier;

use App\Service\Shipping\ShippingCarrierInterface;

class PickupCarrier implements ShippingCarrierInterface
{
    public function getName(string $originCp = '', string $destCp = ''): string
    {
        return 'Retirar en sucursal';
    }

    public function getEta(): string
    {
        return '-';
    }

    public function isAvailable(string $originCp, string $destCp, float $distanceKm): bool
    {
        return true;
    }

    public function calculatePrice(string $originCp, string $destCp, float $distanceKm): float
    {
        return 0.0;
    }
}
