<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET'])]
    public function index(): Response
    {
        // Affiche simplement le formulaire de contact
        return $this->render('contact/index.html.twig');
    }

    #[Route('/contact', name: 'app_contact_send', methods: ['POST'])]
    public function send(Request $request, EntityManagerInterface $em): Response
    {
        // Vérification CSRF — protège contre les soumissions externes
        if (!$this->isCsrfTokenValid('contact', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide. Veuillez réessayer.');
            return $this->redirectToRoute('app_contact');
        }

        // Récupération et nettoyage des données du formulaire
        $fullName = trim($request->request->get('fullName', ''));
        $email    = trim($request->request->get('email', ''));
        $subject  = trim($request->request->get('subject', ''));
        $message  = trim($request->request->get('message', ''));

        // Validation basique côté serveur
        if (!$fullName || !$email || !$subject || !$message) {
            $this->addFlash('warning', 'Veuillez remplir tous les champs obligatoires.');
            return $this->redirectToRoute('app_contact');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('warning', 'Adresse e-mail invalide.');
            return $this->redirectToRoute('app_contact');
        }

        // Création et sauvegarde du message en base → visible dans EasyAdmin
        $contact = new Contact();
        $contact->setFullName($fullName)
                ->setEmail($email)
                ->setSubject($subject)
                ->setMessage($message);

        $em->persist($contact);
        $em->flush();

        // Flash message de confirmation affiché à l'utilisateur
        $this->addFlash('success',
            'Merci ' . explode(' ', $fullName)[0] . ' ! Votre message a bien été envoyé. Nous vous répondrons sous 24–48h.'
        );

        return $this->redirectToRoute('app_contact');
    }
}