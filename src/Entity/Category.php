<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Category — Entité de catégorisation des articles.
 * Utilisée pour filtrer les articles côté front (ex: Routine, DIY, Ingrédients...)
 * et pour organiser le contenu dans le back-office EasyAdmin.
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    // Identifiant unique auto-généré par la BDD
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Nom affiché de la catégorie (ex: "Routine", "DIY", "Ingrédients")
    #[ORM\Column(length: 120)]
    private ?string $name = null;

    // Slug unique utilisé dans les URLs et les filtres jQuery côté front
    // ex: "routine", "diy", "ingredients" — doit correspondre aux data-filter du template
    #[ORM\Column(length: 120, unique: true)]
    private ?string $slug = null;

    // Description optionnelle — affichée dans le back-office EasyAdmin
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Relation OneToMany vers Article :
     * Une catégorie peut contenir plusieurs articles.
     * mappedBy: 'category' → Article possède la clé étrangère $category.
     *
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'category')]
    private Collection $articles;

    // Initialisation de la collection articles à la création de l'objet
    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    // ✅ AJOUT — permet à EasyAdmin d'afficher le nom de la catégorie
    // au lieu de "Category #36" dans les listes et les champs de relation
    // Appelé automatiquement par Twig et EasyAdmin lors de l'affichage
    public function __toString(): string
    {
        return $this->name ?? '';
    }

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
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

    // ── Gestion de la relation OneToMany → Article ────────────────────────
    // Pattern standard Doctrine :
    // - getArticles()    → retourne tous les articles de la catégorie
    // - addArticle()     → ajoute un article et synchronise la relation inverse
    // - removeArticle()  → retire l'article et remet null côté propriétaire

    /** @return Collection<int, Article> */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            // Synchronisation du côté propriétaire (Article.category)
            $article->setCategory($this);
        }
        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            // Remet null uniquement si la relation pointe encore sur cette catégorie
            if ($article->getCategory() === $this) {
                $article->setCategory(null);
            }
        }
        return $this;
    }
}