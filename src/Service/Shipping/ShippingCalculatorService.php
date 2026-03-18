<?php

namespace App\Service\Shipping;

use App\Service\Shipping\Resolver\ProvinceResolver;
use App\Service\Shipping\Carrier\MotoCarrier;
use App\Service\Shipping\Carrier\NationalCourierCarrier;
use App\Service\Shipping\Carrier\PickupCarrier;
use App\Service\Shipping\StoreLocationService;

class ShippingCalculatorService
{
    private ProvinceResolver $provinceResolver;
    private StoreLocationService $storeLocationService;
    private array $carriers;

    public function __construct(
        ProvinceResolver $provinceResolver,
        StoreLocationService $storeLocationService,
        MotoCarrier $motoCarrier,
        NationalCourierCarrier $nationalCourierCarrier,
        PickupCarrier $pickupCarrier
    ) {
        $this->provinceResolver = $provinceResolver;
        $this->storeLocationService = $storeLocationService;
        $this->carriers = [
            $pickupCarrier,
            $motoCarrier,
            $nationalCourierCarrier
        ];
    }

    public function calculateShipping(string $destCp, float $cartTotal): array
    {
        // 1. obtener código postal de la tienda (configurado en EasyAdmin)
        $storeCp = $this->storeLocationService->getStorePostalCode();

        // 2. normalizar código postal tienda y cliente
        $originCpInt = $this->provinceResolver->normalizeCp($storeCp);
        $destCpInt = $this->provinceResolver->normalizeCp($destCp);

        // 3. verificar si son iguales (Early Verification)
        if ($originCpInt > 0 && $originCpInt === $destCpInt) {
            $calculatedShippingPrice = 4800.0;
            
            // Implementación obligatoria: precio del envío nunca puede superar el 83% del valor del carrito
            $finalShippingPrice = min($calculatedShippingPrice, $cartTotal * 0.83);

            return [
                [
                    'id' => md5('Correo local'),
                    'name' => 'Correo local',
                    'price' => $finalShippingPrice,
                    'eta' => '1-2 días',
                    'type' => 'standard',
                    'description' => 'Entrega estimada: 1-2 días'
                ],
                [
                    'id' => md5('Retirar en sucursal'),
                    'name' => 'Retirar en sucursal',
                    'price' => 0.0,
                    'eta' => '-',
                    'type' => 'pickup',
                    'description' => 'Retiro gratuito en tienda'
                ]
            ];
        }

        // 5. si no son iguales, continuar con flujo habitual
        
        $provOrigin = $this->provinceResolver->getProvinceFromCp($originCpInt);
        $provDest = $this->provinceResolver->getProvinceFromCp($destCpInt);

        $coordOrig = $this->provinceResolver->getCapitalCoordinates($provOrigin);
        $coordDest = $this->provinceResolver->getCapitalCoordinates($provDest);

        $distanceKm = $this->provinceResolver->distanceKm(
            $coordOrig['lat'], $coordOrig['lon'],
            $coordDest['lat'], $coordDest['lon']
        );

        foreach ($this->carriers as $carrier) {
            if ($carrier->isAvailable($storeCp, $destCp, $distanceKm)) {
                $calculatedShippingPrice = $carrier->calculatePrice($storeCp, $destCp, $distanceKm);
                
                // Implementación obligatoria: precio del envío nunca puede superar el 83% del valor del carrito
                $finalShippingPrice = min($calculatedShippingPrice, $cartTotal * 0.83);

                $carrierName = $carrier->getName($storeCp, $destCp);
                $options[] = [
                    'id' => md5($carrierName),
                    'name' => $carrierName,
                    'price' => $finalShippingPrice,
                    'eta' => $carrier->getEta(),
                    'type' => ($carrier instanceof PickupCarrier) ? 'pickup' : 'standard', 
                    'description' => 'Entrega estimada: ' . $carrier->getEta()
                ];
            }
        }

        return $options;
    }
}
