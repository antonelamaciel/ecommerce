<?php

namespace App\Service\Shipping\Resolver;

class ProvinceResolver
{
    public function normalizeCp(string $cp): int
    {
        // 1. elimine cualquier letra
        // 2. deje solo números
        $numbers = preg_replace('/[^0-9]/', '', $cp);
        
        // 3. tome los primeros 4 dígitos
        $firstFour = substr($numbers, 0, 4);
        
        // 4. devuelva ese valor como entero
        return (int) $firstFour;
    }

    public function getProvinceFromCp(int $cp): string
    {
        if ($cp >= 1000 && $cp <= 1999) return 'CABA_GBA';
        if ($cp >= 2000 && $cp <= 2999) return 'Santa_Fe';
        if ($cp >= 3000 && $cp <= 3399) return 'Entre_Rios';
        if ($cp >= 3400 && $cp <= 3499) return 'Corrientes';
        if ($cp >= 3500 && $cp <= 3599) return 'Chaco';
        if ($cp >= 4000 && $cp <= 4999) return 'Tucuman_Salta_Jujuy';
        if ($cp >= 5000 && $cp <= 5999) return 'Cordoba';
        if ($cp >= 6000 && $cp <= 6999) return 'Buenos_Aires_Interior';
        if ($cp >= 8000 && $cp <= 8999) return 'Patagonia_Norte';
        if ($cp >= 9000 && $cp <= 9999) return 'Patagonia_Sur';

        return 'Otros';
    }

    public function getCapitalCoordinates(string $province): array
    {
        $coords = [
            'CABA_GBA' => ['lat' => -34.6037, 'lon' => -58.3816],
            'Santa_Fe' => ['lat' => -31.6333, 'lon' => -60.7000],
            'Entre_Rios' => ['lat' => -31.7333, 'lon' => -60.5333],
            'Corrientes' => ['lat' => -27.4667, 'lon' => -58.8333],
            'Chaco' => ['lat' => -27.4514, 'lon' => -58.9867],
            'Tucuman_Salta_Jujuy' => ['lat' => -26.8167, 'lon' => -65.2167],
            'Cordoba' => ['lat' => -31.4201, 'lon' => -64.1888],
            'Buenos_Aires_Interior' => ['lat' => -34.9214, 'lon' => -57.9545],
            'Patagonia_Norte' => ['lat' => -38.9516, 'lon' => -68.0591],
            'Patagonia_Sur' => ['lat' => -51.6226, 'lon' => -69.2181],
            'Otros' => ['lat' => -34.6037, 'lon' => -58.3816],
        ];
        
        return $coords[$province] ?? $coords['Otros'];
    }

    public function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth_radius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        
        return $earth_radius * $c;
    }
}
