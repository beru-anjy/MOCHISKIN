<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\ArticleRepository;
use App\Repository\NewsletterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentController extends AbstractController
{
    // On injecte NewsletterRepository pour l'Option B
    // (vérifier si l'email du commentateur est un abonné newsletter)
    public function __construct(
        private readonly NewsletterRepository $newsletterRepository,
    ) {
    }

    // ── ROUTE : Traitement du formulaire de commentaire ───────────────────────
    // methods: ['POST'] → uniquement les soumissions de formulaire
    // {slug} → slug de l'article concerné par le commentaire
    #[Route('/article/{slug}/comment', name: 'app_comment_add', methods: ['POST'])]
    public function add(
        string $slug,
        Request $request,
        ArticleRepository $articleRepository,
        EntityManagerInterface $em,
    ): Response {
        // ── ÉTAPE 1 : Trouver l'article par son slug ──────────────────────────
        $article = $articleRepository->findOneBy(['slug' => $slug]);

        if (!$article) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        // ── ÉTAPE 2 : Vérification du token CSRF ──────────────────────────────
        // Protège contre les attaques Cross-Site Request Forgery
        // Le token est généré dans le template Twig via csrf_token('comment')
        if (!$this->isCsrfTokenValid('comment', $request->request->get('_token'))) {
            $this->addFlash('error', 'Formulaire invalide. Réessayez.');

            return $this->redirectToRoute('app_article_show', ['slug' => $slug]);
        }

        // ── ÉTAPE 3 : Créer le commentaire ───────────────────────────────────
        // Le constructeur de Comment initialise automatiquement :
        //   - createdAt  = now()
        //   - isApproved = false → invisible jusqu'à validation admin dans EasyAdmin
        $comment = new Comment();
        $comment->setContent($request->request->get('content'));
        $comment->setArticle($article);

        // ── ÉTAPE 4 : LOGIQUE OPTION B ────────────────────────────────────────
        // 3 cas selon qui commente :

        $user = $this->getUser(); // null si visiteur non connecté

        if (null !== $user) {
            // ── CAS 1 : User connecté (auteur / admin) ────────────────────────
            // On associe directement son compte User au commentaire
            // authorName + authorEmail restent null (inutiles)
            // getDisplayName() retournera : user->firstName + user->lastName
            $comment->setAuthor($user);
        } else {
            // ── CAS 2 & 3 : Visiteur anonyme ─────────────────────────────────
            $emailSaisi = $request->request->get('authorEmail');
            $nomSaisi = $request->request->get('authorName');

            // ★ OPTION B : Vérifier si l'email est connu dans Newsletter ★
            // findOneBy cherche un abonné actif avec exactement cet email
            $abonne = $this->newsletterRepository->findOneBy([
                'email' => $emailSaisi,
                'isActive' => true, // Seulement les abonnés ayant confirmé leur email
            ]);

            if (null !== $abonne) {
                // ── CAS 2 : Email reconnu dans Newsletter ─────────────────────
                // On utilise automatiquement le prénom de l'abonné newsletter
                // L'utilisateur ne voit aucune différence dans le formulaire
                // Mais son vrai prénom apparaît sur le commentaire publié
                $comment->setAuthorName($abonne->getFirstName()); // ← prénom Newsletter
                $comment->setAuthorEmail($emailSaisi);
            } else {
                // ── CAS 3 : Email inconnu = vraiment anonyme ──────────────────
                // On utilise le nom et l'email saisis dans le formulaire tels quels
                $comment->setAuthorName($nomSaisi);
                $comment->setAuthorEmail($emailSaisi);
            }
        }

        // ── ÉTAPE 5 : Sauvegarder en base de données ─────────────────────────
        // isApproved reste false → le commentaire est invisible sur le blog
        // L'admin devra l'approuver dans EasyAdmin pour le rendre visible
        $em->persist($comment);
        $em->flush();

        // ── ÉTAPE 6 : Message de confirmation pour l'utilisateur ─────────────
        $this->addFlash(
            'success',
            '✅ Merci ! Votre commentaire est en attente de modération.'
        );

        // Rediriger vers l'article après soumission
        return $this->redirectToRoute('app_article_show', ['slug' => $slug]);
    }
}
