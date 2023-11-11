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
                'required' => true,
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
            ->add('company', ChoiceType::class, [
                'label' => 'Compagnie de VTC',
                'mapped' => true,
                'required' => true,
//                'attr' => ['class' => 'select2'],
                'choices' => [
                    "YANGO" => "YANGO",
                    "UBER" => "UBER",
                    "HEECTH" => "HEECTH",
                    "LE TRANSPORTEUR" => "LE TRANSPORTEUR",
                    "IZIGO" => "IZIGO",
                    "TAXI JET" => "TAXI JET",
                    "TREIIZE TAXI" => "TREIIZE TAXI",
                    "GREEN VTC" => "GREEN VTC",
                    "IVOIRE TAXI" => "IVOIRE TAXI",
                    "SB DRIVE" => "SB DRIVE",
                    "AUTRE" => "AUTRE"
                ],
                'empty_data' => null,
                'data' => null,
            ])
            ->add('nationality', TextType::class, [
                'label' => "Nationalité",
                'mapped' => true,
                'required' => true,
                'data' => 'Ivoirienne'
            ])
            ->add('whatsapp', TelType::class, [
                'label' => "Whatsapp",
                'attr' => ['class' => 'input-mask', 'data-inputmask' => "'mask': '9999999999'"],
                'mapped' => true,
                'required' => true,
            ])
            ->add('etatCivil', ChoiceType::class, [
                'label' => 'Etat civil',
                'mapped' => true,
                'required' => true,
//                'attr' => ['class' => 'select2'],
                'choices' => [
                    'MARIE(E)' => 'MARIE(E)',
                    'CELIBATAIRE' => 'CELIBATAIRE',
                    'DIVORCE(E)' => 'DIVORCE(E)',
                    'VEUF(VE)' => 'VEUF(VE)',
                ],
                'empty_data' => null,
                'data' => null,
            ])
            ->add('IdType', ChoiceType::class, [
                'label' => 'Type de la pièce d\'identite (CNI ou Carte consulaire)',
                'mapped' => true,
                'required' => true,
//                'attr' => ['class' => 'select2'],
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
            ->add('IdNumber', TextType::class, [
                'label' => "N° Pièce d'identité",
                'mapped' => true,
                'required' => true,
            ])
            ->add('IdDeliveryPlace',TextType::class, [
                'label' => "Lieu d'établissement de la pièce d'identité",
                'mapped' => true,
                'required' => true,
            ])
            ->add('IdDeliveryAuthority',TextType::class, [
                'label' => "Autorité délivrant la pièce d'identité",
                'mapped' => true,
                'required' => true,
            ])
            ->add('IdDeliveryDate',DateType::class, [
                'label' => 'Délivré le',
                'attr' => ['class' => 'js-datepicker'],
                'widget' => 'single_text',
                'html5' => false,
                'mapped' => true,
                'required' => true,
                'format' => 'dd/MM/yyyy',
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
                'required' => true,
                'format' => 'dd/MM/yyyy',
                'years' => range($past->format('Y'), $end->format('Y')),
            ])
            ->add('birth_country', TextType::class, [
                'label' => 'Pays de naissance',
                'mapped' => true,
                'required' => true,
            ])
            ->add('birth_city', TextType::class, [
                'label' => 'Ville de naissance',
                'mapped' => true,
                'required' => true,
            ])
            ->add('birth_locality', TextType::class, [
                'label' => 'Localité de naissance',
                'mapped' => true,
                'required' => true,
            ])
            ->add('drivingLicenseNumber', TextType::class, [
                'label' => 'Numéro du permis de conduire',
                'mapped' => true,
                'required' => true,
            ])
            ->add('photoPieceFront',FileType::class, [
                'required' => true,
                'label' => "Copie scannée de la pièce (recto)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('photoPieceBack',FileType::class, [
                'required' => true,
                'label' => "Copie scannée de la pièce (verso)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('photoPermisFront',FileType::class, [
                'required' => true,
                'label' => "Copie scannée du permis de conduire (recto)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('photoPermisBack',FileType::class, [
                'required' => true,
                'label' => "Copie scannée du  de conduire (verso)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('paymentReceiptCnmci',FileType::class, [
                'required' => true,
                'label' => "Reçu de paiement CNMCI",
                'data_class' =>  null,
                'mapped' => true,
            ])
//            ->add('paymentReceiptSynacvtcci',FileType::class, [
//                'required' => false,
//                'label' => "Reçu de paiement SYNACVTCCI",
//                'data_class' =>  null,
//                'mapped' => true,
//            ])
            ->add('payment_receipt_cnmci_code',TextType::class, [
                'required' => true,
                'label' => "Code reçu de paiement Orange money",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('city', ChoiceType::class, [
                'label' => "Ville",
                'mapped' => true,
                'required' => true,
                'attr' => ['class' => 'select2'],
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
                'required' => true,
                'attr' => ['class' => 'select2'],
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
                'required' => true
            ])
            ->add('mobile', TextType::class, [
                'label' => "Mobile",
                'attr' => ['class' => 'input-mask','data-inputmask' => "'mask': '9999999999'"],
                'mapped' => true,
                'required' => true
            ])

            ->add('phone', TextType::class, [
                'label' => "Téléphone",
                'attr' => ['class' => 'input-mask','data-inputmask' => "'mask': '9999999999'"],
                'mapped' => true,
                'required' => true
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
            ->add('activity_geo_location', TextType::class, [
                'label' => "Situation géographique d'activité",
                'mapped' => true,
                'required' => true
            ])
            ->add('activity_country_location', TextType::class, [
                'label' => "Pays d'activité",
                'mapped' => true,
                'required' => true
            ])
            ->add('activity_city_location', TextType::class, [
                'label' => "Ville d'activité",
                'mapped' => true,
                'required' => true
            ])
            ->add('activity_quartier_location', TextType::class, [
                'label' => "Quartier d'activité",
                'mapped' => true,
                'required' => true
            ])
            ->add('socioprofessionnelle_category', TextType::class, [
                'label' => "Catégorie socioprofessionnelle",
                'mapped' => true,
                'required' => true
            ])
            ->add('activity', TextType::class, [
                'label' => "Activité",
                'mapped' => true,
                'required' => true
            ])
            ->add('activity_date_debut', DateType::class, [
                'label' => "Date debut d'activité",
                'attr' => ['class' => 'js-datepicker'],
                'widget' => 'single_text',
                'html5' => false,
                'mapped' => true,
                'required' => true,
                'format' => 'dd/MM/yyyy',
                'years' => range($past->format('Y'), $end->format('Y')),
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
