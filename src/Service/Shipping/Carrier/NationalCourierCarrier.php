<?php

namespace App\Service\Shipping\Carrier;

use App\Service\Shipping\ShippingCarrierInterface;
use App\Service\Shipping\Resolver\ProvinceResolver;
use App\Service\Shipping\Resolver\ZoneResolver;

class NationalCourierCarrier implements ShippingCarrierInterface
{
    private ProvinceResolver $provinceResolver;
    private ZoneResolver $zoneResolver;

    public function __construct(ProvinceResolver $provinceResolver, ZoneResolver $zoneResolver)
    {
        $this->provinceResolver = $provinceResolver;
        $this->zoneResolver = $zoneResolver;
    }

    public function getName(string $originCp = '', string $destCp = ''): string
    {
        if ($originCp && $destCp) {
            $numbers = preg_replace('/[^0-9]/', '', $originCp);
            $ocp = (int) substr($numbers, 0, 4);
            $numbers = preg_replace('/[^0-9]/', '', $destCp);
            $dcp = (int) substr($numbers, 0, 4);
            
            if ($ocp === $dcp) {
                return 'Correo local';
            }
        }
        return 'Correo nacional';
    }

    public function getEta(): string
    {
        return '3-7 días';
    }

    public function isAvailable(string $originCp, string $destCp, float $distanceKm): bool
    {
        return true;
    }

    public function calculatePrice(string $originCp, string $destCp, float $distanceKm): float
    {
        $cpOrigInt = $this->provinceResolver->normalizeCp($originCp);
        $cpDestInt = $this->provinceResolver->normalizeCp($destCp);
        
        $provOrigin = $this->provinceResolver->getProvinceFromCp($cpOrigInt);
        $provDest = $this->provinceResolver->getProvinceFromCp($cpDestInt);
        
        $zone = $this->zoneResolver->getZone($provOrigin, $provDest, $cpOrigInt, $cpDestInt, $distanceKm);

        $basePrice = $this->zoneResolver->getBasePriceForZone($zone);
        
        // No distance adjustment for local same CP (Zone 1)
        $ajuste = ($zone === 1 && $cpOrigInt === $cpDestInt) ? 0 : ($distanceKm * 3.0); 
        $precioFinal = $basePrice + $ajuste;
        
        $precioMaximo = 25000.0;
        
        return min($precioFinal, $precioMaximo);
    }
}
