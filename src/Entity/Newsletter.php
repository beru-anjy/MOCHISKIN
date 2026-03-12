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

    #[ORM\Column]
    private ?\DateTimeImmutable $subscribetAt = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\ManyToOne(inversedBy: 'newsletters')]
    private ?SkinType $skinType = null;

    /**
     * @var Collection<int, NewsletterConcern>
     */
    #[ORM\OneToMany(targetEntity: NewsletterConcern::class, mappedBy: 'newsletter')]
    private Collection $newsletterConcerns;

    /**
     * @var Collection<int, NewsletterInterest>
     */
    #[ORM\OneToMany(targetEntity: NewsletterInterest::class, mappedBy: 'newsletter')]
    private Collection $newsletterInterests;

    public function __construct()
    {
        $this->subscribetAt= new \DateTimeImmutable();
        $this->isActive=true;
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

    public function getSubscribetAt(): ?\DateTimeImmutable
    {
        return $this->subscribetAt;
    }

    public function setSubscribetAt(\DateTimeImmutable $subscribetAt): static
    {
        $this->subscribetAt = $subscribetAt;

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
            // set the owning side to null (unless already changed)
            if ($newsletterConcern->getNewsletter() === $this) {
                $newsletterConcern->setNewsletter(null);
            }
        }

        return $this;
    }

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
            // set the owning side to null (unless already changed)
            if ($newsletterInterest->getNewsletter() === $this) {
                $newsletterInterest->setNewsletter(null);
            }
        }

        return $this;
    }
}
