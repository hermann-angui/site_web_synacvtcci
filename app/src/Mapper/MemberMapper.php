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
        $memberAssetPath = "/var/www/html/public/members/" . $member->getReference() . "/";

        $memberDto->setFirstName($member->getFirstName());
        $memberDto->setId($member->getId());
        $memberDto->setLastName($member->getLastName());
        $memberDto->setPassword($member->getPassword());
        $memberDto->setWhatsapp($member->getWhatsapp());
        $memberDto->setQuartier($member->getQuartier());
        $memberDto->setPartnerFirstName($member->getPartnerFirstName());
        $memberDto->setPartnerLastName($member->getPartnerLastName());
        $memberDto->setPhone($member->getPhone());
        $memberDto->setMatricule($member->getMatricule());
        $memberDto->setReference($member->getReference());
        $memberDto->setEtatCivil($member->getEtatCivil());
        $memberDto->setAddress($member->getAddress());
        $memberDto->setIdDeliveryDate($member->getIdDeliveryDate());
        $memberDto->setIdDeliveryPlace($member->getIdDeliveryPlace());
        $memberDto->setMobile($member->getMobile());
        $memberDto->setCity($member->getCity());
        $memberDto->setSex($member->getSex());
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


        if($member->getPhoto()) $memberDto->setPhoto(new File($memberAssetPath . $member->getPhoto()));
        if($member->getPhotoPermisBack()) {
            $file = new File($memberAssetPath . $member->getPhotoPermisBack(), false);
            if($file->isFile()) $memberDto->setPhotoPermisBack($file);
        }
        if($member->getPhotoPermisFront()) {
            $file = new File($memberAssetPath . $member->getPhotoPermisFront(), false);
            if($file->isFile()) $memberDto->setPhotoPermisFront($file);
        }
        if($member->getPhotoPieceBack()) {
            $file = new File($memberAssetPath . $member->getPhotoPieceBack(), false);
            if($file->isFile())  $memberDto->setPhotoPieceBack( $file);
        }
        if($member->getPhotoPieceFront()) {
            $file = new File($memberAssetPath . $member->getPhotoPieceFront(), false);
            if($file->isFile())  $memberDto->setPhotoPieceFront($file);
        }
        if($member->getCardPhoto()) {
            $file = new File($memberAssetPath . $member->getCardPhoto(), false);
            if($file->isFile())  $memberDto->setCardPhoto($file);
        }

        return $memberDto;
    }

    public static function MapToMember(MemberRequestDto $memberDto): Member{
        $member = new Member();
        $member->setFirstName(strtoupper($memberDto->getFirstName()));
        $member->setFirstName(strtoupper($memberDto->getFirstName()));
        $member->setLastName(strtoupper($memberDto->getLastName()));
        $member->setPassword($memberDto->getPassword());
        $member->setWhatsapp($memberDto->getWhatsapp());
        $member->setQuartier(strtoupper($memberDto->getQuartier()));
        $member->setPartnerFirstName(strtoupper($memberDto->getPartnerFirstName()));
        $member->setPartnerLastName(strtoupper($memberDto->getPartnerLastName()));
        $member->setPhone($memberDto->getPhone());
        $member->setMatricule($memberDto->getMatricule());
        $member->setSex($memberDto->getSex());
        $member->setReference($memberDto->getReference());
        $member->setAddress($memberDto->getAddress());
        $member->setIdDeliveryDate($memberDto->getIdDeliveryDate());
        $member->setIdDeliveryPlace($memberDto->getIdDeliveryPlace());
        $member->setMobile($memberDto->getMobile());
        $member->setCity(strtoupper($memberDto->getCity()));
        $member->setCountry(strtoupper($memberDto->getCountry()));
        $member->setIdNumber(strtoupper($memberDto->getIdNumber()));
        $member->setIdType(strtoupper($memberDto->getIdType()));
        $member->setDrivingLicenseNumber(strtoupper($memberDto->getDrivingLicenseNumber()));
        $member->setBirthCity(strtoupper($memberDto->getBirthCity()));
        $member->setDateOfBirth($memberDto->getDateOfBirth());
        $member->setSubscriptionExpireDate($memberDto->getSubscriptionExpireDate());
        $member->setSubscriptionDate($memberDto->getSubscriptionDate());
        $member->setRoles($memberDto->getRoles());
        $member->setCommune(strtoupper($memberDto->getCommune()));
        $member->setCompany(strtoupper($memberDto->getCompany()));
        $member->setEmail($memberDto->getEmail());
        $member->setStatus(strtoupper($memberDto->getStatus()));
        $member->setTitre(strtoupper($memberDto->getTitre()));

        if($memberDto->getPhoto()) {
            $member->setPhoto($memberDto->getPhoto()->getFilename());
        }
        if($memberDto->getPhotoPermisBack()) {
            $member->setPhotoPermisBack($memberDto->getPhotoPermisBack()->getFilename());
        }
        if($memberDto->getPhotoPermisFront()) {
            $member->setPhotoPermisFront($memberDto->getPhotoPermisFront()->getFilename());
        }
        if($memberDto->getPhotoPieceBack()) {
            $member->setPhotoPieceBack($memberDto->getPhotoPieceBack()->getFilename());
        }
        if($memberDto->getPhotoPieceFront()) {
            $member->setPhotoPieceFront($memberDto->getPhotoPieceFront()->getFilename());
        }
        if($memberDto->getCardPhoto()) {
            $member->setCardPhoto($memberDto->getCardPhoto()->getFilename());
        }

        return $member;
    }

}
