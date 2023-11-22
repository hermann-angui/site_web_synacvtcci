<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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

        $villes = [
            "ABIDJAN" => "ABIDJAN",
            "ABENGOUROU" => "ABENGOUROU",
            "ALEPE" => "ALEPE",
            "GAGNOA" => "GAGNOA",
            "ADIAKE" => "ADIAKE",
            "ADJAME" => "ADJAME",
            "BETTIE" => "BETTIE",
            "AZAGUIE" => "AZAGUIE",
            "ATTINGUE" => "ATTINGUE",
            "ADZOPE" => "ADZOPE",
            "ADJOVILLE" => "ADJOVILLE",
            "AGNIBILEKRO" => "AGNIBILEKRO",
            "AKOUPE" => "AKOUPE",
            "BASSAM" => "BASSAM",
            "BONDOUKOU" => "BONDOUKOU",
            "NIAKARAMADOUGOU" => "NIAKARAMADOUGOU",
            "DIKODOUGOU" => "DIKODOUGOU",
            "BONOUA" => "BONOUA",
            "BOUNA" => "BOUNA",
            "NASSIAN" => "NASSIAN",
            "SANDEGUE" => "SANDEGUE",
            "BOUAKE" => "BOUAKE",
            "BIANKOUMA" => "BIANKOUMA",
            "BOUNDIALI" => "BOUNDIALI",
            "MBENGUE" => "MBENGUE",
            "MANKONO" => "MANKONO",
            "TEHINI" => "TEHINI",
            "DROPO" => "DROPO",
            "OUANGOLODOUGOU" => "OUANGOLODOUGOU",
            "BANGOLO" => "BANGOLO",
            "DAOUKRO" => "DAOUKRO",
            "DIANRA" => "DIANRA",
            "DUEKOUE" => "DUEKOUE",
            "DIDIEVI" => "DIDIEVI",
            "KATIOLA" => "KATIOLA",
            "KOUIBLY" => "KOUIBLY",
            "KONG" => "KONG",
            "FAKOBLY" => "FAKOBLY",
            "GRAND-BASSAM" => "GRAND-BASSAM",
            "ISSIA" => "ISSIA",
            "OUANINOU" => "ISSIA",
            "KORO" => "KORO",
            "BOTRO" => "BOTRO",
            "KOUNAHIRI" => "KOUNAHIRI",
            "BEOUMI" => "BEOUMI",
            "PRIKRO" => "PRIKRO",
            "TANDA" => "TANDA",
            "KOUNFAO" => "KOUNFAO",
            "TRANSUA" => "TRANSUA",
            "OUELLE" => "OUELLE",
            "ARRAH" => "ARRAH",
            "BONGOUANOU" => "BONGOUANOU",
            "BOCANDA" => "BOCANDA",
            "TIEBISSOU" => "TIEBISSOU",
            "SAKASSOU" => "SAKASSOU",
            "BOUAFLE" => "BOUAFLE",
            "SINFRA" => "SINFRA",
            "OUME" => "OUME",
            "DJEKANOU" => "DJEKANOU",
            "ATTIE" => "ATTIE",
            "M\'BATO" => "MBATO",
            "TAABO" => "TAABO",
            "TIAPOUM" => "TIAPOUM",
            "KANI" => "KANI",
            "KANIASSO" => "KANIASSO",
            "KOUTO" => "KOUTO",
            "MADINANI" => "MADINANI",
            "GBELEBAN" => "GBELEBAN",
            "MINIGNAN" => "MINIGNAN",
            "GRAND-LAHOU" => "GRAND-LAHOU",
            "ODIENNE" => "ODIENNE",
            "DABAKALA" => "DABAKALA",
            "FERKE" => "FERKE",
            "SIKENSI" => "SIKENSI",
            "SEGUELA" => "SEGUELA",
            "SEGUELON" => "SEGUELON",
            "SOUBRE" => "SOUBRE",
            "KOUASSI-KOUASSIKRO" => "KOUASSI-KOUASSIKRO",
            "TOUMODI" => "TOUMODI",
            "TOULEPLEU" => "TOULEPLEU",
            "SIPILOU" => "SIPILOU",
            "TOUBA" => "TOUBA",
            "TABOU" => "TABOU",
            "JACQUEVILLE" => "JACQUEVILLE",
            "TAI" => "TAI",
            "TAFIRE" => "TAFIRE",
            "MEAGUI" => "MEAGUI",
            "LAKOTA" => "LAKOTA",
            "ZIENOULA" => "ZIENOULA",
            "DIVO" => "DIVO",
            "FRESCO" => "FRESCO",
            "GUITRY" => "GUITRY",
            "ABOISSO" => "ABOISSO",
            "DALOA" => "DALOA",
            "KORHOGO" => "KORHOGO",
            "DABOU" => "DABOU",
            "MAN" => "MAN",
            "VAVOUA" => "VAVOUA",
            "SAMATIGUILA" => "SAMATIGUILA",
            "SAN-PEDRO" => "SAN-PEDRO",
            "SASSANDRA" => "SASSANDRA",
            "SINEMATIALI" => "SINEMATIALI",
            "YAMOUSSOUKRO" => "YAMOUSSOUKRO",
        ];
        $communes = [
            "ABOBO" => "ABOBO",
            "ANYAMA" => "ANYAMA",
            "ADJAME" => "ADJAME",
            "ATTECOUBE" => "ATTECOUBE",
            "MARCORY" => "MARCORY",
            "BINGERVILLE" => "BINGERVILLE",
            "COCODY" => "COCODY",
            "PLATEAU" => "PLATEAU",
            "KOUMASSI" => "KOUMASSI",
            "PORT-BOUET" => "PORT-BOUET",
            "TREICHVILLE" => "TREICHVILLE",
            "YOPOUGON" => "YOPOUGON",
        ];

        $builder
