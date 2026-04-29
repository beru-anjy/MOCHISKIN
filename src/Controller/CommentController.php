<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentController extends AbstractController
{
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
        // ── SÉCURITÉ : réservé aux utilisateurs connectés ────────────────────
        // Bloque toute tentative de POST sans être connecté
        // (ex : appel direct à la route via un outil externe)
        if (!$this->getUser()) {
            $this->addFlash('error', 'Vous devez être connecté(e) pour laisser un commentaire.');

            return $this->redirectToRoute('app_login');
        }

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

        // ── ÉTAPE 4 : Associer l'utilisateur connecté ────────────────────────
        // Grâce à la vérification en haut, $this->getUser() est forcément non null ici
        // authorName + authorEmail restent null (champs dépréciés, non utilisés)
        // getDisplayName() retournera : user->firstName + user->lastName
        $comment->setAuthor($this->getUser());

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