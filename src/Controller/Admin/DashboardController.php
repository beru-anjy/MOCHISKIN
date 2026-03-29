<?php

namespace App\Controller\Admin;
// ── Imports EasyAdmin 4.x ─────────────────────────────────────────────
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
// ── Imports Symfony ───────────────────────────────────────────────────
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
// ── Imports des entités à gérer dans l'admin ──────────────────────────
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
/**
* Route principale de l'admin → http://localhost:8000/admin
* Affiche le template personnalisé admin/dashboard.html.twig
*
* En EasyAdmin 4.x on utilise l'attribut PHP #[Route]
* Note : #[AdminDashboard] est disponible en EA 4.9+
*/
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
public function index(): Response
{
// Rend notre template de dashboard personnalisé
// Ce template étend @EasyAdmin/page/content.html.twig
return $this->render('admin/dashboard.html.twig');
}
/**
* configureDashboard() — Paramètres globaux de l'interface admin
* Titre, favicon, langue, couleurs...
*/
public function configureDashboard(): Dashboard
{
return Dashboard::new()
// Titre affiché dans la barre latérale
->setTitle('MochiSkin Admin')
// Locale pour les dates et formats (fr = français)
->setLocales(['fr'])
// Nombre d'éléments par page dans les listes
->renderContentMaximized();
}
/**
* configureMenuItems() — Menu latéral de l'admin
*
* Utilise yield pour retourner chaque élément de menu.
* Les sections (MenuItem::section) créent des séparateurs visuels.
* linkToCrud() lie directement au CrudController de l'entité.
*/
public function configureMenuItems(): iterable
{
// ── Accueil du dashboard ───────────────────────────────────────
yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
// ── Lien vers le site public (s'ouvre dans un nouvel onglet) ──
yield MenuItem::linkToUrl('Voir le site', 'fa fa-eye', '/')
->setLinkTarget('_blank');
// ── Section Contenu ────────────────────────────────────────────
// Regroupe tout ce qui concerne le contenu éditorial du blog
yield MenuItem::section('Contenu');
yield MenuItem::linkToCrud('Articles', 'fa fa-newspaper', Article::class);
yield MenuItem::linkToCrud('Catégories', 'fa fa-folder', Category::class);
yield MenuItem::linkToCrud('Tags', 'fa fa-tag', Tag::class);
yield MenuItem::linkToCrud('Commentaires', 'fa fa-comment', Comment::class);
// ── Section Routines ───────────────────────────────────────────
yield MenuItem::section('Routines');
yield MenuItem::linkToCrud('Routines', 'fa fa-list', Routine::class);
// ── Section Utilisateurs & CRM ────────────────────────────────
yield MenuItem::section('Utilisateurs & CRM');
yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', User::class);
yield MenuItem::linkToCrud('Newsletter', 'fa fa-envelope', Newsletter::class);
yield MenuItem::linkToCrud('Contacts', 'fa fa-phone', Contact::class);
yield MenuItem::linkToCrud('Types de peau','fa fa-spa', SkinType::class);
}


public function configureUserMenu(UserInterface $user): UserMenu
{
return parent::configureUserMenu($user)
// Affiche le nom de l'utilisateur connecté
->setName($user->getUserIdentifier())
// Lien vers le profil (à créer si nécessaire)
->addMenuItems([
MenuItem::linkToUrl('Mon profil', 'fa fa-user', '/admin/profile'),
MenuItem::linkToUrl('Voir le blog', 'fa fa-eye', '/'),
]);
}
}