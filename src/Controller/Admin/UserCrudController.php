<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    /**
     * Injection du service de hashage des mots de passe.
     * Symfony 6+ utilise UserPasswordHasherInterface.
     */
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    /**
     * Indique à EasyAdmin quelle entité ce controller gère.
     * Obligatoire — doit retourner le FQCN de l'entité.
     */
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /**
     * configureCrud() — Paramètres globaux de ce CRUD
     * Titre des pages, champ de tri par défaut, recherche...
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Titre affiché sur la page liste
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            // Recherche sur email, prénom et nom
            ->setSearchFields(['email', 'firstName', 'lastName'])
            // Tri par défaut : utilisateurs les plus récents en premier
            ->setDefaultSort(['registrationDate' => 'DESC']);
    }

    /**
     * configureFields() — Définit les champs affichés
     *
     * $pageName permet d'adapter les champs selon la page :
     * - Crud::PAGE_INDEX  → liste
     * - Crud::PAGE_NEW    → formulaire de création
     * - Crud::PAGE_EDIT   → formulaire d'édition
     * - Crud::PAGE_DETAIL → page de détail
     */
    public function configureFields(string $pageName): iterable
    {
        // Champ mot de passe — affiché uniquement dans les formulaires
        // RepeatedType demande de saisir le mot de passe deux fois pour confirmation
        $passwordField = TextField::new('password', 'Mot de passe')
            ->onlyOnForms()
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type'           => PasswordType::class,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer'],
                'mapped'         => false, // Ne mappe pas directement sur l'entité
                'required'       => $pageName === Crud::PAGE_NEW,
            ]);

        return [
            // ID — visible en liste uniquement, pas dans les formulaires
            IdField::new('id')->hideOnForm(),

            // Email — identifiant unique de connexion
            EmailField::new('email', 'Email'),

            TextField::new('firstName', 'Prénom'),
            TextField::new('lastName', 'Nom'),

            $passwordField,

            // Rôles Symfony — tableau (ex: ["ROLE_ADMIN"])
            // ChoiceField avec multiple() pour sélectionner plusieurs rôles
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Utilisateur' => 'ROLE_USER',
                    'Admin'       => 'ROLE_ADMIN',
                    'Éditeur'     => 'ROLE_EDITOR',
                ])
                ->allowMultipleChoices()
                ->hideOnIndex(),

            // Compte actif ou désactivé (bannissement sans suppression)
            BooleanField::new('isActive', 'Actif')
                ->renderAsSwitch(true),

            // Relation vers le type de peau choisi à l'inscription
            AssociationField::new('skinType', 'Type de peau')
                ->hideOnIndex(),

            // Date d'inscription automatique — non modifiable
            DateTimeField::new('registrationDate', 'Inscrit le')
                ->setFormat('dd/MM/Y')
                ->hideOnForm(),
        ];
    }

    /**
     * createNewFormBuilder() — Personnalise le formulaire de création
     *
     * On écoute l'événement FormEvents::AFTER_SUBMIT pour hasher
     * le mot de passe avant que Doctrine ne persiste l'entité.
     */
    public function createNewFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context
    ): FormBuilderInterface {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordHashListener($formBuilder);
    }

    /**
     * createEditFormBuilder() — Même logique pour le formulaire d'édition
     */
    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context
    ): FormBuilderInterface {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordHashListener($formBuilder);
    }

    /**
     * addPasswordHashListener() — Ajoute l'écouteur de hashage
     *
     * Si le champ 'password' est rempli dans le formulaire,
     * le mot de passe est hashé avant l'enregistrement en base.
     * Si le champ est vide (édition sans changement de MDP), on ne touche à rien.
     */
    private function addPasswordHashListener(
        FormBuilderInterface $formBuilder
    ): FormBuilderInterface {
        $formBuilder->addEventListener(
            FormEvents::AFTER_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $user = $event->getData(); // Instance de User

                // Récupère le mot de passe saisi (non mappé sur l'entité)
                $plainPassword = $form->get('password')->getData();

                // Si un mot de passe a été saisi, on le hashe
                if ($plainPassword) {
                    $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashed);
                }
            }
        );

        return $formBuilder;
    }
}