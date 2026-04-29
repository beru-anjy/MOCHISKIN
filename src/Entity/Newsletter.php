<?php

namespace App\Entity;

use App\Repository\NewsletterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterRepository::class)]
class Newsletter
{
    // Identifiant unique auto-généré
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Prénom de l'abonné
    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    // Email unique de l'abonné
    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    // Date d'inscription
    #[ORM\Column]
    private ?\DateTimeImmutable $subscribedAt = null;

    // true = abonné confirmé / false = en attente de confirmation
    #[ORM\Column]
    private ?bool $isActive = null;

    // Token UUID envoyé par email pour confirmer l'inscription (null après confirmation)
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $confirmationToken = null;

    // Date limite de validité du token (24h après inscription)
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $tokenExpiresAt = null;

    // Type de peau de l'abonné (optionnel)
    #[ORM\ManyToOne(inversedBy: 'newsletters')]
    private ?SkinType $skinType = null;

    // quand l'abonné est supprimé → évite l'erreur ForeignKeyConstraintViolation
    #[ORM\OneToMany(targetEntity: NewsletterConcern::class, mappedBy: 'newsletter', cascade: ['remove'])]
    private Collection $newsletterConcerns;

    // quand l'abonné est supprimé → évite l'erreur ForeignKeyConstraintViolation
    #[ORM\OneToMany(targetEntity: NewsletterInterest::class, mappedBy: 'newsletter', cascade: ['remove'])]
    private Collection $newsletterInterests;

    // isActive = false par défaut → passe à true uniquement après clic sur le lien de confirmation
    public function __construct()
    {
        $this->subscribedAt = new \DateTimeImmutable();
        $this->isActive = false;
        $this->newsletterConcerns = new ArrayCollection();
        $this->newsletterInterests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getSubscribedAt(): ?\DateTimeImmutable
    {
        return $this->subscribedAt;
    }

    public function setSubscribedAt(\DateTimeImmutable $subscribedAt): static
    {
        $this->subscribedAt = $subscribedAt;
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

    // ── Token de confirmation ─────────────────────────────────

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    // null = token effacé après confirmation (sécurité : non réutilisable)
    public function setConfirmationToken(?string $token): static
    {
        $this->confirmationToken = $token;
        return $this;
    }

    // ── Date d'expiration du token ────────────────────────────

    public function getTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->tokenExpiresAt;
    }

    // null = effacée après confirmation
    public function setTokenExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        $this->tokenExpiresAt = $expiresAt;
        return $this;
    }

    // Retourne true si le lien de confirmation a dépassé 24h
    public function isTokenExpired(): bool
    {
        if ($this->tokenExpiresAt === null) {
            return true;
        }
        return new \DateTimeImmutable() > $this->tokenExpiresAt;
    }

    // ── Type de peau ──────────────────────────────────────────

    public function getSkinType(): ?SkinType
    {
        return $this->skinType;
    }

    public function setSkinType(?SkinType $skinType): static
    {
        $this->skinType = $skinType;
        return $this;
    }

    // ── Préoccupations skincare ───────────────────────────────
    // Gestion de la collection NewsletterConcern (ex: acné, rides, taches...)
    // cascade: remove configuré sur la relation → pas besoin de supprimer manuellement

    /** @return Collection<int, NewsletterConcern> */
    public function getNewsletterConcerns(): Collection
    {
        return $this->newsletterConcerns;
    }

    public function addNewsletterConcern(NewsletterConcern $newsletterConcern): static
    {
        if (!$this->newsletterConcerns->contains($newsletterConcern)) {
            $this->newsletterConcerns->add($newsletterConcern);
            // Synchronisation du côté propriétaire de la relation
            $newsletterConcern->setNewsletter($this);
        }
        return $this;
    }

    public function removeNewsletterConcern(NewsletterConcern $newsletterConcern): static
    {
        if ($this->newsletterConcerns->removeElement($newsletterConcern)) {
            if ($newsletterConcern->getNewsletter() === $this) {
                $newsletterConcern->setNewsletter(null);
            }
        }
        return $this;
    }

    // ── Centres d'intérêt ─────────────────────────────────────
    // Gestion de la collection NewsletterInterest (ex: DIY, K-beauty, naturel...)
    // cascade: remove configuré sur la relation → pas besoin de supprimer manuellement

    /** @return Collection<int, NewsletterInterest> */
    public function getNewsletterInterests(): Collection
    {
        return $this->newsletterInterests;
    }

    public function addNewsletterInterest(NewsletterInterest $newsletterInterest): static
    {
        if (!$this->newsletterInterests->contains($newsletterInterest)) {
            $this->newsletterInterests->add($newsletterInterest);
            // Synchronisation du côté propriétaire de la relation
            $newsletterInterest->setNewsletter($this);
        }
        return $this;
    }

    public function removeNewsletterInterest(NewsletterInterest $newsletterInterest): static
    {
        if ($this->newsletterInterests->removeElement($newsletterInterest)) {
            if ($newsletterInterest->getNewsletter() === $this) {
                $newsletterInterest->setNewsletter(null);
            }
        }
        return $this;
    }
}