<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * CRUD admin pour la gestion des utilisateurs.
 * Gère le hashage sécurisé du mot de passe à la création et à l'édition.
 */
class UserCrudController extends AbstractCrudController
{
    // UserPasswordHasherInterface est injecté automatiquement par Symfony (autowiring)
    // Il sert uniquement à hasher le mot de passe avant persistance en base
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setSearchFields(['email', 'firstName', 'lastName'])
            ->setDefaultSort(['registrationDate' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        // ⚠️ 'mapped' => false : le champ n'est PAS lié directement à l'entité.
        // Le mot de passe en clair est récupéré manuellement dans addPasswordHashListener()
        // pour être hashé avant d'être affecté à $user->setPassword().
        // 'required' est true uniquement en création, false en édition (champ optionnel)
        $passwordField = TextField::new('password', 'Mot de passe')
            ->onlyOnForms()
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmer'],
                'mapped' => false,
                'required' => Crud::PAGE_NEW === $pageName,
            ]);

        return [
            // L'ID est affiché en liste mais masqué dans les formulaires
            // car il est auto-généré par la base de données — on ne le saisit jamais
            IdField::new('id')->hideOnIndex()->hideOnForm(),

            EmailField::new('email', 'Email'),
            TextField::new('firstName', 'Prénom'),
            TextField::new('lastName', 'Nom'),
            $passwordField,

            // allowMultipleChoices() = un user peut avoir plusieurs rôles simultanément
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Utilisateur' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                    'Éditeur' => 'ROLE_EDITOR',
                ])
                ->allowMultipleChoices()
                ->hideOnIndex(),

            // renderAsSwitch() = affiche un toggle ON/OFF plutôt qu'une checkbox
            BooleanField::new('isActive', 'Actif')->renderAsSwitch(true),

            // ⚠️ SkinType doit avoir __toString() pour s'afficher dans le dropdown
            AssociationField::new('skinType', 'Type de peau')->hideOnIndex(),

            // hideOnForm() = date générée automatiquement, non modifiable par l'admin
            DateTimeField::new('registrationDate', 'Inscrit le')
                ->setFormat('dd/MM/Y')
                ->hideOnForm(),
        ];
    }

    // On surcharge les deux builders (NEW et EDIT) pour y brancher
    // le listener de hashage du mot de passe dans les deux cas
    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, \EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context): FormBuilderInterface
    {
        return $this->addPasswordHashListener(parent::createNewFormBuilder($entityDto, $formOptions, $context));
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, \EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context): FormBuilderInterface
    {
        return $this->addPasswordHashListener(parent::createEditFormBuilder($entityDto, $formOptions, $context));
    }

    /**
     * Écoute POST_SUBMIT (après soumission du formulaire) pour hasher le mot de passe.
     * Si le champ est vide en édition → on ne fait rien, l'ancien mot de passe est conservé.
     * ⚠️ POST_SUBMIT remplace AFTER_SUBMIT qui a été supprimé en Symfony 7+.
     */
    private function addPasswordHashListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $event->getData();
            $plainPassword = $form->get('password')->getData();

            // On hashe uniquement si un mot de passe a été saisi
            // (en édition, champ vide = on garde l'ancien hash)
            if ($plainPassword) {
                $user->setPassword(
                    $this->passwordHasher->hashPassword($user, $plainPassword)
                );
            }
        });

        return $formBuilder;
    }
}
