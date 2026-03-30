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
    public static function getEntityFqcn(): string { return Routine::class; }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityLabelInSingular('Routine')
            ->setEntityLabelInPlural('Routines')
            ->setSearchFields(['name', 'description'])
            ->setDefaultSort(['type' => 'ASC', 'name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            ChoiceField::new('type', 'Type')->setChoices(['Matin' => 'morning', 'Soir' => 'evening']),
            TextareaField::new('description', 'Description')->hideOnIndex()->setNumOfRows(3),
            IntegerField::new('durationMinutes', 'Durée (min)'),
            IntegerField::new('stepCount', 'Nb étapes'),
        ];
    }

    public function configureFilters(\EasyCorp\Bundle\EasyAdminBundle\Config\Filters $filters): \EasyCorp\Bundle\EasyAdminBundle\Config\Filters
    {
        return $filters->add(ChoiceFilter::new('type', 'Type')->setChoices(['Matin' => 'morning', 'Soir' => 'evening']));
    }
}