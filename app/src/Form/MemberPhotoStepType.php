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

class MemberPhotoStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('photo',FileType::class, [
                'required' => true,
                'label' => 'Photo',
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('tracking_code',TextType::class, [
                'required' => true,
                'label' => "Code de suivi dossier",
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
            ->add('mobile', TextType::class, [
                'label' => "Mobile",
                'attr' => ['class' => 'input-mask','data-inputmask' => "'mask': '9999999999'"],
                'mapped' => true,
                'required' => true
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
                'label' => "Reçu de paiement Orange Money) CNMCI (format .pdf) ",
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
                'attr' => ['class' => 'input-mask','data-inputmask' => "'mask': '**999999.9999.*99999'"],
                'data_class' =>  null,
                'mapped' => true,
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
