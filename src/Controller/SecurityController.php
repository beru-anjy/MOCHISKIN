<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // AJOUT — si l'utilisateur est déjà connecté, on le redirige directement
        // Evite d'afficher le formulaire de login à quelqu'un déjà authentifié
        if ($this->getUser()) {
            // Admin → tableau de bord EasyAdmin
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin');
            }
            // Utilisateur simple → page d'accueil
            return $this->redirectToRoute('app_home');
        }

        // Récupère l'erreur de connexion s'il y en a une (mauvais mdp, compte non vérifié...)
        $error = $authenticationUtils->getLastAuthenticationError();

        // Pré-remplit le champ email avec la dernière valeur saisie
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony intercepte cette route automatiquement via le firewall
        // Ce code ne s'exécute jamais — c'est voulu
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}