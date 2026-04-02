<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Entité User — Utilisateur du site MochiSkin.
 * Implémente UserInterface (Security) et PasswordAuthenticatedUserInterface (hashage mdp).
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Clé primaire auto-incrémentée par la BDD — jamais saisie manuellement
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Email unique — sert aussi d'identifiant de connexion (voir getUserIdentifier())
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    // Tableau de rôles ex: ['ROLE_ADMIN'] — ROLE_USER est toujours ajouté automatiquement
    /** @var list<string> The user roles */
    #[ORM\Column]
    private array $roles = [];

    // Mot de passe hashé — jamais stocké en clair
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 120)]
    private ?string $firstName = null;

    #[ORM\Column(length: 150)]
    private ?string $lastName = null;

    // Date d'inscription — initialisée automatiquement dans le constructeur
    #[ORM\Column]
    private ?\DateTimeImmutable $registrationDate = null;

    // Permet de désactiver un compte sans le supprimer
    #[ORM\Column]
    private ?bool $isActive = null;

    // Relation ManyToOne → un user a un type de peau, un type de peau a plusieurs users
    // ⚠️ SkinType doit avoir __toString() pour s'afficher dans les dropdowns EasyAdmin
    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?SkinType $skinType = null;

    // Un utilisateur peut rédiger plusieurs articles
    /** @var Collection<int, Article> */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'author')]
    private Collection $articles;

    // Un utilisateur peut écrire plusieurs commentaires
    /** @var Collection<int, Comment> */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'author')]
    private Collection $comments;

    // Compte vérifié par email (true après confirmation du lien reçu par mail)
    #[ORM\Column]
    private bool $isVerified = false;

    // Initialisation automatique à la création de l'objet
    public function __construct()
    {
        $this->registrationDate = new \DateTimeImmutable();
        $this->isActive = true;
        $this->roles = [];
        $this->articles = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ✅ CORRECTION AJOUTÉE — méthode __toString()
    //
    // Sans cette méthode, EasyAdmin ne sait pas comment afficher un objet User
    // dans les colonnes de relation (ex: colonne "Auteur" dans la liste des articles)
    // et lève une erreur : "Object could not be converted to string"
    //
    // Affiche "Prénom Nom", avec fallback sur l'email si l'un des deux est absent
    // ══════════════════════════════════════════════════════════════════════════
    public function __toString(): string
    {
        $fullName = trim(($this->firstName ?? '').' '.($this->lastName ?? ''));

        // Si prénom et nom sont renseignés → "Marie Dupont", sinon → email
        return $fullName ?: ($this->email ?? '');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Getters & Setters
    // Convention Symfony : getXxx() pour lire, setXxx() pour écrire.
    // Les setters retournent "static" pour permettre le chaînage fluent :
    //   $user->setFirstName('Marie')->setLastName('Dupont');
    // ══════════════════════════════════════════════════════════════════════════

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    // Utilisé par Symfony Security pour identifier l'utilisateur connecté
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // ROLE_USER toujours ajouté automatiquement pour garantir un rôle minimum
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /** @param array<string> $roles */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    // Sérialisation personnalisée — on stocke un hash du mdp pour éviter toute fuite en session
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    // Deprecated depuis Symfony 7 — conservé pour compatibilité, sera retiré en Symfony 8
    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeImmutable
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeImmutable $registrationDate): static
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSkinType(): ?SkinType
    {
        return $this->skinType;
    }

    public function setSkinType(?SkinType $skinType): static
    {
        $this->skinType = $skinType;

        return $this;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Gestion de la relation OneToMany → Article
    //
    // Pattern standard Doctrine pour les collections :
    // - getXxx()    → retourne la collection complète
    // - addXxx()    → ajoute un élément si absent (évite les doublons avec contains())
    // - removeXxx() → supprime et remet null côté propriétaire si nécessaire
    // ══════════════════════════════════════════════════════════════════════════

    /** @return Collection<int, Article> */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            // Synchronisation du côté propriétaire de la relation (Article.author)
            $article->setAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            // Remet null uniquement si la relation pointe encore sur ce user
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Gestion de la relation OneToMany → Comment
    // Même pattern que pour Article ci-dessus.
    // ══════════════════════════════════════════════════════════════════════════

    /** @return Collection<int, Comment> */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            // Synchronisation du côté propriétaire de la relation (Comment.author)
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // Remet null uniquement si la relation pointe encore sur ce user
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
