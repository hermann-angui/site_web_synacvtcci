<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $past = new \DateTime('- 80 years');
        $end = new \DateTime();
        $countries = array_combine(
            array_values(Countries::getNames()),
            array_values(Countries::getNames())
        );

        $builder
            ->add('email',EmailType::class,[
                'required' => false,
                'label' => 'Email',
                'mapped' => true,
            ])
            ->add('firstname',TextType::class,[
                'required' => false,
                'label' => 'Prénoms',
                'mapped' => true
            ])
            ->add('photo',FileType::class, [
                'required' => false,
                'label' => 'Photo',
                'data_class' =>  null,
                'mapped' => true,
            ])
            ->add('lastname',TextType::class,[
                'required' => false,
                'label' => 'Nom',
                'mapped' => true
            ])
            ->add('city',TextType::class,[
                'required' => false,
                'label' => 'Ville',
                'mapped' => true
            ])
            ->add('commune',TextType::class, [
                'required' => false,
                'label' => 'Commune',
                'mapped' => true
            ])
            ->add('quartier',TextType::class, [
                'required' => false,
                'label' => 'Quartier',
                'mapped' => true
            ])
            ->add('passport',TextType::class, [
                'required' => false,
                'label' => 'N° Passeport',
                'mapped' => true
            ])
            ->add('cni',TextType::class, [
                'required' => false,
                'label' => 'N° Passeport',
                'mapped' => true
            ])
            ->add('sex', ChoiceType::class, [
                'mapped' => true,
                'required' => false,
                'choices' => [
                    'monsieur' => 'Homme',
                    'madame' => 'Femme',
                ],
                'empty_data' => 'Homme',
                'data' => 'Homme',
            ])
            ->add('place_of_birth', TextType::class,[
                'required' => false,
                'label' => 'Lieu de naissance',
                'mapped' => true
            ])
            ->add('date_of_birth', DateType::class, [
                'required' => false,
                'label' => 'Date de naissance',
                'mapped' => true,
                'years' => range($past->format('Y'), $end->format('Y')),
            ])
            ->add('nationality', ChoiceType::class, [
                'required' => false,
                'label' => 'Pays',
                'mapped' => true,
                'choices' => $countries,
                'choice_loader' => null
            ])
            ->add('address', TextareaType::class,[
                'required' => false,
                'label' => 'Adresse',
                'mapped' => true
            ])
            ->add('phone_number', TelType::class,[
                'required' => false,
                'label' => 'Tel',
                'mapped' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
