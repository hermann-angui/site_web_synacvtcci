<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Unique;

class MemberOnlineRegistrationType extends AbstractType
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
                'required' => false,
                'constraints' => [
                    new Unique([
                        'message' => 'Email en doublon'
                    ]),
                ]
            ])

            ->add('IdNumber', TextType::class, [
                'label' => "N° Pièce d'identité",
                'mapped' => true,
                'required' => false,
                'constraints' => [
                    new Unique([
                        'message' => 'Numero de piece en doublon'
                    ]),
                ]
            ])
            ->add('IdType', ChoiceType::class, [
                'label' => 'Type de pièce',
                'mapped' => true,
                'required' => false,
             //   'attr' => ['class' => 'select2'],
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
//            ->add('etatCivil', ChoiceType::class, [
//                'label' => 'Etat civil',
//                'mapped' => true,
//                'required' => false,
//                'attr' => ['class' => 'select2'],
//                'choices' => [
//                    'MARIE(E)' => 'MARIE(E)',
//                    'CELIBATAIRE' => 'CELIBATAIRE',
//                    'DIVORCE(E)' => 'DIVORCE(E)',
//                    'VEUF(VE)' => 'VEUF(VE)',
//                ],
//                'empty_data' => null,
//                'data' => null,
//            ])
            ->add('IdDeliveryPlace',TextType::class, [
                'label' => "Délivré à",
                'mapped' => true,
                'required' => false
            ])
            ->add('IdDeliveryDate',DateType::class, [
                'label' => 'Délivré le',
                'attr' => ['class' => 'js-datepicker'],
                'widget' => 'single_text',
                'html5' => false,
                'mapped' => true,
                'required' => false,
                'format' =>  'dd/MM/yyyy',
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
                'attr' => ['class' => 'js-datepicker'],
                'widget' => 'single_text',
                'html5' => false,
                'mapped' => true,
                'required' => false,
                'format' =>  'dd/MM/yyyy',
                'years' => range($past->format('Y'), $end->format('Y')),
            ])
//            ->add('birth_city', TextType::class, [
//                'label' => 'Ville de naissance',
//                'mapped' => true,
//                'required' => false
//            ])
            ->add('drivingLicenseNumber', TextType::class, [
                'label' => 'Numéro du permis de conduire',
                'mapped' => true,
                'required' => false,
            ])

//            ->add('city', ChoiceType::class, [
//                'label' => "Ville",
//                'mapped' => true,
//                'required' => false,
//                'attr' => ['class' => 'select2'],
//                'choices' => [
//                    "ABIDJAN" => "ABIDJAN",
//                    "BOUAKE" => "BOUAKE",
//                    "YAMOUSSOUKRO" => "YAMOUSSOUKRO",
//                    "ABENGOUROU" => "ABENGOUROU",
//                    "BONDOUKOU" => "BONDOUKOU",
//                    "VAVOUA" => "VAVOUA",
//                    "KORHOGO" => "KORHOGO",
//                    "MAN" => "MAN",
//                    "SAN-PEDRO" => "SAN-PEDRO",
//                    "BASSAM" => "BASSAM",
//                    "BONOUA" => "BONOUA",
//                    "DALOA" => "DALOA"
//                ],
//                'empty_data' => null,
//                'data' => null,
//            ])
//            ->add('commune', ChoiceType::class, [
//                'label' => "Commune",
//                'mapped' => true,
//                'required' => false,
//                'attr' => ['class' => 'select2'],
//                'choices' => [
//                    "ABOBO" => "ABOBO",
//                    "ANYAMA" => "ANYAMA",
//                    "ATTECOUBE" => "ATTECOUBE",
//                    "MARCORY" => "MARCORY",
//                    "BINGERVILLE" => "BINGERVILLE",
//                    "COCODY" => "COCODY",
//                    "PLATEAU" => "PLATEAU",
//                    "KOUMASSI" => "KOUMASSI",
//                    "PORT-BOUET" => "PORT-BOUET",
//                    "TREICHVILLE" => "TREICHVILLE",
//                ],
//                'empty_data' => null,
//                'data' => null,
//            ])
//            ->add('quartier', TextType::class, [
//                'label' => "Quartier",
//                'mapped' => true,
//                'required' => false
//            ])
            ->add('mobile', TextType::class, [
                'label' => "Numéro Téléphone mobile",
                'attr' => ['class' => 'input-mask','data-inputmask' => "'mask': '9999999999'"],
                'mapped' => true,
                'required' => true,
            ])
            ->add('phone', TextType::class, [
                'label' => "Numéro Téléphone Fixe",
                'attr' => ['class' => 'input-mask','data-inputmask' => "'mask': '9999999999'"],
                'mapped' => true,
                'required' => false,
            ])
            ->add('whatsapp', TelType::class, [
                'label' => "Numéro Whatsapp",
                'attr' => ['class' => 'input-mask','data-inputmask' => "'mask': '9999999999'"],
                'mapped' => true,
                'required' => true,
            ])

            ->add('birth_locality', TextType::class, [
                'label' => "Lieu de naissance",
                'mapped' => true,
                'required' => false
            ])
            ->add('IdDeliveryAuthority', TextType::class, [
                'label' => "Délivré par",
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
