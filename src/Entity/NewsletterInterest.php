<?php

namespace App\Entity;

use App\Repository\NewsletterInterestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterInterestRepository::class)]
class NewsletterInterest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $interest = null;

    #[ORM\ManyToOne(inversedBy: 'newsletterInterests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Newsletter $newsletter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInterest(): ?string
    {
        return $this->interest;
    }

    public function setInterest(string $interest): static
    {
        $this->interest = $interest;

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
