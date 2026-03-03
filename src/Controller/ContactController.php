<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\Mail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, \App\Repository\PersonalizeRepository $personalizeRepository): Response
    {
        $personalize = $personalizeRepository->findOneBy([]);
        $companyName = $personalize ? $personalize->getCompanyName() : 'Mi Ecommerce';

        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $datas = $form->getData();
            $content = "De parte de : {$datas['firstname']} {$datas['lastname']} <br> Mensaje : {$datas['content']} <br> Email: {$datas['email']}";
            
            $recipientEmail = ($personalize && $personalize->getEmail()) ? $personalize->getEmail() : 'antonelamaciel2024@gmail.com';
            
            $mail = new Mail();
            $success = $mail->send($recipientEmail, $companyName, "Nuevo mensaje de contacto - $companyName", $content);

            if ($success) {
                $this->addFlash('success_contact', '¡Gracias! Tu mensaje ha sido enviado correctamente. Te contactaremos muy pronto.');
                return $this->redirectToRoute('contact');
            } else {
                $this->addFlash('error', 'Lo sentimos, hubo un problema al enviar tu mensaje. Por favor, inténtalo de nuevo más tarde.');
            }
        }

        return $this->renderForm('contact/index.html.twig', [
            'form' => $form,
        ]);
    }
}
