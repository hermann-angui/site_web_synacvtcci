<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginFormType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class,[
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('email_is_blank'),
                    ]),
                    new Email([
                        'message' => $this->translator->trans('email_not_valid'),
                    ]),
                ],
            ])
            ->add('password',PasswordType::class, [
                'invalid_message' => $this->translator->trans('invalid_password'),
                'required' => true,
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

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
