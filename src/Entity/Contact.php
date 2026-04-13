<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Contact — Entité représentant un message reçu via le formulaire de contact.
 * Visible et gérable depuis EasyAdmin > Messages Contact.
 * Les messages sont créés par ContactController lors de la soumission du formulaire.
 */
#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    // Identifiant unique auto-généré
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Nom complet de l'expéditeur
    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    // Email de l'expéditeur — utilisé pour répondre au message
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    // Sujet du message (ex: collaboration, question, partenariat...)
    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    // Contenu complet du message
    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    // Date et heure d'envoi — initialisée automatiquement dans le constructeur
    #[ORM\Column]
    private ?\DateTimeImmutable $sentAt = null;

    // Indique si le message a été lu dans EasyAdmin (false par défaut)
    #[ORM\Column]
    private ?bool $isRead = null;

    // Indique si une réponse a été envoyée (false par défaut)
    #[ORM\Column]
    private ?bool $isReplied = null;

    // ✅ AJOUT — constructeur pour initialiser les valeurs par défaut
    // Evite l'erreur "sent_at ne peut pas être vide" lors de la sauvegarde
    public function __construct()
    {
        // Date d'envoi automatique — correspond à l'instant de soumission du formulaire
        $this->sentAt    = new \DateTimeImmutable();
        // Non lu par défaut — à marquer manuellement dans EasyAdmin
        $this->isRead    = false;
        // Non répondu par défaut — à marquer manuellement dans EasyAdmin
        $this->isReplied = false;
    }

    // ✅ AJOUT — permet à EasyAdmin d'afficher le nom de l'expéditeur
    // au lieu de "Contact #1" dans les listes et relations
    public function __toString(): string
    {
        return $this->fullName ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;
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

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    // ── Dates et statuts ──────────────────────────────────────────────────────

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;
        return $this;
    }

    // true = message lu dans EasyAdmin
    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
        return $this;
    }

    // true = réponse envoyée à l'expéditeur
    public function isReplied(): ?bool
    {
        return $this->isReplied;
    }

    public function setIsReplied(bool $isReplied): static
    {
        $this->isReplied = $isReplied;
        return $this;
    }
}