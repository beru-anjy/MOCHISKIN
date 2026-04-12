<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserChecker — Vérifie l'état du compte avant et après authentification.
 * Branché sur le firewall via user_checker dans security.yaml.
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * Exécuté AVANT la vérification du mot de passe.
     * Bloque immédiatement si le compte est désactivé par un admin.
     */
    public function checkPreAuth(UserInterface $user): void
    {
        // Sécurité défensive : on ne traite que les objets User de l'application
        if (!$user instanceof User) {
            return;
        }

        // isActive = false → compte banni/désactivé par l'admin
        // On bloque avant même de vérifier le mot de passe
        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte a été désactivé. Contactez l\'administrateur.'
            );
        }
    }

    /**
     * Exécuté APRÈS la vérification du mot de passe (mdp correct).
     * Bloque si l'email n'a pas encore été confirmé.
     */
    public function checkPostAuth(UserInterface $user): void
    {
        // Sécurité défensive : on ne traite que les objets User de l'application
        if (!$user instanceof User) {
            return;
        }

        // isVerified = false → l'utilisateur n'a pas cliqué sur le lien de confirmation
        // Le mdp est bon mais on refuse quand même la connexion jusqu'à vérification
        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre adresse e-mail n\'est pas encore vérifiée. '
                .'Vérifiez votre boîte mail et cliquez sur le lien de confirmation.'
            );
        }
    }
}