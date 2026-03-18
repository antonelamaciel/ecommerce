<?php

namespace App\Service\Shipping\Carrier;

use App\Service\Shipping\ShippingCarrierInterface;

class MotoCarrier implements ShippingCarrierInterface
{
    public function getName(string $originCp = '', string $destCp = ''): string
    {
        return 'Envío por moto';
    }

    public function getEta(): string
    {
        return '1 día';
    }

    public function isAvailable(string $originCp, string $destCp, float $distanceKm): bool
    {
        // Don't show Moto if CP is identical, let the Local/National Courier handle it for 3000
        $numbers = preg_replace('/[^0-9]/', '', $originCp);
        $ocp = (int) substr($numbers, 0, 4);
        $numbers = preg_replace('/[^0-9]/', '', $destCp);
        $dcp = (int) substr($numbers, 0, 4);

        if ($ocp === $dcp) {
            return false;
        }

        return $distanceKm > 0 && $distanceKm < 30; 
    }

    public function calculatePrice(string $originCp, string $destCp, float $distanceKm): float
    {
        return 3500.0;
    }
}
