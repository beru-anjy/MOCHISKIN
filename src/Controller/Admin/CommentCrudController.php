<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class CommentCrudController extends AbstractCrudController
{
    /**
     * Indique à EasyAdmin quelle entité ce controller gère.
     * Obligatoire — doit retourner le FQCN de l'entité.
     */
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    /**
     * configureCrud() — Paramètres globaux de ce CRUD
     * Titre des pages, champ de tri par défaut, recherche...
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Titre affiché sur la page liste
            ->setEntityLabelInSingular('Commentaire')
            ->setEntityLabelInPlural('Commentaires')
            // Tri par défaut : commentaires les plus récents en premier
            ->setDefaultSort(['createdAt' => 'DESC'])
            // Recherche sur le contenu du commentaire
            ->setSearchFields(['content']);
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

            // Contenu du commentaire
            TextareaField::new('content', 'Commentaire'),

            // Article associé — lien cliquable vers l'article
            AssociationField::new('article', 'Article'),

            // Auteur du commentaire
            AssociationField::new('author', 'Auteur'),

            // Champ d'approbation — renderAsSwitch() affiche un toggle on/off
            // C'est le champ principal de modération
            BooleanField::new('isApproved', 'Approuvé')
                ->renderAsSwitch(true),

            // Date de création — non modifiable
            DateTimeField::new('createdAt', 'Créé le')
                ->setFormat('dd/MM/Y HH:mm')
                ->hideOnForm(),
        ];
    }

    /**
     * configureFilters() — Filtres dans la barre latérale de la liste
     *
     * Permettent de filtrer les commentaires par statut, article, etc.
     * Disponibles uniquement sur la page liste (PAGE_INDEX).
     */
    public function configureFilters(
        \EasyCorp\Bundle\EasyAdminBundle\Config\Filters $filters
    ): \EasyCorp\Bundle\EasyAdminBundle\Config\Filters {
        return $filters
            // Filtre Approuvé / Non approuvé — très utile pour la modération
            ->add(BooleanFilter::new('isApproved', 'Approuvé'))
            // Filtre par article
            ->add(EntityFilter::new('article', 'Article'));
    }

    /**
     * configureActions() — Boutons d'action dans la liste et les formulaires
     *
     * EasyAdmin 4 fournit des actions par défaut :
     * - INDEX     : edit, delete
     * - DETAIL    : edit, delete, index (retour liste)
     * - NEW/EDIT  : saveAndReturn, saveAndContinue
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Ajoute le bouton 'Voir le détail' pour lire le commentaire complet
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}