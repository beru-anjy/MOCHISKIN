<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ email — sert aussi d'identifiant de connexion
            ->add('email', EmailType::class, [
                'attr' => ['autocomplete' => 'email'],
            ])

            // ✅ AJOUT — prénom requis, mappé directement sur User::firstName
            ->add('firstName', TextType::class, [
                'attr' => ['autocomplete' => 'given-name'],
                'constraints' => [
                    new NotBlank(message: 'Le prénom est obligatoire.'),
                    new Length(max: 120),
                ],
            ])

            // ✅ AJOUT — nom requis, mappé directement sur User::lastName
            ->add('lastName', TextType::class, [
                'attr' => ['autocomplete' => 'family-name'],
                'constraints' => [
                    new NotBlank(message: 'Le nom est obligatoire.'),
                    new Length(max: 150),
                ],
            ])

            // Checkbox CGU — mapped: false car pas stocké en base
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(message: 'Vous devez accepter les conditions d\'utilisation.'),
                ],
            ])

            // Mot de passe — mapped: false car hashé manuellement dans le controller
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(message: 'Le mot de passe est obligatoire.'),
                    new Length(
                        min: 6,
                        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                        max: 4096,
                    ),
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