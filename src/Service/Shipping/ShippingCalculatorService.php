<?php

namespace App\Service\Shipping;

use App\Service\Shipping\Resolver\ProvinceResolver;
use App\Service\Shipping\Carrier\MotoCarrier;
use App\Service\Shipping\Carrier\NationalCourierCarrier;
use App\Service\Shipping\Carrier\PickupCarrier;
use App\Service\Shipping\StoreLocationService;

use App\Repository\CarrierRepository;

class ShippingCalculatorService
{
    private ProvinceResolver $provinceResolver;
    private StoreLocationService $storeLocationService;
    private CarrierRepository $carrierRepository;
    private array $carriers;

    public function __construct(
        ProvinceResolver $provinceResolver,
        StoreLocationService $storeLocationService,
        CarrierRepository $carrierRepository,
        MotoCarrier $motoCarrier,
        NationalCourierCarrier $nationalCourierCarrier,
        PickupCarrier $pickupCarrier
    ) {
        $this->provinceResolver = $provinceResolver;
        $this->storeLocationService = $storeLocationService;
        $this->carrierRepository = $carrierRepository;
        $this->carriers = [
            $pickupCarrier,
            $motoCarrier,
            $nationalCourierCarrier
        ];
    }

    public function calculateShipping(string $destCp, float $cartTotal): array
    {
        $options = [];
        $dbCarriers = $this->carrierRepository->findAll();
        
        // 1. Separar carriers por tipo
        $standardCarriers = [];
        $longDistanceCarriers = [];
        foreach ($dbCarriers as $c) {
            if ($c->getType() === 'standard') $standardCarriers[] = $c;
            if ($c->getType() === 'long_distance') $longDistanceCarriers[] = $c;
        }

        // 2. Procesar carriers COSTO FIJO (standard)
        foreach ($standardCarriers as $carrier) {
            $options[] = [
                'id' => (string)$carrier->getId(),
                'name' => $carrier->getName(),
                'price' => (float)($carrier->getPrice() / 100),
                'eta' => 'Coordinar con el vendedor',
                'type' => 'standard',
                'description' => $carrier->getDescription()
            ];
        }

        // 3. Preparar variables para cálculo dinámico (CP)
        $storeCp = $this->storeLocationService->getStorePostalCode();
        $originCpInt = $this->provinceResolver->normalizeCp($storeCp);
        $destCpInt = $this->provinceResolver->normalizeCp($destCp);
        
        $provOrigin = $this->provinceResolver->getProvinceFromCp($originCpInt);
        $provDest = $this->provinceResolver->getProvinceFromCp($destCpInt);
        $coordOrig = $this->provinceResolver->getCapitalCoordinates($provOrigin);
        $coordDest = $this->provinceResolver->getCapitalCoordinates($provDest);
        $distanceKm = $this->provinceResolver->distanceKm($coordOrig['lat'], $coordOrig['lon'], $coordDest['lat'], $coordDest['lon']);

        // Buscamos el motor de cálculo nacional (el que calcula por distancia en distancias largas)
        /** @var \App\Service\Shipping\Carrier\NationalCourierCarrier $courierEngine */
        $courierEngine = null;
        foreach ($this->carriers as $c) {
            if ($c instanceof NationalCourierCarrier) {
                $courierEngine = $c;
                break;
            }
        }

        if ($courierEngine) {
            $calculatedPrice = $courierEngine->calculatePrice($storeCp, $destCp, $distanceKm);
            $finalPrice = min($calculatedPrice, $cartTotal * 0.83);

            // 4. Procesar carriers LARGA DISTANCIA (DB)
            foreach ($longDistanceCarriers as $carrier) {
                $options[] = [
                    'id' => (string)$carrier->getId(),
                    'name' => $carrier->getName(),
                    'price' => $finalPrice,
                    'eta' => $courierEngine->getEta(),
                    'type' => 'long_distance',
                    'description' => $carrier->getDescription()
                ];
            }

            // Fallback: si no hay long_distance en DB pero CP es lejano, mostrar el genérico
            if ($originCpInt !== $destCpInt && empty($longDistanceCarriers)) {
                $options[] = [
                    'id' => 'correo_nacional_fallback',
                    'name' => 'Correo Nacional',
                    'price' => $finalPrice,
                    'eta' => $courierEngine->getEta(),
                    'type' => 'standard',
                    'description' => 'Servicio de mensajería para largas distancias'
                ];
            }
        }

        // 5. Pickup (si el CP es el mismo)
        if ($originCpInt > 0 && $originCpInt === $destCpInt) {
            $options[] = [
                'id' => 'pickup_store',
                'name' => 'Retirar en local',
                'price' => 0.0,
                'eta' => '-',
                'type' => 'pickup',
                'description' => 'Retiro gratuito en tienda'
            ];
        }

        return $options;
    }
}
