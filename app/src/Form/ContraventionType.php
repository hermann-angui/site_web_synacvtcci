<?php

namespace App\Form;

use App\Entity\Contravention;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContraventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contraventionNumero', TextType::class,[
                'label' => 'Numéro de contravention',
                'required' => false,
                'mapped' => true,
            ])
            ->add('nomControleur', TextType::class,[
                'label' => 'Nom',
                'required' => false,
                'mapped' => true,
            ])
            ->add('contrevenantNom', TextType::class,[
                'label' => 'Nom et prénoms',
                'required' => false,
                'mapped' => true,
            ])
            ->add('contrevenantTelephone', TextType::class,[
                'label' => 'N° Téléphone',
                'required' => false,
                'mapped' => true,
            ])
            ->add('contrevenantActivites', ChoiceType::class, [
                'label' => 'Activités artisanales',
                'multiple' => true,
                'expanded' => true,
                'choices'  => [
                    'VTC' => "VTC",
                    'TAXI COMPTEUR' => 'TAXI COMPTEUR',
                    'TAXI COMMUNAL' => 'TAXI COMMUNAL',
                    'MOTO TAXI' => 'MOTO TAXI',
                    'LIVREUR' => 'LIVREUR',
                    'TRICYCLE' => 'TRICYCLE',
                ]
              ]
            )
            ->add('contrevenantNumeroPermis', TextType::class,[
                'label' => 'N° Permis de conduire',
                'required' => false,
                'mapped' => true,
            ])
            ->add('montantContravention', TextType::class,[
                'label' => 'Montant',
                'required' => false,
                'mapped' => true,
            ])
            ->add('infractionType', TextType::class,[
                'label' => 'Type',
                'required' => false,
                'mapped' => true,
            ])
            ->add('infractionDate', DateType::class,[
                'label' => 'Date',
                'required' => false,
                'mapped' => true,
            ])
            ->add('infractionLieu', TextType::class,[
                'label' => 'Lieu',
                'required' => false,
                'mapped' => true,
            ])
            ->add('vehiculeImmatriculation', TextType::class,[
                'label' => "Numéro plaque d'immatriculation",
                'required' => false,
                'mapped' => true,
            ])
            ->add('vehiculeMarque', TextType::class,[
                'label' => 'Marque',
                'required' => false,
                'mapped' => true,
            ])
            ->add('rapportAgent', TextareaType::class,[
                'label' => "RAPPORT DE L'AGENT:",
                'required' => false,
                'mapped' => true,
            ])



        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contravention::class,
        ]);
    }
}
