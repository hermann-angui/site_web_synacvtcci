<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if(!$user instanceof User) return ;
        if($user->getIsDisable()) throw new CustomUserMessageAccountStatusException('user is disable');
    }

    public function checkPostAuth(UserInterface $user)
    {
        if(!$user instanceof User) return ;
        if($user->getIsDisable()) throw new CustomUserMessageAccountStatusException('user is disable');
    }
}
