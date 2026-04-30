<?php

namespace App\Controller\Admin;

use App\Entity\SkinType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SkinTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SkinType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Type de peau')
            ->setEntityLabelInPlural('Types de peau')
            ->setSearchFields(['name'])
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnIndex()->hideOnForm(),
            TextField::new('name', 'Nom'),
            TextareaField::new('description', 'Description')
                ->hideOnIndex()
                ->setNumOfRows(3),
        ];
    }
}
