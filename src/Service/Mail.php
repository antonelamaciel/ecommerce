<?php
namespace App\Service;

use App\Entity\Personalize;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class Mail 
{
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;

    public function __construct(MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }

    public function send(string $toEmail, string $toName, string $subject, string $content): bool
    {
        // Fetch personalization settings from DB
        $personalize = $this->entityManager->getRepository(Personalize::class)->findOneBy([]);
        $companyName = $personalize ? $personalize->getCompanyName() : 'Tu Tienda';
        $companyEmail = $personalize ? $personalize->getEmail() : 'antonelamaciel2024@gmail.com';

        $email = (new Email())
            ->from(new Address($companyEmail, $companyName))
            ->to(new Address($toEmail, $toName))
            ->subject($subject)
            ->html($content);

        try {
            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}