<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    // EmailVerifier : service qui génère et envoie le lien de confirmation email
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    // Route : /register — affiche et traite le formulaire d'inscription
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher, // hache le mot de passe avant stockage
        EntityManagerInterface $entityManager            // sauvegarde l'utilisateur en base
        // Security supprimé — plus de connexion automatique après inscription
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Récupère le mot de passe en clair puis le hache (bcrypt/argon2)
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Compte NON vérifié par défaut — bloqué jusqu'à confirmation par email
            // setIsVerified(true) supprimé volontairement
            $user->setIsVerified(false);

            // Sauvegarde l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Envoie l'email de confirmation via Mailtrap (dev) ou SMTP réel (prod)
            // Template : templates/registration/confirmation_email.html.twig
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('contact@mochiskin.fr', 'MOCHISKIN'))
                    ->to((string) $user->getEmail())
                    ->subject('Bienvenue sur MOCHISKIN — Confirmez votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // Redirige vers login avec message — l'utilisateur doit confirmer son email avant de se connecter
            $this->addFlash('success', 'Compte créé ! Vérifiez votre boîte mail pour activer votre compte.');
            return $this->redirectToRoute('app_login');
        }

        // Affiche le formulaire d'inscription (GET ou formulaire invalide)
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    // Route : /verify/email — traite le lien de confirmation reçu par email
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        // Bloque l'accès si l'utilisateur n'est pas connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            // Vérifie la signature du lien et passe is_verified à true en base
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            // Lien expiré ou invalide — redirige vers l'inscription avec message d'erreur
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        // Confirmation réussie — redirige vers login, le compte est maintenant actif
        $this->addFlash('success', 'Votre adresse email a bien été confirmée. Vous pouvez vous connecter.');
        return $this->redirectToRoute('app_login');
    }
}