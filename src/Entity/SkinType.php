<?php

namespace App\Entity;

use App\Repository\SkinTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité SkinType — Type de peau (ex: "Peau grasse", "Peau sèche", "Peau mixte"...).
 *
 * Cette entité est utilisée :
 * - En relation avec les utilisateurs (User) → chaque utilisateur peut avoir un type de peau
 * - En relation avec les newsletters (Newsletter) → ciblage par type de peau
 * - Dans les dropdowns et AssociationField d'EasyAdmin
 *
 * ⚠️ IMPORTANT : toute entité utilisée dans un champ de relation Symfony/EasyAdmin
 * (AssociationField, EntityType, etc.) DOIT implémenter __toString(),
 * sinon PHP lève une erreur : "Object could not be converted to string"
 * car Symfony ne sait pas comment afficher l'objet dans un <select> ou une liste.
 */
#[ORM\Entity(repositoryClass: SkinTypeRepository::class)]
class SkinType
{
    /**
     * Identifiant unique auto-incrémenté en base de données.
     * Géré automatiquement par Doctrine — ne jamais setter manuellement.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom du type de peau (ex: "Peau grasse", "Peau sèche", "Peau sensible").
     * Limité à 120 caractères. C'est ce champ qui sera affiché dans les dropdowns
     * grâce à la méthode __toString() ci-dessous.
     */
    #[ORM\Column(length: 120)]
    private ?string $name = null;

    /**
     * Description détaillée du type de peau (optionnelle).
     * Stockée en TEXT en base pour permettre des textes longs.
     * Nullable car non obligatoire à la création.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Relation OneToMany vers Newsletter.
     * Un type de peau peut être associé à plusieurs newsletters (ciblage).
     * La relation est gérée côté Newsletter via le champ "skinType".
     *
     * @var Collection<int, Newsletter>
     */
    #[ORM\OneToMany(targetEntity: Newsletter::class, mappedBy: 'skinType')]
    private Collection $newsletters;

    /**
     * Relation OneToMany vers User.
     * Un type de peau peut être associé à plusieurs utilisateurs.
     * La relation est gérée côté User via le champ "skinType".
     *
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'skinType')]
    private Collection $users;

    /**
     * Constructeur — initialise les collections Doctrine.
     *
     * Doctrine utilise ArrayCollection comme implémentation concrète de Collection.
     * On initialise ici pour éviter des erreurs "null object" si on accède
     * aux relations avant que l'entité soit persistée en base.
     */
    public function __construct()
    {
        $this->newsletters = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    // ══════════════════════════════════════════════════════════════════════════════
    //  Méthode __toString()
    // ══════════════════════════════════════════════════════════════════════════════
    /**
     * Représentation textuelle de l'entité SkinType.
     *
     * POURQUOI cette méthode est indispensable :
     * Symfony et EasyAdmin ont besoin de convertir les objets en chaîne de caractères
     * dans plusieurs contextes :
     *   - Les listes déroulantes (<select>) des formulaires (EntityType, AssociationField)
     *   - Les champs autocomplete d'EasyAdmin
     *   - Les affichages de relations dans les pages de liste et de détail
     *   - Les messages de log et de debug
     *
     * ERREUR obtenue sans cette méthode :
     *   "Object of class App\Entity\SkinType could not be converted to string"
     *
     * On retourne $this->name avec un fallback '' pour éviter une erreur
     * si name est null (entité non encore persistée ou mal initialisée).
     */
    public function __toString(): string
    {
        return $this->name ?? '';
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // Getters & Setters
    // Convention Symfony : getXxx() pour lire, setXxx() pour écrire.
    // Les setters retournent "static" pour permettre le chaînage fluent :
    //   $skinType->setName('Peau grasse')->setDescription('...');
    // ══════════════════════════════════════════════════════════════════════════════

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // Gestion de la relation OneToMany → Newsletter
    //
    // Pattern standard Doctrine pour les collections :
    // - getXxx()    → retourne la collection complète
    // - addXxx()    → ajoute un élément si absent (évite les doublons avec contains())
    // - removeXxx() → supprime un élément et remet null côté propriétaire si nécessaire
    // ══════════════════════════════════════════════════════════════════════════════

    /**
     * @return Collection<int, Newsletter>
     */
    public function getNewsletters(): Collection
    {
        return $this->newsletters;
    }

    public function addNewsletter(Newsletter $newsletter): static
    {
        if (!$this->newsletters->contains($newsletter)) {
            $this->newsletters->add($newsletter);
            // On synchronise le côté propriétaire de la relation (Newsletter.skinType)
            $newsletter->setSkinType($this);
        }

        return $this;
    }

    public function removeNewsletter(Newsletter $newsletter): static
    {
        if ($this->newsletters->removeElement($newsletter)) {
            // On remet null côté propriétaire uniquement si la relation pointe encore ici
            // (elle aurait pu être changée entre-temps)
            if ($newsletter->getSkinType() === $this) {
                $newsletter->setSkinType(null);
            }
        }

        return $this;
    }

    // ══════════════════════════════════════════════════════════════════════════════
    // Gestion de la relation OneToMany → User
    // Même pattern que pour Newsletter ci-dessus.
    // ══════════════════════════════════════════════════════════════════════════════

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            // Synchronisation côté propriétaire (User.skinType)
            $user->setSkinType($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // Remet null côté User uniquement si la relation n'a pas changé
            if ($user->getSkinType() === $this) {
                $user->setSkinType(null);
            }
        }

        return $this;
    }
}
