<?php

namespace App\Twig;

use App\Entity\Personalize;
use App\Model\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private $em;
    private $cart;

    public function __construct(EntityManagerInterface $em, Cart $cart)
    {
        $this->em = $em;
        $this->cart = $cart;
    }

    public function getGlobals(): array
    {
        $personalize = $this->em->getRepository(Personalize::class)->findOneBy([], ['id' => 'DESC']);
        $categories = $this->em->getRepository(\App\Entity\Category::class)->findAll();
        
        $headerIsLight = true;
        if ($personalize && $personalize->getPrimarycolor()) {
            $headerIsLight = $this->isColorLight($personalize->getPrimarycolor());
        }

        $bannerBundles = $this->em->getRepository(\App\Entity\Bundle::class)->findBy(['isBannerActive' => true]);

        return [
            'personalize' => $personalize,
            'cartCount' => $this->cart->getFullQuantity(),
            'headerIsLight' => $headerIsLight,
            'allCategories' => $categories,
            'bannerBundles' => $bannerBundles
        ];
    }

    private function isColorLight($hex): bool
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        
        if (strlen($hex) != 6) return true;

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Use luminance formula
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b);
        return $luminance > 180; // 180 is a good threshold for "very light"
    }
}
