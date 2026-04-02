<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;

class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setEntityLabelInSingular('Message Contact')
            ->setEntityLabelInPlural('Messages Contact')
            ->setSearchFields(['fullName', 'email', 'subject', 'message'])
            ->setDefaultSort(['sentAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('fullName', 'Nom complet'),
            EmailField::new('email', 'Email'),
            TextField::new('subject', 'Sujet'),
            TextareaField::new('message', 'Message')->hideOnIndex()->setNumOfRows(5),
            BooleanField::new('isRead', 'Lu')->renderAsSwitch(true),
            BooleanField::new('isReplied', 'Répondu')->renderAsSwitch(true),
            DateTimeField::new('sentAt', 'Envoyé le')->setFormat('dd/MM/Y HH:mm')->hideOnForm(),
        ];
    }

    public function configureFilters(\EasyCorp\Bundle\EasyAdminBundle\Config\Filters $filters): \EasyCorp\Bundle\EasyAdminBundle\Config\Filters
    {
        return $filters
            ->add(BooleanFilter::new('isRead', 'Lu'))
            ->add(BooleanFilter::new('isReplied', 'Répondu'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
