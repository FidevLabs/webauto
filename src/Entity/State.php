<?php

namespace App\Entity;

use App\Repository\StateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StateRepository::class)]
class State
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $color = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\OneToMany(mappedBy: 'state', targetEntity: StepsRequest::class)]
    private Collection $stepsRequests;

    public function __construct()
    {
        $this->stepsRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, StepsRequest>
     */
    public function getStepsRequests(): Collection
    {
        return $this->stepsRequests;
    }

    public function addStepsRequest(StepsRequest $stepsRequest): self
    {
        if (!$this->stepsRequests->contains($stepsRequest)) {
            $this->stepsRequests->add($stepsRequest);
            $stepsRequest->setState($this);
        }

        return $this;
    }

    public function removeStepsRequest(StepsRequest $stepsRequest): self
    {
        if ($this->stepsRequests->removeElement($stepsRequest)) {
            // set the owning side to null (unless already changed)
            if ($stepsRequest->getState() === $this) {
                $stepsRequest->setState(null);
            }
        }

        return $this;
    }

    public function __toString() {

        return $this->title;

    }
}