//            ->add('photo',FileType::class, [
//                'required' => false,
//                'label' => 'Photo',
//                'data_class' =>  null,
//                'mapped' => true,
//            ])
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
            ->add('referantLastName', TextType::class, [
                'label' => 'Nom personne à contacter',
                'mapped' => true,
                'required' => true
            ])
            ->add('referantFirstName', TextType::class, [
                'label' => 'Prénoms personne à contacter',
                'mapped' => true,
                'required' => true
            ])
            ->add('referantMobile', TextType::class, [
                'label' => 'Numéro Téléphone personne à contacter',
                'attr' => ['class' => 'input-mask', 'data-inputmask' => "'mask': '9999999999'"],
                'mapped' => true,
                'required' => true
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'mapped' => true,
                'required' => false,
                'empty_data' => null,
                'data' => null,
            ])
            ->add('company', ChoiceType::class, [
                'label' => 'Compagnie de VTC',
                'mapped' => true,
                'required' => true,
                'multiple' => true,
                'attr' => ['class' => 'select2-multiple'],
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
                'data' => 'IVOIRIENNE'
            ])
            ->add('whatsapp', TelType::class, [
                'label' => "Whatsapp",
                'attr' => ['class' => 'input-mask', 'data-inputmask' => "'mask': '9999999999'"],
                'mapped' => true,
                'required' => true,
            ])
            ->add('etatCivil', ChoiceType::class, [
                'label' => 'Situation matrimoniale',
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
                'data' => 'ONECI'
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
                'data' => 'Côte d\'Ivoire'
            ])
            ->add('birth_city', ChoiceType::class, [
                'label' => 'Ville de naissance',
                'mapped' => true,
                'attr' => ['class' => 'select2'],
                'required' => true,
                'choices' => $villes,
                'empty_data' => "ABIDJAN",
                'data' => null,
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
//            ->add('photoPieceFront',FileType::class, [
//                'required' => false,
//                'label' => "Copie scannée de la pièce (recto)",
//                'data_class' =>  null,
//                'mapped' => true,
//            ])
//            ->add('photoPieceBack',FileType::class, [
//                'required' => false,
//                'label' => "Copie scannée de la pièce (verso)",
//                'data_class' =>  null,
//                'mapped' => true,
//            ])
//            ->add('photoPermisFront',FileType::class, [
//                'required' => false,
//                'label' => "Copie scannée du permis de conduire (recto)",
//                'data_class' =>  null,
//                'mapped' => true,
//            ])
//            ->add('photoPermisBack',FileType::class, [
//                'required' => false,
//                'label' => "Copie scannée du  de conduire (verso)",
//                'data_class' =>  null,
//                'mapped' => true,
//            ])
//            ->add('paymentReceiptCnmci',FileType::class, [
//                'required' => false,
//                'label' => "Reçu de paiement Orange Money) CNMCI (format .pdf) ",
//                'data_class' =>  null,
//                'mapped' => true,
//            ])
//            ->add('paymentReceiptSynacvtcci',FileType::class, [
//                'required' => false,
//                'label' => "Reçu de paiement SYNACVTCCI",
//                'data_class' =>  null,
//                'mapped' => true,
//            ])
            ->add('payment_receipt_cnmci_code',TextType::class, [
                'required' => true,
                'label' => "Code reçu de paiement Orange money",
                'attr' => ['class' => 'input-mask','data-inputmask' => "'mask': '**999999.9999.*99999'"],
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('city', ChoiceType::class, [
                'label' => "Situation géographique de résidence",
                'mapped' => true,
                'required' => true,
                'attr' => ['class' => 'select2'],
                'choices' => $villes,
                'empty_data' => "ABIDJAN",
                'data' => null,
            ])
            ->add('commune', ChoiceType::class, [
                'label' => "Ville de résidence",
                'mapped' => true,
                'required' => true,
                'attr' => ['class' => 'select2'],
                'choices' => $communes,
                'empty_data' => null,
                'data' => null,
            ])
            ->add('postal_code', TextType::class, [
                'label' => "Boite postale",
                'mapped' => true,
                'required' => false
            ])
            ->add('quartier', TextType::class, [
                'label' => "Quartier de résidence",
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
            ->add('activity_geo_location', TextType::class, [
                'label' => "Situation géographique d'activité",
                'mapped' => true,
                'required' => true,
                'data' => 'ABIDJAN'
            ])
            ->add('activity_country_location', TextType::class, [
                'label' => "Pays d'activité",
                'mapped' => true,
                'required' => true,
                'empty_data' => "COTE D'IVOIRE",
                'data' => "COTE D'IVOIRE",
            ])
            ->add('activity_city_location', ChoiceType::class, [
                'label' => "Ville d'activité",
                'mapped' => true,
                'required' => true,
                'choices' => $villes,
                'empty_data' => "ABIDJAN",
                'data' => "ABIDJAN",

            ])
            ->add('activity_quartier_location', TextType::class, [
                'label' => "Quartier d'activité",
                'mapped' => true,
                'required' => true,
                'empty_data' => "ABIDJAN",
                'data' =>  "ABIDJAN",
            ])
            ->add('socioprofessionnelle_category', TextType::class, [
                'label' => "Catégorie socioprofessionnelle",
                'mapped' => true,
                'required' => true,
                'data' => 'ARTISAN',
                'empty_data' => "ARTISAN",
            ])
            ->add('activity', TextType::class, [
                'label' => "Activité",
                'mapped' => true,
                'required' => true,
                'data' => 'CHAUFFEUR VTC'
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
