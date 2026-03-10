<?php

namespace App\Entity;

use App\Repository\RoutineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoutineRepository::class)]
class Routine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $durationMinutes = null;

    #[ORM\Column]
    private ?int $stepCount = null;

    /**
     * @var Collection<int, RoutineStep>
     */
    #[ORM\OneToMany(targetEntity: RoutineStep::class, mappedBy: 'routine')]
    private Collection $routineSteps;

    public function __construct()
    {
        $this->routineSteps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
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

    public function getDurationMinutes(): ?int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;

        return $this;
    }

    public function getStepCount(): ?int
    {
        return $this->stepCount;
    }

    public function setStepCount(int $stepCount): static
    {
        $this->stepCount = $stepCount;

        return $this;
    }

    /**
     * @return Collection<int, RoutineStep>
     */
    public function getRoutineSteps(): Collection
    {
        return $this->routineSteps;
    }

    public function addRoutineStep(RoutineStep $routineStep): static
    {
        if (!$this->routineSteps->contains($routineStep)) {
            $this->routineSteps->add($routineStep);
            $routineStep->setRoutine($this);
        }

        return $this;
    }

    public function removeRoutineStep(RoutineStep $routineStep): static
    {
        if ($this->routineSteps->removeElement($routineStep)) {
            // set the owning side to null (unless already changed)
            if ($routineStep->getRoutine() === $this) {
                $routineStep->setRoutine(null);
            }
        }

        return $this;
    }
}
