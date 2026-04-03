<?php

namespace App\Controller;

use App\Entity\Newsletter;
use App\Entity\NewsletterConcern;
use App\Entity\NewsletterInterest;
use App\Repository\NewsletterRepository;
use App\Repository\SkinTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class NewsletterController extends AbstractController
{
    // Affiche le formulaire d'inscription
    #[Route('/newsletter', name: 'app_newsletter', methods: ['GET'])]
    public function index(SkinTypeRepository $skinTypeRepository): Response
    {
        return $this->render('newsletter/index.html.twig', [
            'skinTypes' => $skinTypeRepository->findAll(),
        ]);
    }

    // Traite la soumission : validation, création abonné, envoi email
    #[Route('/newsletter/subscribe', name: 'app_newsletter_subscribe', methods: ['POST'])]
    public function subscribe(
        Request $request,
        SkinTypeRepository $skinTypeRepository,
        NewsletterRepository $newsletterRepository,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator,
    ): Response {
        // Vérification CSRF
        if (!$this->isCsrfTokenValid('newsletter', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_newsletter');
        }

        $firstName  = trim($request->request->get('firstName', ''));
        $email      = trim($request->request->get('email', ''));
        $skinTypeId = $request->request->get('skinTypeId');
        $concerns   = $request->request->all('concerns');
        $interests  = $request->request->all('interests');

        // Validation des champs obligatoires
        if (!$firstName || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('warning', 'Veuillez remplir correctement les champs obligatoires.');
            return $this->redirectToRoute('app_newsletter');
        }

        // Vérification si l'email existe déjà en BDD
        $existing = $newsletterRepository->findOneBy(['email' => $email]);
        if ($existing !== null) {
            if ($existing->isActive()) {
                // Déjà confirmé → on informe sans bloquer
                $this->addFlash('info', '📧 Cet email est déjà inscrit à la newsletter.');
            } else {
                // Inscrit mais non confirmé → renvoi de l'email
                $this->sendConfirmationEmail($mailer, $urlGenerator, $existing);
                $this->addFlash('success', '📨 Email de confirmation renvoyé ! Vérifiez vos spams.');
            }
            return $this->redirectToRoute('app_newsletter');
        }

        // Création de l'abonné (isActive = false par défaut jusqu'à confirmation)
        $newsletter = new Newsletter();
        $newsletter->setFirstName($firstName);
        $newsletter->setEmail($email);

        // Lier le type de peau si sélectionné
        if ($skinTypeId) {
            $skinType = $skinTypeRepository->find($skinTypeId);
            if ($skinType) {
                $newsletter->setSkinType($skinType);
            }
        }

        // Génération du token unique (UUID) + expiration dans 24h
        $newsletter->setConfirmationToken(Uuid::v4()->toRfc4122());
        $newsletter->setTokenExpiresAt(new \DateTimeImmutable('+24 hours'));

        $em->persist($newsletter);

        // Préoccupations skincare cochées
        foreach ($concerns as $concern) {
            $nc = new NewsletterConcern();
            $nc->setConcern($concern);
            $nc->setNewsletter($newsletter);
            $em->persist($nc);
        }

        // Centres d'intérêt cochés
        foreach ($interests as $interest) {
            $ni = new NewsletterInterest();
            $ni->setInterest($interest);
            $ni->setNewsletter($newsletter);
            $em->persist($ni);
        }

        // Sauvegarde en BDD avant envoi (le token doit exister en BDD)
        $em->flush();

        // Envoi de l'email de confirmation
        $this->sendConfirmationEmail($mailer, $urlGenerator, $newsletter);

        $this->addFlash('success', '📨 Un email de confirmation a été envoyé à ' . $email . '. Vérifiez vos spams !');
        return $this->redirectToRoute('app_newsletter');
    }

    // Valide le token du lien cliqué dans l'email et active l'abonné
    #[Route('/newsletter/confirm/{token}', name: 'app_newsletter_confirm')]
    public function confirm(
        string $token,
        NewsletterRepository $newsletterRepository,
        EntityManagerInterface $em,
    ): Response {
        $newsletter = $newsletterRepository->findOneBy(['confirmationToken' => $token]);

        // Token introuvable en BDD
        if ($newsletter === null) {
            $this->addFlash('error', '❌ Lien de confirmation invalide.');
            return $this->redirectToRoute('app_newsletter');
        }

        // Token expiré (> 24h)
        if ($newsletter->isTokenExpired()) {
            $this->addFlash('warning', '⏱ Votre lien a expiré. Réinscrivez-vous pour recevoir un nouveau lien.');
            return $this->redirectToRoute('app_newsletter');
        }

        // Token valide → activation + suppression du token (sécurité : non réutilisable)
        $newsletter->setIsActive(true);
        $newsletter->setConfirmationToken(null);
        $newsletter->setTokenExpiresAt(null);
        $em->flush();

        return $this->render('newsletter/confirmed.html.twig', [
            'firstName' => $newsletter->getFirstName(),
        ]);
    }

    // Génère l'URL de confirmation et envoie l'email HTML via TemplatedEmail
    private function sendConfirmationEmail(
        MailerInterface $mailer,
        UrlGeneratorInterface $urlGenerator,
        Newsletter $newsletter,
    ): void {
        // ABSOLUTE_URL obligatoire : le lien doit fonctionner depuis un client email
        $confirmationUrl = $urlGenerator->generate(
            'app_newsletter_confirm',
            ['token' => $newsletter->getConfirmationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from($_ENV['MAILER_FROM'] ?? 'noreply@mochiskin.fr')
            ->to($newsletter->getEmail())
            ->subject('✅ Confirmez votre inscription — MOCHISKIN')
            ->htmlTemplate('emails/newsletter_confirmation.html.twig') // templates/emails/
            ->context([
                'firstName'       => $newsletter->getFirstName(),
                'confirmationUrl' => $confirmationUrl,
            ]);

        $mailer->send($email);
    }
}