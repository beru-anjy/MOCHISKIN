<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Routine;
use App\Entity\Comment;
use App\Entity\Newsletter;
use App\Entity\Contact;
use App\Entity\SkinType;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return parent::index();
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
        yield MenuItem::linkToCrud('Articles', 'fa fa-newspaper', Article::class);
        yield MenuItem::linkToCrud('Catégories', 'fa fa-folder', Category::class);
        yield MenuItem::linkToCrud('Tags', 'fa fa-tag', Tag::class);
        yield MenuItem::linkToCrud('Commentaires', 'fa fa-comment', Comment::class);

        yield MenuItem::section('Routines');
        yield MenuItem::linkToCrud('Routines', 'fa fa-list', Routine::class);

        yield MenuItem::section('Utilisateurs & CRM');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', User::class);
        yield MenuItem::linkToCrud('Newsletter', 'fa fa-envelope', Newsletter::class);
        yield MenuItem::linkToCrud('Contacts', 'fa fa-phone', Contact::class);
        yield MenuItem::linkToCrud('Types de peau', 'fa fa-spa', SkinType::class);
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