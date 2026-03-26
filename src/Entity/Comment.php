<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $isApproved = null;

    // Nom du visiteur anonyme
    // nullable: true car si User connecté, on utilise author->getFirstName()
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $authorName = null;

    // Email du visiteur anonyme
    // nullable: true pour la même raison que authorName
    // Utilisé par l'Option B pour vérifier si l'email est dans Newsletter
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $authorEmail = null;

    /**
     * Relation ManyToOne vers User
     * ✅ CORRIGÉ : nullable: true (pas nullable: false !)
     * Pourquoi ? Un visiteur anonyme n'a PAS de compte User.
     * null = commentaire anonyme | renseigné = User connecté.
     */
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: true)] // ← WAS false, MUST be true
    private ?User $author = null;

    /**
     * Relation ManyToOne vers Article
     * nullable: false → un commentaire DOIT être lié à un article.
     */
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    public function __construct()
    {
        // Date de soumission enregistrée automatiquement
        $this->createdAt = new \DateTimeImmutable();
        // false = commentaire EN ATTENTE de validation admin
        // Sans ça, les commentaires seraient visibles immédiatement
        $this->isApproved = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isApproved(): ?bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): static
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(?string $authorName): static
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail(?string $authorEmail): static
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * ✅ AJOUT : Méthode clé pour l'Option B
     * Retourne LE BON NOM à afficher dans Twig selon le cas :
     *
     * Cas 1 : User connecté      → prénom + nom du compte User
     * Cas 2 : Abonné newsletter  → prénom récupéré depuis Newsletter
     *                               (authorName renseigné par le contrôleur)
     * Cas 3 : Visiteur anonyme   → nom saisi dans le formulaire
     * Cas 4 : Aucun nom          → "Anonyme" par défaut
     */
    public function getDisplayName(): string
    {
        // Cas 1 : User connecté avec un vrai compte
        if (null !== $this->author) {
            return $this->author->getFirstName().' '.$this->author->getLastName();
        }

        // Cas 2 & 3 : authorName contient soit le prénom Newsletter,
        // soit le nom saisi manuellement dans le formulaire
        // ?? 'Anonyme' = valeur par défaut si authorName est null
        return $this->authorName ?? 'Anonyme';
    }
}
