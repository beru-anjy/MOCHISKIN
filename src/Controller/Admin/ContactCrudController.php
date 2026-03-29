<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class ContactCrudController extends AbstractCrudController
{
    /**
     * Indique à EasyAdmin quelle entité ce controller gère.
     * Obligatoire — doit retourner le FQCN de l'entité.
     */
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    /**
     * configureCrud() — Paramètres globaux de ce CRUD
     * Titre des pages, champ de tri par défaut, recherche...
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Titre affiché sur la page liste
            ->setEntityLabelInSingular('Message Contact')
            ->setEntityLabelInPlural('Messages Contact')
            // Recherche sur le nom, email, sujet et contenu du message
            ->setSearchFields(['fullName', 'email', 'subject', 'message'])
            // Messages les plus récents en premier
            ->setDefaultSort(['sentAt' => 'DESC']);
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
        return [
            // ID — visible en liste uniquement, pas dans les formulaires
            IdField::new('id')->hideOnForm(),

            // Nom complet de la personne qui a contacté
            TextField::new('fullName', 'Nom complet'),

            // Email pour répondre
            EmailField::new('email', 'Email'),

            // Sujet sélectionné dans le formulaire contact
            TextField::new('subject', 'Sujet'),

            // Contenu du message — trop long pour la liste
            TextareaField::new('message', 'Message')
                ->hideOnIndex()
                ->setNumOfRows(5),

            // Marqué comme lu par l'admin
            BooleanField::new('isRead', 'Lu')
                ->renderAsSwitch(true),

            // Une réponse a été envoyée
            BooleanField::new('isReplied', 'Répondu')
                ->renderAsSwitch(true),

            // Date d'envoi du message — non modifiable
            DateTimeField::new('sentAt', 'Envoyé le')
                ->setFormat('dd/MM/Y HH:mm')
                ->hideOnForm(),
        ];
    }

    /**
     * configureFilters() — Filtres dans la barre latérale de la liste
     *
     * Permettent de filtrer les messages par statut de lecture et de réponse.
     * Disponibles uniquement sur la page liste (PAGE_INDEX).
     */
    public function configureFilters(
        \EasyCorp\Bundle\EasyAdminBundle\Config\Filters $filters
    ): \EasyCorp\Bundle\EasyAdminBundle\Config\Filters {
        return $filters
            // Filtre les messages non lus — priorité de traitement
            ->add(BooleanFilter::new('isRead', 'Lu'))
            // Filtre les messages sans réponse
            ->add(BooleanFilter::new('isReplied', 'Répondu'));
    }

    /**
     * configureActions() — Boutons d'action dans la liste et les formulaires
     *
     * Les messages sont créés uniquement via le formulaire frontend.
     * L'édition est désactivée pour préserver l'intégrité des messages reçus.
     * Seule la lecture via DETAIL est autorisée.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Les messages sont créés uniquement via le formulaire frontend
            // On désactive la création depuis l'admin
            ->disable(Action::NEW)
            // On désactive l'édition — les messages ne doivent pas être modifiés
            // Seuls isRead et isReplied peuvent être changés via le DETAIL
            ->disable(Action::EDIT)
            // On garde DETAIL pour lire le message complet
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}