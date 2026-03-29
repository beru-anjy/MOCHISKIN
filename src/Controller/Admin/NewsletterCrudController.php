<?php

namespace App\Controller\Admin;

use App\Entity\Newsletter;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class NewsletterCrudController extends AbstractCrudController
{
    /**
     * Indique à EasyAdmin quelle entité ce controller gère.
     * Obligatoire — doit retourner le FQCN de l'entité.
     */
    public static function getEntityFqcn(): string
    {
        return Newsletter::class;
    }

    /**
     * configureCrud() — Paramètres globaux de ce CRUD
     * Titre des pages, champ de tri par défaut, recherche...
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Titre affiché sur la page liste
            ->setEntityLabelInSingular('Abonné Newsletter')
            ->setEntityLabelInPlural('Abonnés Newsletter')
            // Recherche sur le prénom et l'email
            ->setSearchFields(['firstName', 'email'])
            // Abonnés les plus récents en premier
            ->setDefaultSort(['subscribedAt' => 'DESC']);
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

            // Prénom de l'abonné
            TextField::new('firstName', 'Prénom'),

            // Email de l'abonné — unique en base
            EmailField::new('email', 'Email'),

            // Abonnement actif ou désactivé (suite à désinscription)
            // renderAsSwitch() affiche un toggle visuel pour activer/désactiver
            BooleanField::new('isActive', 'Actif')
                ->renderAsSwitch(true),

            // Relation vers le type de peau renseigné à l'inscription
            AssociationField::new('skinType', 'Type de peau')
                ->hideOnIndex(),

            // Date d'inscription automatique — non modifiable
            DateTimeField::new('subscribedAt', 'Inscrit le')
                ->setFormat('dd/MM/Y HH:mm')
                ->hideOnForm(),
        ];
    }

    /**
     * configureFilters() — Filtres dans la barre latérale de la liste
     *
     * Permettent de filtrer les abonnés par statut.
     * Disponibles uniquement sur la page liste (PAGE_INDEX).
     */
    public function configureFilters(
        \EasyCorp\Bundle\EasyAdminBundle\Config\Filters $filters
    ): \EasyCorp\Bundle\EasyAdminBundle\Config\Filters {
        return $filters
            // Filtre actif / inactif — très utile pour voir les désabonnés
            ->add(BooleanFilter::new('isActive', 'Actif'));
    }

    /**
     * configureActions() — Boutons d'action dans la liste et les formulaires
     *
     * Les abonnés viennent uniquement du formulaire frontend,
     * donc la création manuelle depuis l'admin est désactivée.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Désactive la création manuelle depuis l'admin
            // Les abonnés viennent uniquement du formulaire frontend
            ->disable(Action::NEW);
    }
}