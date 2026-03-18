<?php

namespace App\Service\Shipping;

use App\Repository\PersonalizeRepository;

class StoreLocationService
{
    private PersonalizeRepository $personalizeRepository;

    public function __construct(PersonalizeRepository $personalizeRepository)
    {
        $this->personalizeRepository = $personalizeRepository;
    }

    public function getStorePostalCode(): string
    {
        $personalize = $this->personalizeRepository->findOneBy([]);
        
        if (!$personalize || !$personalize->getPostal()) {
            // As instructed, we shouldn't assume a value, 
            // but for safety in calculations we return an empty string 
            // which will be handled by the normalizer.
            return '';
        }

        return $personalize->getPostal();
    }
}
