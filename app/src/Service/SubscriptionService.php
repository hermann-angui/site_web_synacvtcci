<?php

namespace App\Service;


use App\DTO\UserDto;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SubscriptionService
{
    protected MailerInterface $mailer;

    function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function subscribeNewMember(UserDto $member)
    {
        return true;
    }

    public function removeMember(UserDto $member)
    {
        return true;
    }

    public function subscribeNewStaff(UserDto $member)
    {
        return true;
    }

    public function removeStaff(UserDto $member)
    {
        return true;
    }

}