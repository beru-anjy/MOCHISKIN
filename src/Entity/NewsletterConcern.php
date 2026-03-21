<?php

namespace App\Entity;

use App\Repository\NewsletterConcernRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterConcernRepository::class)]
class NewsletterConcern
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $concern = null;

    #[ORM\ManyToOne(inversedBy: 'newsletterConcerns')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Newsletter $newsletter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConcern(): ?string
    {
        return $this->concern;
    }

    public function setConcern(string $concern): static
    {
        $this->concern = $concern;

        return $this;
    }

    public function getNewsletter(): ?Newsletter
    {
        return $this->newsletter;
    }

    public function setNewsletter(?Newsletter $newsletter): static
    {
        $this->newsletter = $newsletter;

        return $this;
    }
}
