<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect(
            $adminUrlGenerator
                ->setDashboard(DashboardController::class)
                ->setController(ArticleCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('MochiSkin Admin')
            ->setLocales(['fr'])
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToUrl('Voir le site', 'fa fa-eye', '/')
            ->setLinkTarget('_blank');

        yield MenuItem::section('Contenu');
        // ✅ CORRIGÉ : on passe le CrudController (pas l'entité) + ->setAction('index')
        yield MenuItem::linkTo(ArticleCrudController::class, 'Articles', 'fa fa-newspaper')
            ->setAction('index');
        yield MenuItem::linkTo(CategoryCrudController::class, 'Categories', 'fa fa-folder')
            ->setAction('index');
        yield MenuItem::linkTo(TagCrudController::class, 'Tags', 'fa fa-tag')
            ->setAction('index');
        yield MenuItem::linkTo(CommentCrudController::class, 'Commentaires', 'fa fa-comment')
            ->setAction('index');

        yield MenuItem::section('Routines');
        yield MenuItem::linkTo(RoutineCrudController::class, 'Routines', 'fa fa-list')
            ->setAction('index');

        yield MenuItem::section('Utilisateurs & CRM');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-user')
            ->setAction('index');
        yield MenuItem::linkTo(NewsletterCrudController::class, 'Newsletter', 'fa fa-envelope')
            ->setAction('index');
        yield MenuItem::linkTo(ContactCrudController::class, 'Contacts', 'fa fa-phone')
            ->setAction('index');
        yield MenuItem::linkTo(SkinTypeCrudController::class, 'Types de peau', 'fa fa-spa')
            ->setAction('index');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->setName($user->getUserIdentifier())
            ->addMenuItems([
                MenuItem::linkToUrl('Mon profil', 'fa fa-user', '/admin/profile'),
                MenuItem::linkToUrl('Voir le blog', 'fa fa-eye', '/'),
            ]);
    }
}
