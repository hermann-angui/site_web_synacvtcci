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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserFormType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $past = new \DateTime('- 80 years');
        $end = new \DateTime();

        $countries = array_combine(array_values(Countries::getNames()), array_values(Countries::getNames()));

        $builder
            ->add('sex', ChoiceType::class, [
                'required' => false,
                'mapped' => true,
                'choices' => [
                    'monsieur' => 'Homme',
                    'madame' => 'Femme',
                ],
                'empty_data' => 'Homme',
                'data' => 'Homme',
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénoms',
                'mapped' => true,
                'required' => true
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'mapped' => true,
                'required' => true
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Téléphone',
                'mapped' => true,
                'required' => true
            ])
            ->add('dateofBirth', DateType::class, [
                'label' => 'Date de naissance',
                'mapped' => true,
                'years' => range($past->format('Y'), $end->format('Y')),
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'mapped' => true,
                'required' => true
            ])
            ->add('password', RepeatedType::class, [
                'label' => 'Mot de passe',
                'mapped' => true,
                'type' => PasswordType::class,
                'invalid_message' => $this->translator->trans('invalid_password'),
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('password_is_blank'),
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => $this->translator->trans('invalid_password_length'),
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])

            ->add('nationality', CountryType::class, [
                'label' => 'Votre nationalité',
                'mapped' => true,
                'required' => true,
                'choices' => $countries,
                'choice_loader' => null
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo',
                'mapped' => true,
                'required' => false
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => $this->translator->trans('general_term_gdpr'),
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => $this->translator->trans('invalid_password'),
                    ]),
                ],
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
