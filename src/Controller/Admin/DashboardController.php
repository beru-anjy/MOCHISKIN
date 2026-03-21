<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Routine;
use App\Entity\Comment;
use App\Entity\Newsletter;
use App\Entity\Contact;
use App\Entity\SkinType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('MochiSkin Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
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
}