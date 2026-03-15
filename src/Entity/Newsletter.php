<?php

namespace App\Entity;

use App\Entity\NewsletterConcern;
use App\Entity\SkinType;
use App\Repository\NewsletterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterRepository::class)]
class Newsletter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    // 🐛 BUGFIX : 'subscribetAt' → 'subscribedAt' (faute de frappe corrigée)
    #[ORM\Column]
    private ?\DateTimeImmutable $subscribedAt = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    /**
     * Relation ManyToOne vers SkinType :
     * Plusieurs abonnés peuvent avoir le même type de peau.
     * nullable → un abonné peut ne pas avoir renseigné son type de peau.
     */
    #[ORM\ManyToOne(inversedBy: 'newsletters')]
    private ?SkinType $skinType = null;

    /**
     * Relation OneToMany vers NewsletterConcern :
     * Un abonné peut avoir plusieurs préoccupations.
     * mappedBy: 'newsletter' → NewsletterConcern possède la clé étrangère $newsletter.
     *
     * @var Collection<int, NewsletterConcern>
     */
    #[ORM\OneToMany(targetEntity: NewsletterConcern::class, mappedBy: 'newsletter')]
    private Collection $newsletterConcerns;

    /**
     * Relation OneToMany vers NewsletterInterest :
     * Un abonné peut avoir plusieurs centres d'intérêt.
     * mappedBy: 'newsletter' → NewsletterInterest possède la clé étrangère $newsletter.
     *
     * @var Collection<int, NewsletterInterest>
     */
    #[ORM\OneToMany(targetEntity: NewsletterInterest::class, mappedBy: 'newsletter')]
    private Collection $newsletterInterests;

    public function __construct()
    {
        $this->subscribedAt        = new \DateTimeImmutable(); // 🐛 BUGFIX : subscribetAt → subscribedAt
        $this->isActive            = true;
        $this->newsletterConcerns  = new ArrayCollection();
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

    // 🐛 BUGFIX : getSubscribetAt → getSubscribedAt
    public function getSubscribedAt(): ?\DateTimeImmutable
    {
        return $this->subscribedAt;
    }

    // 🐛 BUGFIX : setSubscribetAt → setSubscribedAt
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

    public function getSkinType(): ?SkinType
    {
        return $this->skinType;
    }

    public function setSkinType(?SkinType $skinType): static
    {
        $this->skinType = $skinType;
        return $this;
    }

    // ── NewsletterConcerns ────────────────────────────────────

    /**
     * @return Collection<int, NewsletterConcern>
     */
    public function getNewsletterConcerns(): Collection
    {
        return $this->newsletterConcerns;
    }

    public function addNewsletterConcern(NewsletterConcern $newsletterConcern): static
    {
        if (!$this->newsletterConcerns->contains($newsletterConcern)) {
            $this->newsletterConcerns->add($newsletterConcern);
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

    // ── NewsletterInterests ───────────────────────────────────

    /**
     * @return Collection<int, NewsletterInterest>
     */
    public function getNewsletterInterests(): Collection
    {
        return $this->newsletterInterests;
    }

    public function addNewsletterInterest(NewsletterInterest $newsletterInterest): static
    {
        if (!$this->newsletterInterests->contains($newsletterInterest)) {
            $this->newsletterInterests->add($newsletterInterest);
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