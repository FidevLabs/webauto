<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    #[ORM\OneToMany(mappedBy: 'payment', targetEntity: StepsRequest::class)]
    private Collection $stepsRequests;

    public function __construct()
    {
        $this->stepsRequests = new ArrayCollection();
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

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): self
    {
        $this->img = $img;

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
            $stepsRequest->setPayment($this);
        }

        return $this;
    }

    public function removeStepsRequest(StepsRequest $stepsRequest): self
    {
        if ($this->stepsRequests->removeElement($stepsRequest)) {
            // set the owning side to null (unless already changed)
            if ($stepsRequest->getPayment() === $this) {
                $stepsRequest->setPayment(null);
            }
        }

        return $this;
    }

    public function __toString() {

        return $this->name;
        
    }
}
