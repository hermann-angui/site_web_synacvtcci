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
                'label' => 'Sexe',
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
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'mapped' => true,
                'required' => true
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Role',
                'mapped' => false,
                'required' => false,
                'choices' => [
                    "AGENT" => "ROLE_AGENT",
                    "DISTRICT" => "ROLE_DISTRICT",
                    "ADMININISTRATEUR" => "ROLE_ADMIN",
                    "SUPER ADMINISTRATEUR" => "ROLE_SUPER_ADMIN",
                ],
                'empty_data' => null,
                'data' => null,
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
            ->add('photo', FileType::class, [
                'label' => 'Photo',
                'mapped' => true,
                'required' => false
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
