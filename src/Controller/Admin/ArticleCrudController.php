<?php

namespace App\Controller\Admin;

use App\Entity\Article;

// ── Champs EasyAdmin 4.x ──────────────────────────────────────────────
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;

// ── Filtres EasyAdmin 4.x ─────────────────────────────────────────────
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

// ── Configuration des actions ─────────────────────────────────────────
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class ArticleCrudController extends AbstractCrudController
{
    /**
     * Indique à EasyAdmin quelle entité ce controller gère.
     * Obligatoire — doit retourner le FQCN de l'entité.
     */
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    /**
     * configureCrud() — Paramètres globaux de ce CRUD
     * Titre des pages, champ de tri par défaut, nombre d'éléments...
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Titre affiché sur la page liste
            ->setEntityLabelInSingular('Article')
            ->setEntityLabelInPlural('Articles')
            // Champ utilisé pour la recherche globale (barre de recherche en haut)
            ->setSearchFields(['title', 'excerpt', 'content'])
            // Tri par défaut : publishedAt décroissant (articles récents en premier)
            ->setDefaultSort(['publishedAt' => 'DESC'])
            // Nombre de résultats par page dans la liste
            ->setPaginatorPageSize(20);
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

            // Titre de l'article — affiché partout
            TextField::new('title', 'Titre'),

            // Slug URL auto-généré depuis le titre
            // SlugField::new() génère automatiquement le slug
            // hideOnIndex() masque dans la liste pour gagner de la place
            SlugField::new('slug', 'Slug')
                ->setTargetFieldName('title')
                ->hideOnIndex(),

            // Résumé court — affiché en liste et formulaire
            TextareaField::new('excerpt', 'Résumé'),

            // Contenu complet avec éditeur WYSIWYG
            // hideOnIndex() car trop long pour la liste
            TextEditorField::new('content', 'Contenu')
                ->hideOnIndex()
                ->setNumOfRows(15),

            // Relation ManyToOne — dropdown autocomplete vers Category
            AssociationField::new('category', 'Catégorie')
                ->autocomplete(),

            // Relation ManyToMany — multi-select vers Tag
            AssociationField::new('tags', 'Tags')
                ->autocomplete()
                ->hideOnIndex(),

            // Auteur de l'article — relation ManyToOne vers User
            AssociationField::new('author', 'Auteur')
                ->autocomplete(),

            // Temps de lecture en minutes (calculé automatiquement ou saisi)
            IntegerField::new('readingTime', 'Lecture (min)')
                ->hideOnIndex(),

            // Date de publication — setFormat() adapte l'affichage
            // hideOnForm() car elle est définie dans le constructeur de l'entité
            DateTimeField::new('publishedAt', 'Publié le')
                ->setFormat('dd/MM/Y HH:mm')
                ->hideOnForm(),
        ];
    }

    /**
     * configureFilters() — Filtres dans la barre latérale de la liste
     *
     * Permettent de filtrer les articles par catégorie, date, etc.
     * Disponibles uniquement sur la page liste (PAGE_INDEX).
     */
    public function configureFilters(
        \EasyCorp\Bundle\EasyAdminBundle\Config\Filters $filters
    ): \EasyCorp\Bundle\EasyAdminBundle\Config\Filters {
        return $filters
            // Filtre par catégorie (liste déroulante)
            ->add(EntityFilter::new('category', 'Catégorie'))
            // Filtre par auteur
            ->add(EntityFilter::new('author', 'Auteur'))
            // Filtre par date de publication (plage de dates)
            ->add(DateTimeFilter::new('publishedAt', 'Date de publication'))
            // Filtre textuel sur le titre
            ->add(TextFilter::new('title', 'Titre'));
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
            // Ajoute le bouton 'Voir le détail' dans la liste
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            // Ajoute 'Sauvegarder et ajouter un autre' dans le formulaire
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
    }
}