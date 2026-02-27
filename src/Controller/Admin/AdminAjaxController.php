<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AdminAjaxController extends AbstractController
{
    #[Route('/admin/ajax/subcategories/{id}', name: 'admin_ajax_subcategories', methods: ['GET'])]
    public function getSubcategories(?Category $category): JsonResponse
    {
        if (!$category) {
            return $this->json([]);
        }

        $data = [];
        foreach ($category->getSubcategories() as $sub) {
            $data[] = ['value' => strval($sub->getId()), 'text' => $sub->getName()];
        }
        
        return $this->json($data);
    }
}
