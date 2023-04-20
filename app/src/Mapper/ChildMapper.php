<?php

namespace App\Mapper;

use App\DTO\ChildDto;
use App\Entity\Child;

class ChildMapper
{
    public static function MapToChildDto(Child $child): ChildDto{
        $childDto =  new ChildDto();
        $childDto->setLastName($child->getLastName());
        $childDto->setParent($child->getParent());
        $childDto->setFirstName($child->getFirstName());
        $childDto->setLieuNaissance($child->getLieuNaissance());
        $childDto->setSex($child->getSex());
        return $childDto;
    }

    public static function MapToChild(ChildDto $childDto): Child{
        $child = new Child;
        $child->setLastName($childDto->getLastName());
        $child->setParent(MemberMapper::MapToMember($childDto->getParent()));
        $child->setFirstName($childDto->getFirstName());
        $child->setLieuNaissance($childDto->getLieuNaissance());
        $child->setSex($childDto->getSex());
        return $child;
    }

}
