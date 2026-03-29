<?php

namespace App\Controller\Admin;

use App\Entity\SkinType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class SkinTypeCrudController extends AbstractCrudController
{
    /**
     * Indique à EasyAdmin quelle entité ce controller gère.
     * Obligatoire — doit retourner le FQCN de l'entité.
     */
    public static function getEntityFqcn(): string
    {
        return SkinType::class;
    }

    /**
     * configureCrud() — Paramètres globaux de ce CRUD
     * Titre des pages, champ de tri par défaut, recherche...
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Titre affiché sur la page liste
            ->setEntityLabelInSingular('Type de peau')
            ->setEntityLabelInPlural('Types de peau')
            // Recherche sur le nom du type de peau
            ->setSearchFields(['name'])
            // Tri par défaut : alphabétique
            ->setDefaultSort(['name' => 'ASC']);
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

            // Nom du type de peau (ex: Normale, Sèche, Grasse, Mixte, Sensible)
            TextField::new('name', 'Nom'),

            // Description détaillée — utile pour guider les utilisateurs
            TextareaField::new('description', 'Description')
                ->hideOnIndex()
                ->setNumOfRows(3),
        ];
    }
}