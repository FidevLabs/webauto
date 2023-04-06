<?php

namespace App\Entity;

use App\Repository\AgencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgencyRepository::class)]
class Agency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\OneToMany(mappedBy: 'agency', targetEntity: StepsRequest::class, orphanRemoval: true)]
    private Collection $stepsRequests;

    #[ORM\OneToMany(mappedBy: 'agency', targetEntity: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->stepsRequests = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
            $stepsRequest->setAgency($this);
        }

        return $this;
    }

    public function removeStepsRequest(StepsRequest $stepsRequest): self
    {
        if ($this->stepsRequests->removeElement($stepsRequest)) {
            // set the owning side to null (unless already changed)
            if ($stepsRequest->getAgency() === $this) {
                $stepsRequest->setAgency(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setAgency($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getAgency() === $this) {
                $user->setAgency(null);
            }
        }

        return $this;
    }

    public function __toString() {
        return $this->name;
    }
}
