<?php

namespace App\Form;

use App\Entity\Member;
use App\Repository\CommunesRepository;
use App\Repository\VillesRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MemberRegistrationType extends AbstractType
{


    public function __construct(private CommunesRepository $communesRepository, private VillesRepository $villesRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $data = $builder->getData();
        $past = new \DateTime('- 65 years');
        $end = new \DateTime('- 18 years');
//        $countries = array_combine(
//            array_values(Countries::getNames()),
//            array_values(Countries::getNames())
//        );

        $builder
            ->add('photo',FileType::class, [
                'required' => false,
                'label' => 'Photo',
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('tracking_code',TextType::class, [
                'required' => true,
                'label' => "Code de suivi dossier",
                'attr' => ['class' => 'input-mask','data-inputmask' => "'mask': '*****'"],
                'mapped' => true,
            ])
            ->add('sticker_code',TextType::class, [
                'required' => true,
                'label' => "N° Sticker",
                'attr' => ['class' => 'input-mask','data-inputmask' => "'mask': '*******'"],
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
            ])
            ->add('nationality', TextType::class, [
                'label' => "Nationalité",
                'mapped' => true,
                'required' => true,
                'data' => $data->getNationality() ?? "IVOIRIENNE"
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
                'attr' => ['placeholder' => "ONECI"],
                'data' => $data->getSocioprofessionnelleCategory()?? "ONECI"
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
                'empty_data' => 'H'
            ])
            ->add('is_syndicat_member', CheckboxType::class, [
                'label' => 'Voulez-vous adhérer au syndicat ?',
                'required' => false,
                'mapped' => true,
            ])
            ->add('has_paid_for_syndicat', CheckboxType::class, [
                'label' => "Payer votre adhésion maintenant (5 000 F) ?",
                'required' => false,
                'mapped' => true,
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
                'data' => $data->getBirthCountry() ?? "COTE D'IVOIRE"
            ])
            ->add('birth_city', ChoiceType::class, [
                'label' => 'Ville de naissance',
                'mapped' => true,
                'attr' => ['class' => 'select2'],
                'required' => true,
                'choices' => $this->villesRepository->findAllNames(),
            ])
            ->add('birth_city_other', TextType::class, [
                'label' => 'Saisir le nom de la ville',
                'mapped' => false,
                'required' => false,
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
                'label' => "Copie scannée du permis de conduire (verso)",
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('ficheEngagementSynacvtcciPdf',FileType::class, [
                'required' => false,
                'label' => "Fiche engagement",
                'data_class' =>  null,
                'mapped' => true,
                'constraints' => [
                    new File([
                        //  'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Le fichier doit être au format pdf',
                    ])
                ],
            ])
            ->add('formulaireCnmciPdf',FileType::class, [
                'required' => false,
                'label' => "Fiche engagement",
                'data_class' =>  null,
                'mapped' => true,
                'constraints' => [
                    new File([
                        //  'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Le fichier doit être au format pdf',
                    ])
                ],
            ])
            ->add('paymentReceiptSynacvtcciPdf',FileType::class, [
                'required' => false,
                'label' => "Fiche engagement",
                'data_class' =>  null,
                'mapped' => true,
                'constraints' => [
                    new File([
                        //  'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Le fichier doit être au format pdf',
                    ])
                ],
            ])
            ->add('paymentReceiptCnmciPdf',FileType::class, [
                'required' => false,
                'label' => "Reçu de paiement Orange Money) CNMCI (format .pdf) ",
                'data_class' =>  null,
                'mapped' => true,
                'constraints' => [
                    new File([
                        //  'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Le fichier doit être au format pdf',
                    ])
                ],
            ])
            ->add('scanDocumentIdentitePdf',FileType::class, [
                'required' => false,
                'label' => "Scan des documents d'identités i.e CNI, PERMIS, CC (format .pdf) ",
                'data_class' =>  null,
                'mapped' => true,
                'constraints' => [
                    new File([
                        // 'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Le fichier doit être au format pdf',
                    ])
                ],
            ])
            ->add('payment_receipt_cnmci_code',TextType::class, [
                'required' => true,
                'label' => "Code reçu de paiement Orange money",
                'attr' => ['class' => 'input-mask','data-inputmask' => "'mask': '**999999.9999.*99999'"],
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('commune', ChoiceType::class, [
                'label' => "Situation géographique de résidence",
                'mapped' => true,
                'required' => true,
                'attr' => ['class' => 'select2'],
             //   'choices' => $communes,
                'choices' => $this->communesRepository->findAllNames(),
                'data' => $data->getCommune() ?? null
            ])
            ->add('city', ChoiceType::class, [
                'label' => "Ville de résidence",
                'mapped' => true,
                'required' => true,
                'attr' => ['class' => 'select2'],
                'choices' => $this->villesRepository->findAllNames(),
                'data' => $data->getCity() ?? null
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
                'data' => $data->getActivityGeoLocation()?? 'ABIDJAN'
            ])
            ->add('activity_country_location', TextType::class, [
                'label' => "Pays d'activité",
                'mapped' => true,
                'required' => true,
                'data' => $data->getActivityCountryLocation()?? "COTE D'IVOIRE"
            ])
            ->add('activity_city_location', ChoiceType::class, [
                'label' => "Ville d'activité",
                'mapped' => true,
                'required' => true,
                'choices' => $this->villesRepository->findAllNames(),
                'data' => $data->getActivityCityLocation()?? "ABIDJAN"
            ])
            ->add('activity_quartier_location', TextType::class, [
                'label' => "Quartier d'activité",
                'mapped' => true,
                'required' => true,
                'data' => $data->getActivityQuartierLocation()?? "ABIDJAN"
            ])
            ->add('socioprofessionnelle_category', TextType::class, [
                'label' => "Catégorie socioprofessionnelle",
                'mapped' => true,
                'required' => true,
                'data' => $data->getSocioprofessionnelleCategory()?? "ARTISAN"
            ])
            ->add('activity', TextType::class, [
                'label' => "Activité",
                'mapped' => true,
                'required' => true,
                'data' => $data->getSocioprofessionnelleCategory()?? "CHAUFFEUR VTC"
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
            ->add('children',CollectionType::class, [
                    'entry_type' => ChildType::class,
                    'entry_options' => [
                        'label'         => false,
                    ],
                    'label'         => false,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false,  // Very important thing!
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

