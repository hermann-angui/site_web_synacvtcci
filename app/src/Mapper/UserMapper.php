<?php

namespace App\Mapper;

use App\DTO\UserDto;
use App\Entity\User;

class UserMapper
{

    public static function MapToUserDto(User $user): UserDto{
        $userDto =  new UserDto();
        $userDto->set($user->getLastName());
        $userDto->setParent($user->getParent());
        $userDto->setFirstName($user->getFirstName());
        $userDto->setLieuNaissance($user->getLieuNaissance());
        $userDto->setSex($user->getSex());
        return $userDto;
    }

    public static function MapToChild(UserDto $childDto): User{
        $child = new User();
        $child->setLastName($childDto->getLastName());
        $child->setParent($childDto->getParent());
        $child->setFirstName($childDto->getFirstName());
        $child->setLieuNaissance($childDto->getLieuNaissance());
        $child->setSex($childDto->getSex());
        return $child;
    }
}
