<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $past = new \DateTime('- 65 years');
        $end = new \DateTime();
        $countries = array_combine(
            array_values(Countries::getNames()),
            array_values(Countries::getNames())
        );

        $builder
            ->add('photo',FileType::class, [
                'required' => false,
                'label' => 'Photo',
                'data_class' =>  null,
                'mapped' => true,
            ])


            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'mapped' => true,
                'required' => true
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénoms',
                'mapped' => true,
                'required' => true
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'mapped' => true,
                'required' => true
            ])
            ->add('titre', ChoiceType::class, [
                'label' => 'Titre',
                'mapped' => true,
                'required' => false,
                'choices' => Member::getTitres(),
                'empty_data' => null,
                'data' => null,
            ])
            ->add('company', ChoiceType::class, [
                'label' => 'Compagnie de VTC',
                'mapped' => true,
                'required' => false,
                'choices' => [
                    "YANGO" => "YANGO",
                    "UBER" => "UBER",
                    "HEECTH" => "HEECTH",
                    "LE TRANSPORTEUR" => "LE TRANSPORTEUR",
                    "IZIGO" => "IZIGO"
                ],
                'empty_data' => null,
                'data' => null,
            ])
            ->add('nationality', TextType::class, [
                'label' => "Nationalité",
                'mapped' => true,
                'required' => false
            ])
            ->add('whatsapp', TextType::class, [
                'label' => "Whatsapp",
                'mapped' => true,
                'required' => false
            ])
            ->add('IdNumber', TextType::class, [
                'label' => "N° Pièce d'identité (CNI, Passeport ou Carte consulaire)",
                'mapped' => true,
                'required' => false
            ])
            ->add('IdType', ChoiceType::class, [
                'label' => 'Type de la pièce',
                'mapped' => true,
                'required' => false,
                'choices' => [
                    'CNI' => 'CNI',
                    'PASSEPORT' => 'PASSEPORT',
                ],
                'empty_data' => null,
                'data' => null,
            ])
            ->add('sex', ChoiceType::class, [
                'label' => 'Sexe',
                'required' => true,
                'mapped' => true,
                'choices' => [
                    'Homme' => 'H',
                    'Femme' => 'F',
                ],
                'empty_data' => 'H',
                'data' => 'H',
            ])
            ->add('date_of_birth',DateType::class, [
                'label' => 'Date de naissance',
                'mapped' => true,
                'required' => false,
                'years' => range($past->format('Y'), $end->format('Y')),
            ])
            ->add('birth_city', TextType::class, [
                'label' => 'Lieu de naissance',
                'mapped' => true,
                'required' => false
            ])
            ->add('drivingLicenseNumber', TextType::class, [
                'label' => 'Numéro du permis de conduire',
                'mapped' => true,
                'required' => false
            ])
            ->add('photoPieceFront',FileType::class, [
                'required' => false,
                'label' => "Photo Piece d'identité (recto)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('photoPieceBack',FileType::class, [
                'required' => false,
                'label' => "Photo Piece d'identité (verso)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('photoPermisFront',FileType::class, [
                'required' => false,
                'label' => "Photo permis de conduire (recto)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('photoPermisBack',FileType::class, [
                'required' => false,
                'label' => "Photo permis de conduire (verso)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('country', ChoiceType::class, [
                'required' => false,
                'label' => 'Pays',
                'mapped' => true,
                'choices' => $countries,
                'choice_loader' => null
            ])
            ->add('city', TextType::class, [
                'label' => "Ville",
                'mapped' => true,
                'required' => false
            ])
            ->add('commune', TextType::class, [
                'label' => "Commune",
                'mapped' => true,
                'required' => false
            ])
            ->add('quartier', TextType::class, [
                'label' => "Quartier",
                'mapped' => true,
                'required' => false
            ])
            ->add('mobile', TextType::class, [
                'label' => "Tel Mobile",
                'mapped' => true,
                'required' => false
            ])
            ->add('phone', TextType::class, [
                'label' => "Tel fixe",
                'mapped' => true,
                'required' => false
            ])
            ->add('partner_first_name', TextType::class, [
                'label' => "Prénoms conjoint",
                'mapped' => true,
                'required' => false
            ])
            ->add('partner_last_name', TextType::class, [
                'label' => "Nom conjoint",
                'mapped' => true,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
