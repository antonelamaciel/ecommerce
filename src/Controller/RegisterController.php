<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Security\LoginAuthenticator;
use App\Service\Mail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

/**
 * Formulaire d'inscription créé manuellement
 * Une fois inscris, l'utilisateur est automatiquement authentifié.
 */
class RegisterController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator, EntityManagerInterface $em, Mail $mail): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));

            $em->persist($user);
            $em->flush();

            // Envoi mail confirmation
            $content = "Hola {$user->getFirstname()} Gracias por registrarte en nuestra tienda! esperamos que encuentres lo que estas buscando, en caso de no encontrarlo, siempre estaremos dispuestos a ayudarte!";
            $mail->send($user->getEmail(), $user->getFirstname(), "Bienvenido a empresa", $content);

            // Loggin auto
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->renderForm('register/index.html.twig', [
            'form' => $form,
        ]);
    }
}
