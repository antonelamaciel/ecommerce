<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class Mail 
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(string $toEmail, string $toName, string $subject, string $content): bool
    {
        $email = (new Email())
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