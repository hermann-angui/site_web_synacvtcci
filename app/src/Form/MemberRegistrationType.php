<?php

namespace App\Form;

use App\DTO\MemberRequestDto;
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
        $end = new \DateTime('- 18 years');
        $countries = array_combine(
            array_values(Countries::getNames()),
            array_values(Countries::getNames())
        );

        $builder
            ->add('photo',FileType::class, [
                'required' => false,
                'label' => 'Photo',
                'data_class' =>  null,
                'mapped' => false,
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
                'required' => false,
                'data' => 'Ivoirienne'
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
                'label' => 'Type de pièce',
                'mapped' => true,
                'required' => false,
                'choices' => [
                    'CNI' => 'CNI',
                    'CC' => 'CC',
                    'PASSEPORT' => 'PASSEPORT',
                    'CARTE DE RESIDENCE' => 'CARTE DE RESIDENCE',
                    'AUTRE' => 'AUTRE',
                ],
                'empty_data' => null,
                'data' => null,
            ])
            ->add('etatCivil', ChoiceType::class, [
                'label' => 'Etat civil',
                'mapped' => true,
                'required' => false,
                'choices' => [
                    'MARIE(E)' => 'MARIE(E)',
                    'CELIBATAIRE' => 'CELIBATAIRE',
                    'DIVORCE(E)' => 'DIVORCE(E)',
                    'VEUF(VE)' => 'VEUF(VE)',
                ],
                'empty_data' => null,
                'data' => null,
            ])
            ->add('IdDeliveryPlace',TextType::class, [
                'label' => "Délivré à",
                'mapped' => true,
                'required' => false
            ])
            ->add('IdDeliveryDate',DateType::class, [
                'label' => 'Délivré le',
                'mapped' => true,
                'required' => false,
                'format' => 'dd-MM-yyyy',
                'years' => range($past->format('Y'), $end->format('Y')),
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
                'format' => 'dd-MM-yyyy',
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
                'label' => "Copie scannée de la pièce (recto)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('photoPieceBack',FileType::class, [
                'required' => false,
                'label' => "Copie scannée de la pièce (verso)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('photoPermisFront',FileType::class, [
                'required' => false,
                'label' => "Copie scannée du permis de conduire (recto)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('photoPermisBack',FileType::class, [
                'required' => false,
                'label' => "Copie scannée du  de conduire (verso)",
                'data_class' =>  null,
                'mapped' => true,
            ])

            ->add('city', ChoiceType::class, [
                'label' => "Ville",
                'mapped' => true,
                'required' => false,
                'choices' => [
                    "ABIDJAN" => "ABIDJAN",
                    "BOUAKE" => "BOUAKE",
                    "YAMOUSSOUKRO" => "YAMOUSSOUKRO",
                    "ABENGOUROU" => "ABENGOUROU",
                    "BONDOUKOU" => "BONDOUKOU",
                    "VAVOUA" => "VAVOUA",
                    "KORHOGO" => "KORHOGO",
                    "MAN" => "MAN",
                    "SAN-PEDRO" => "SAN-PEDRO",
                    "BASSAM" => "BASSAM",
                    "BONOUA" => "BONOUA",
                    "DALOA" => "DALOA"
                ],
                'empty_data' => null,
                'data' => null,
            ])
            ->add('commune', ChoiceType::class, [
                'label' => "Commune",
                'mapped' => true,
                'required' => false,
                'choices' => [
                    "ABOBO" => "ABOBO",
                    "ANYAMA" => "ANYAMA",
                    "ATTECOUBE" => "ATTECOUBE",
                    "MARCORY" => "MARCORY",
                    "BINGERVILLE" => "BINGERVILLE",
                    "COCODY" => "COCODY",
                    "PLATEAU" => "PLATEAU",
                    "KOUMASSI" => "KOUMASSI",
                    "PORT-BOUET" => "PORT-BOUET",
                    "TREICHVILLE" => "TREICHVILLE",
                ],
                'empty_data' => null,
                'data' => null,
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
/*            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'required' => true,
                'mapped' => false,
                'choices' => [
                    'PENDING' => 'EN ATTENTE',
                    'VALIDATED' => 'VALIDER',
                ],
                'empty_data' =>  null,
                'data' => null,
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MemberRequestDto::class,
        ]);
    }
}
