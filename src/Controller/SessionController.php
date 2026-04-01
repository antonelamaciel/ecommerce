<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SessionController extends AbstractController
{
    #[Route('/api/session-check', name: 'api_session_check')]
    public function check(Security $security): JsonResponse
    {
        $user = $security->getUser();
        return new JsonResponse([
            'authenticated' => $user !== null,
        ]);
    }
}
