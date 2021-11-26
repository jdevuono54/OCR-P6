<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
                'attr' => ['placeholder' => "Votre nom d'utilisateur"],
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez entrer votre nom d'utilisateur",
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => "Reinitialiser",
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
