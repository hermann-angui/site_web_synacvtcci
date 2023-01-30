<?php

namespace App\Service\Email;


use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    protected MailerInterface $mailer;

    function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(Email $email)
    {
        return true;
    }

}