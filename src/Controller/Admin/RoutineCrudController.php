<?php

namespace App\Controller\Admin;

use App\Entity\Routine;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class RoutineCrudController extends AbstractCrudController
{
    /**
     * Indique à EasyAdmin quelle entité ce controller gère.
     * Obligatoire — doit retourner le FQCN de l'entité.
     */
    public static function getEntityFqcn(): string
    {
        return Routine::class;
    }

    /**
     * configureCrud() — Paramètres globaux de ce CRUD
     * Titre des pages, champ de tri par défaut, recherche...
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Titre affiché sur la page liste
            ->setEntityLabelInSingular('Routine')
            ->setEntityLabelInPlural('Routines')
            // Recherche sur le nom et la description
            ->setSearchFields(['name', 'description'])
            // Tri par défaut : par type puis par nom alphabétique
            ->setDefaultSort(['type' => 'ASC', 'name' => 'ASC']);
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

            // Nom de la routine (ex: Routine Matin Peau Grasse)
            TextField::new('name', 'Nom'),

            // Type : morning (matin) ou evening (soir)
            // ChoiceField avec setChoices() permet une liste déroulante propre
            ChoiceField::new('type', 'Type')
                ->setChoices([
                    'Matin' => 'morning',
                    'Soir'  => 'evening',
                ]),

            // Description générale de la routine
            TextareaField::new('description', 'Description')
                ->hideOnIndex()
                ->setNumOfRows(3),

            // Durée totale en minutes
            IntegerField::new('durationMinutes', 'Durée (min)'),

            // Nombre d'étapes dans la routine
            IntegerField::new('stepCount', 'Nb étapes'),
        ];
    }

    /**
     * configureFilters() — Filtres dans la barre latérale de la liste
     *
     * Permettent de filtrer les routines par type (matin/soir).
     * Disponibles uniquement sur la page liste (PAGE_INDEX).
     */
    public function configureFilters(
        \EasyCorp\Bundle\EasyAdminBundle\Config\Filters $filters
    ): \EasyCorp\Bundle\EasyAdminBundle\Config\Filters {
        return $filters
            // Filtre Matin / Soir — permet d'afficher uniquement un type
            ->add(
                ChoiceFilter::new('type', 'Type')
                    ->setChoices([
                        'Matin' => 'morning',
                        'Soir'  => 'evening',
                    ])
            );
    }
}