<?php

namespace App\Service\Shipping;

interface ShippingCarrierInterface
{
    public function getName(string $originCp = '', string $destCp = ''): string;
    public function getEta(): string;
    public function isAvailable(string $originCp, string $destCp, float $distanceKm): bool;
    public function calculatePrice(string $originCp, string $destCp, float $distanceKm): float;
}
