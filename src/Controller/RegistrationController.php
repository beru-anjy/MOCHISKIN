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
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setIsVerified(false);

            $entityManager->persist($user);
            $entityManager->flush();

            // ── Envoie l'email de confirmation ────────────────────────────────
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('contact@mochiskin.fr', 'MOCHISKIN'))
                    ->to((string) $user->getEmail())
                    ->subject('Bienvenue sur MOCHISKIN — Confirmez votre email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // On redirige vers la page de login avec un message flash d'info
            // L'utilisateur doit d'abord confirmer son email avant de se connecter
            $this->addFlash('info', '📧 Un email de confirmation vous a été envoyé. Veuillez cliquer sur le lien pour activer votre compte.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    // ── Route : /verify/email — traite le lien de confirmation reçu par email ──
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, EntityManagerInterface $em): Response
    {

        // Maintenant : on récupère l'utilisateur via le paramètre 'id' dans l'URL
        $id = $request->query->get('id');

        if (!$id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            // Vérifie la signature du lien et passe isVerified à true
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        // ── Flash "compte confirmé" → affiché sur la page login sous forme de pop-up
        $this->addFlash('account_verified', $user->getFirstName());

        // Redirige vers la page de connexion SANS connecter l'utilisateur
        return $this->redirectToRoute('app_login');
    }
}