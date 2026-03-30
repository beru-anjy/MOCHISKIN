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
     * Obligatoire — doit retourner le FQCN (Full Qualified Class Name) de l'entité.
     */
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    /**
     * configureCrud() — Paramètres globaux de ce CRUD
     * Titre des pages, champ de tri par défaut, nombre d'éléments par page...
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Libellés affichés dans l'interface admin (singulier / pluriel)
            ->setEntityLabelInSingular('Article')
            ->setEntityLabelInPlural('Articles')
            // Champs utilisés pour la barre de recherche globale en haut de la liste
            ->setSearchFields(['title', 'excerpt', 'content'])
            // Tri par défaut : articles les plus récents en premier
            ->setDefaultSort(['publishedAt' => 'DESC'])
            // Nombre de résultats affichés par page dans la liste
            ->setPaginatorPageSize(20);
    }

    /**
     * configureFields() — Définit les champs affichés selon la page
     *
     * $pageName permet d'adapter les champs selon le contexte :
     * - Crud::PAGE_INDEX  → liste des articles
     * - Crud::PAGE_NEW    → formulaire de création
     * - Crud::PAGE_EDIT   → formulaire d'édition
     * - Crud::PAGE_DETAIL → page de détail (lecture seule)
     *
     * Les méthodes hideOnIndex(), hideOnForm(), onlyOnForms()
     * permettent de contrôler la visibilité champ par champ.
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            // ID auto-incrémenté — affiché en liste, masqué dans les formulaires
            IdField::new('id')->hideOnForm(),

            // Titre — affiché partout (liste, formulaires, détail)
            TextField::new('title', 'Titre'),

            // Slug URL auto-généré à partir du champ "title"
            // setTargetFieldName('title') = surveille le champ title en temps réel
            // hideOnIndex() = masqué dans la liste pour ne pas surcharger l'affichage
            SlugField::new('slug', 'Slug')
                ->setTargetFieldName('title')
                ->hideOnIndex(),

            // Résumé court de l'article — affiché en liste et dans les formulaires
            TextareaField::new('excerpt', 'Résumé'),

            // Contenu complet avec éditeur WYSIWYG (What You See Is What You Get)
            // hideOnIndex() = trop long pour s'afficher dans la liste
            // setNumOfRows(15) = hauteur de l'éditeur dans le formulaire
            TextEditorField::new('content', 'Contenu')
                ->hideOnIndex()
                ->setNumOfRows(15),

            // Catégorie de l'article — relation ManyToOne vers l'entité Category
            // autocomplete() = active la recherche AJAX dans le dropdown
            AssociationField::new('category', 'Catégorie')
                ->autocomplete(),

            // Tags de l'article — relation ManyToMany vers l'entité Tag
            // autocomplete() = recherche AJAX multi-sélection
            // hideOnIndex() = masqué en liste (trop verbeux)
            AssociationField::new('tags', 'Tags')
                ->autocomplete()
                ->hideOnIndex(),

            // Auteur — relation ManyToOne vers l'entité User
            // autocomplete() = recherche par nom/email dans le dropdown
            AssociationField::new('author', 'Auteur')
                ->autocomplete(),

            // Temps de lecture estimé en minutes — saisi manuellement ou calculé
            // hideOnIndex() = masqué en liste pour alléger l'affichage
            IntegerField::new('readingTime', 'Lecture (min)')
                ->hideOnIndex(),

            // Date de publication — définie automatiquement dans le constructeur de Article
            // hideOnForm() = non modifiable depuis les formulaires admin
            // setFormat() = format d'affichage FR dans la liste et le détail
            DateTimeField::new('publishedAt', 'Publié le')
                ->setFormat('dd/MM/Y HH:mm')
                ->hideOnForm(),
        ];
    }

    /**
     * configureFilters() — Filtres affichés dans la barre latérale de la liste
     *
     * Ces filtres sont disponibles UNIQUEMENT sur PAGE_INDEX (la liste).
     * Ils permettent de réduire les résultats sans toucher à la recherche globale.
     *
     * EntityFilter = filtre sur une relation (liste déroulante des valeurs)
     * DateTimeFilter = filtre sur une plage de dates (date début / date fin)
     * TextFilter = filtre textuel (contient / commence par / etc.)
     */
    public function configureFilters(
        \EasyCorp\Bundle\EasyAdminBundle\Config\Filters $filters
    ): \EasyCorp\Bundle\EasyAdminBundle\Config\Filters {
        return $filters
            // Filtre par catégorie — affiche un dropdown avec toutes les catégories
            ->add(EntityFilter::new('category', 'Catégorie'))
            // Filtre par auteur — affiche un dropdown avec tous les utilisateurs
            ->add(EntityFilter::new('author', 'Auteur'))
            // Filtre par plage de dates — champs "Du" et "Au"
            ->add(DateTimeFilter::new('publishedAt', 'Date de publication'))
            // Filtre textuel sur le titre — champ de saisie libre
            ->add(TextFilter::new('title', 'Titre'));
    }

    /**
     * configureActions() — Boutons d'action dans la liste et les formulaires
     *
     * ════════════════════════════════════════════════════════════════════
     * RÈGLE IMPORTANTE EasyAdmin 4.x :
     * ════════════════════════════════════════════════════════════════════
     *
     * EasyAdmin distingue deux cas d'usage pour les actions :
     *
     * 1. ->add()          → Pour ajouter une action qui N'EXISTE PAS encore sur la page.
     *                       Ex : DETAIL n'est pas sur PAGE_INDEX par défaut → on l'ajoute.
     *
     * 2. ->updateAction() → Pour modifier les options d'une action QUI EXISTE DÉJÀ.
     *                       Ex : SAVE_AND_ADD_ANOTHER existe déjà sur PAGE_NEW par défaut
     *                       → on ne peut PAS l'ajouter une 2e fois avec ->add(),
     *                       → on utilise ->updateAction() pour changer son libellé, icône, etc.
     *
     * Actions présentes PAR DÉFAUT selon la page :
     * - PAGE_INDEX  : edit, delete                        → DETAIL absent  → utiliser ->add()
     * - PAGE_DETAIL : edit, delete, index (retour liste)
     * - PAGE_NEW    : saveAndReturn, saveAndContinue, saveAndAddAnother
     * - PAGE_EDIT   : saveAndReturn, saveAndContinue
     *
     * Utiliser ->add() sur une action déjà présente lève une InvalidArgumentException.
     * ════════════════════════════════════════════════════════════════════
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions

            // ✅ ->add() est CORRECT ici :
            // Action::DETAIL n'existe pas par défaut sur PAGE_INDEX
            // → on l'ajoute pour avoir un bouton "Voir le détail" dans la liste
            ->add(Crud::PAGE_INDEX, Action::DETAIL)

            // ✅ ->updateAction() est CORRECT ici :
            // Action::SAVE_AND_ADD_ANOTHER existe DÉJÀ par défaut sur PAGE_NEW
            // → on NE PEUT PAS utiliser ->add() (lèverait une InvalidArgumentException)
            // → on utilise ->updateAction() pour personnaliser son libellé et son icône
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_ADD_ANOTHER,
                fn(Action $action) => $action
                    // Personnalise le libellé du bouton dans le formulaire de création
                    ->setLabel('Sauvegarder et ajouter un autre article')
                    // Ajoute une icône FontAwesome devant le libellé
                    ->setIcon('fa fa-plus')
            );
    }
}