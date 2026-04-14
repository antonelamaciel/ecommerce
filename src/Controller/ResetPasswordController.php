<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Mail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    #[Route('/recuperar-contrasena', name: 'reset_password')]
    public function resetPassword(Request $request, EntityManagerInterface $em, Mail $mail, TokenGeneratorInterface $tokenGenerator): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                // Generate token
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $user->setResetTokenAt(new \DateTime());
                $em->flush();

                // Send email
                $url = $this->generateUrl('update_password', ['token' => $token], Response::HTTP_FOUND);
                $fullUrl = $request->getUriForPath($url);
                
                // For some reason getUriForPath might be tricky with absolute URLs in some setups, 
                // but let's try to build it manually if needed or use absolute URL.
                $fullUrl = $this->generateUrl('update_password', ['token' => $token], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

                $content = "Hola " . $user->getFirstname() . ",<br><br>";
                $content .= "Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:<br>";
                $content .= "<a href='" . $fullUrl . "'>Restablecer mi contraseña</a><br><br>";
                $content .= "Este enlace expirará en 1 hora.<br>";
                $content .= "Si no solicitaste este cambio, puedes ignorar este correo.";

                $mail->send($user->getEmail(), $user->getFirstname(), 'Restablecer tu contraseña', $content);
            }

            $this->addFlash('success', 'Si el correo proporcionado coincide con una cuenta, recibirás un enlace para restablecer tu contraseña en unos instantes.');
            return $this->redirectToRoute('reset_password');
        }

        return $this->render('security/reset_password.html.twig');
    }

    #[Route('/restablecer-contrasena/{token}', name: 'update_password')]
    public function updatePassword(string $token, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'El token de restablecimiento no es válido.');
            return $this->redirectToRoute('reset_password');
        }

        // Check expiration (1 hour)
        $now = new \DateTime();
        if ($user->getResetTokenAt()->getTimestamp() + 3600 < $now->getTimestamp()) {
            $this->addFlash('error', 'Tu solicitud de restablecimiento ha expirado.');
            return $this->redirectToRoute('reset_password');
        }


        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Las contraseñas no coinciden.');
                return $this->render('security/update_password.html.twig', ['token' => $token]);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setResetToken(null);
            $user->setResetTokenAt(null);
            $em->flush();

            $this->addFlash('success', 'Tu contraseña ha sido actualizada correctamente.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/update_password.html.twig', [
            'token' => $token
        ]);
    }
}
