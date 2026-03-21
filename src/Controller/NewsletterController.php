<?php

namespace App\Controller;

use App\Entity\Newsletter;
use App\Entity\NewsletterConcern;
use App\Entity\NewsletterInterest;
use App\Repository\SkinTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NewsletterController extends AbstractController
{
    #[Route('/newsletter', name: 'app_newsletter', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        SkinTypeRepository $skinTypeRepository,
        EntityManagerInterface $em,
    ): Response {
        // ── Récupérer les types de peau pour le <select> ──
        $skinTypes = $skinTypeRepository->findAll();

        // ── Traitement du formulaire POST ──
        if ($request->isMethod('POST')) {
            // Vérification CSRF
            if (!$this->isCsrfTokenValid('newsletter', $request->request->get('_token'))) {
                $this->addFlash('danger', 'Token de sécurité invalide. Veuillez réessayer.');

                return $this->redirectToRoute('app_newsletter');
            }

            $firstName = trim($request->request->get('firstName', ''));
            $email = trim($request->request->get('email', ''));
            $skinTypeId = $request->request->get('skinTypeId');
            $concerns = $request->request->all('concerns');   // tableau []
            $interests = $request->request->all('interests');  // tableau []

            // Validation basique
            if (!$firstName || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('warning', 'Veuillez remplir correctement les champs obligatoires.');

                return $this->redirectToRoute('app_newsletter');
            }

            // Vérifier si email déjà inscrit
            $existing = $em->getRepository(Newsletter::class)->findOneBy(['email' => $email]);
            if ($existing) {
                $this->addFlash('warning', 'Cet email est déjà inscrit à la newsletter.');

                return $this->redirectToRoute('app_newsletter');
            }

            // Créer l'abonnement
            $newsletter = new Newsletter();
            $newsletter->setFirstName($firstName);
            $newsletter->setEmail($email);
            $newsletter->setSubscribedAt(new \DateTimeImmutable());
            $newsletter->setIsActive(true);

            // Lier le type de peau si sélectionné
            if ($skinTypeId) {
                $skinType = $skinTypeRepository->find($skinTypeId);
                if ($skinType) {
                    $newsletter->setSkinType($skinType);
                }
            }

            $em->persist($newsletter);

            // Enregistrer les préoccupations
            foreach ($concerns as $concern) {
                $nc = new NewsletterConcern();
                $nc->setConcern($concern);
                $nc->setNewsletter($newsletter);
                $em->persist($nc);
            }

            // Enregistrer les centres d'intérêt
            foreach ($interests as $interest) {
                $ni = new NewsletterInterest();
                $ni->setInterest($interest);
                $ni->setNewsletter($newsletter);
                $em->persist($ni);
            }

            $em->flush();

            $this->addFlash('success', 'Merci '.$firstName.' ! Votre inscription est confirmée. Premier email dimanche à 9h 🌸');

            return $this->redirectToRoute('app_newsletter');
        }

        return $this->render('newsletter/index.html.twig', [
            'skinTypes' => $skinTypes,
        ]);
    }
}
