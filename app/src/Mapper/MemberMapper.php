<?php

namespace App\Mapper;

use App\DTO\MemberRequestDto;
use App\DTO\MemberResponseDto;
use App\Entity\Member;
use Symfony\Component\HttpFoundation\File\File;

class MemberMapper
{
    public static function MapToMemberRequestDto(Member $member): MemberRequestDto{
        $memberDto = new MemberRequestDto();
        $memberDto->setFirstName($member->getFirstName());
        $memberDto->setLastName($member->getLastName());
        $memberDto->setPassword($member->getPassword());
        $memberDto->setWhatsapp($member->getWhatsapp());
        $memberDto->setQuartier($member->getQuartier());
        $memberDto->setPartnerFirstName($member->getPartnerFirstName());
        $memberDto->setPartnerLastName($member->getPartnerLastName());
        $memberDto->setPhone($member->getPhone());
        $memberDto->setMatricule($member->getMatricule());
        $memberDto->setPhoto(new File($member->getPhoto()));
        $memberDto->setPhotoPermisBack(new File($member->getPhotoPermisBack()));
        $memberDto->setPhotoPermisFront(new File($member->getPhotoPermisFront()));
        $memberDto->setPhotoPieceBack(new File($member->getPhotoPieceBack()));
        $memberDto->setPhotoPieceFront(new File($member->getPhotoPieceFront()));
        $memberDto->setCardPhoto(new File($member->getCardPhoto()));
        $memberDto->setMobile($member->getMobile());
        $memberDto->setCity($member->getCity());
        $memberDto->setCountry($member->getCountry());
        $memberDto->setIdNumber($member->getIdNumber());
        $memberDto->setIdType($member->getIdType());
        $memberDto->setDrivingLicenseNumber($member->getDrivingLicenseNumber());
        $memberDto->setBirthCity($member->getBirthCity());
        $memberDto->setDateOfBirth($member->getDateOfBirth());
        $memberDto->setSubscriptionExpireDate($member->getSubscriptionExpireDate());
        $memberDto->setSubscriptionDate($member->getSubscriptionDate());
        $memberDto->setRoles($member->getRoles());
        $memberDto->setCommune($member->getCommune());
        $memberDto->setCompany($member->getCompany());
        $memberDto->setEmail($member->getEmail());
        $memberDto->setStatus($member->getStatus());
        $memberDto->setTitre($member->getTitre());

        foreach($member->getChildren() as $child){
            $memberDto->addChild($child);
        }

        return $memberDto;
    }

    public static function MapToMember(MemberRequestDto $memberDto): Member{
        $member = new Member();
        $member->setFirstName($memberDto->getFirstName());
        $member->setLastName($memberDto->getLastName());
        $member->setPassword($memberDto->getPassword());
        $member->setWhatsapp($memberDto->getWhatsapp());
        $member->setQuartier($memberDto->getQuartier());
        $member->setPartnerFirstName($memberDto->getPartnerFirstName());
        $member->setPartnerLastName($memberDto->getPartnerLastName());
        $member->setPhone($memberDto->getPhone());
        $member->setMatricule($memberDto->getMatricule());
        if($memberDto->getPhoto()) $member->setPhoto($memberDto->getPhoto());
        if($memberDto->getPhotoPermisBack()) $member->setPhotoPermisBack($memberDto->getPhotoPermisBack()->getFilename());
        if($memberDto->getPhotoPermisFront()) $member->setPhotoPermisFront($memberDto->getPhotoPermisFront()->getFilename());
        if($memberDto->getPhotoPieceBack()) $member->setPhotoPieceBack($memberDto->getPhotoPieceBack()->getFilename());
        if($memberDto->getPhotoPieceFront()) $member->setPhotoPieceFront($memberDto->getPhotoPieceFront()->getFilename());
        if($memberDto->getCardPhoto()) $member->setCardPhoto($memberDto->getCardPhoto()->getFilename());
        $member->setMobile($memberDto->getMobile());
        $member->setCity($memberDto->getCity());
        $member->setCountry($memberDto->getCountry());
        $member->setIdNumber($memberDto->getIdNumber());
        $member->setIdType($memberDto->getIdType());
        $member->setDrivingLicenseNumber($memberDto->getDrivingLicenseNumber());
        $member->setBirthCity($memberDto->getBirthCity());
        $member->setDateOfBirth($memberDto->getDateOfBirth());
        $member->setSubscriptionExpireDate($memberDto->getSubscriptionExpireDate());
        $member->setSubscriptionDate($memberDto->getSubscriptionDate());
        $member->setRoles($memberDto->getRoles());
        $member->setCommune($memberDto->getCommune());
        $member->setCompany($memberDto->getCompany());
        $member->setEmail($memberDto->getEmail());
        $member->setStatus($memberDto->getStatus());
        $member->setTitre($memberDto->getTitre());

        return $member;
    }

}
