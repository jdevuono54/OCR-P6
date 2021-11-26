<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
                'attr' => ['placeholder' => "Votre nom d'utilisateur"],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => "Le nom d'utilisateur ne peut pas être vide",
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => "Votre nom d'utilisateur doit faire {{ limit }} caractères minimum",
                        'max' => 20,
                        'maxMessage' => "Votre nom d'utilisateur doit faire {{ limit }} caractères maximum",
                    ]),
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => "Adresse email",
                'attr' => ['placeholder' => "Votre adresse email"],
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => "L'adresse email ne peut pas être vide",
                    ])
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'invalid_message' => 'Les mots de passes ne correspondent pas',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le mot de passe ne peut pas être vide',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire {{ limit }} caractères minimum',
                        'max' => 4096,
                        'maxMessage' => "Votre mot de passe doit faire {{ limit }} caractères maximum",
                    ]),
                ],
                'first_options'  => ['label' => 'Mot de passe', 'attr' => ['placeholder' => "Votre mot de passe"]],
                'second_options' => ['label' => 'Confirmation du mot de passe', 'attr' => ['placeholder' => "Confirmation du mot de passe"]],
            ])
            ->add('save', SubmitType::class, [
                'label' => "S'incrire",
                'row_attr' => [
                    'class' => 'text-center'
                ],
                'attr' => ['class' => 'float-md-end btn-primary']
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
