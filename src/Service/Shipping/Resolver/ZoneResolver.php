<?php

namespace App\Service\Shipping\Resolver;

class ZoneResolver
{
    public function getZone(string $provOrigin, string $provDest, int $cpOrig, int $cpDest, float $distanceKm): int
    {
        // 1. Same CP -> Local
        if ($cpOrig === $cpDest) {
            return 1;
        }

        // 2. Distance < 30km -> Moto/Local (handled by carrier, but zone 1 price base)
        if ($distanceKm < 30) {
            return 1;
        }

        // 3. Same Province
        if ($provOrigin === $provDest) {
            return 2;
        }

        // 4. Neighboring / Regional logic
        if ($provOrigin === 'CABA_GBA') {
            if ($provDest === 'Buenos_Aires_Interior') return 2;
            if (in_array($provDest, ['Santa_Fe', 'Entre_Rios', 'Cordoba'])) return 3;
            if (in_array($provDest, ['Corrientes', 'Chaco', 'Tucuman_Salta_Jujuy'])) return 4;
            if (in_array($provDest, ['Patagonia_Norte', 'Patagonia_Sur'])) return 5;
        }

        return 4; // Default
    }

    public function getBasePriceForZone(int $zone): float
    {
        $prices = [
            1 => 4700.0,
            2 => 6000.0,
            3 => 12000.0,
            4 => 16000.0,
            5 => 20000.0,
        ];
        
        return $prices[$zone] ?? 20000.0;
    }
}
