<?php

namespace App\Form;

use App\Entity\Artisan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtisanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName')
            ->add('firstName')
            ->add('date_of_birth')
            ->add('birth_city')
            ->add('nationality')
            ->add('sex')
            ->add('IdType')
            ->add('IdNumber')
            ->add('IdDeliveryDate')
            ->add('titre')
            ->add('mobile')
            ->add('phone')
            ->add('email')
            ->add('domicile')
            ->add('formationNiveauEtude')
            ->add('formationClass')
            ->add('formationDiplomeObtenu')
            ->add('formationApprenMetierNiveau')
            ->add('formationApprenMetierDiplomeObtenu')
            ->add('exploitantEtatCivil')
            ->add('formationApprenMetier')
            ->add('formationApprenMetierCNMCI')
            ->add('formationApprenMetierTypeCNMCI')
            ->add('companyMainActivity')
            ->add('companySecondaryActivity')
            ->add('companyName')
            ->add('companySigle')
            ->add('companyStartingDate')
            ->add('typeCompany')
            ->add('companyFiscalRegime')
            ->add('identifiantCnps')
            ->add('companyAdressPostal')
            ->add('companyTel')
            ->add('companyFax')
            ->add('companyDepartement')
            ->add('companyCommune')
            ->add('companySp')
            ->add('companyQuartier')
            ->add('companyVillage')
            ->add('companyLotNum')
            ->add('companyILotNum')
            ->add('companyTotalMen')
            ->add('companyTotalWomen')
            ->add('companyTotalMenApprentis')
            ->add('companyTotalWomenApprentis')
            ->add('numCompteContribuableEtabl')
            ->add('reprLastName')
            ->add('reprFirstName')
            ->add('reprQualification')
            ->add('reprSex')
            ->add('reprIDType')
            ->add('reprIDNum')
            ->add('reprIDDeliveryPlace')
            ->add('reprTitle')
            ->add('reprTel')
            ->add('reprEmail')
            ->add('reprLieuNais')
            ->add('reprEtatCivil')
            ->add('reprDomicile')
            ->add('reprIDDeliveryDate')
            ->add('reprDateNais')
            ->add('drivingLicenseNumber')
            ->add('country')
            ->add('city')
            ->add('commune')
            ->add('quartier')
            ->add('whatsapp')
            ->add('company')
            ->add('partner_first_name')
            ->add('partner_last_name')
            ->add('status')
            ->add('photoPiece_front')
            ->add('photoPiece_back')
            ->add('photoPermis_front')
            ->add('photoPermis_back')
            ->add('roles')
            ->add('password')
            ->add('created_at')
            ->add('modified_at')
            ->add('matricule')
            ->add('subscription_date')
            ->add('subscription_expire_date')
            ->add('photo')
            ->add('cardPhoto')
            ->add('reference')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artisan::class,
        ]);
    }
}
