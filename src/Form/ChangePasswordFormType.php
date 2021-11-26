<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
                'first_options'  => ['label' => 'Nouveau mot de passe', 'attr' => ['placeholder' => "Votre nouveau mot de passe"]],
                'second_options' => ['label' => 'Confirmation du mot de passe', 'attr' => ['placeholder' => "Confirmation du mot de passe"]],
            ])
            ->add('save', SubmitType::class, [
                'label' => "Confirmer",
                'row_attr' => [
                    'class' => 'text-center'
                ],
                'attr' => ['class' => 'float-md-end btn-primary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
