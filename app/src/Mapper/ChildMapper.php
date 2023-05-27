<?php

namespace App\Mapper;

use App\DTO\ChildDto;
use App\DTO\MemberRequestDto;
use App\Entity\Child;
use App\Entity\Member;

class ChildMapper
{
    public static function MapToChildDto(MemberRequestDto $parent, Child $child): ChildDto{
        $childDto =  new ChildDto();
        $childDto->setLastName($child->getLastName());
        $childDto->setParent($parent);
        $childDto->setFirstName($child->getFirstName());
        $childDto->setLieuNaissance($child->getLieuNaissance());
        $childDto->setSex($child->getSex());
        return $childDto;
    }

    public static function MapToChild(Member $parent, ChildDto $childDto): Child{
        $child = new Child;
        $child->setLastName($childDto->getLastName());
        $child->setParent($parent);
        $child->setFirstName($childDto->getFirstName());
        $child->setLieuNaissance($childDto->getLieuNaissance());
        $child->setSex($childDto->getSex());
        return $child;
    }

}
