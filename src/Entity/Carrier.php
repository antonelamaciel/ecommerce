<?php

namespace App\Entity;

use App\Repository\CarrierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarrierRepository::class)]
class Carrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'text')]
    private $description;

    #[ORM\Column(type: 'float', nullable: true)]
    private $price;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $type = 'standard'; // 'standard', 'long_distance', 'special'

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price ?? 0.0;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price ?? 0.0;

        return $this;
    }

    public function getCarrierLabel(): ?string
    {
        $label = "{$this->name}: [br]{$this->description}[br]";
        if ($this->type === 'long_distance') {
            $label .= " Cálculo por CP";
        } elseif ($this->type === 'special') {
            $label .= " A convenir";
        } elseif ($this->type === 'pickup') {
            $label .= " Gratis";
        } else {
            $price = number_format(($this->price ?? 0)/100, 2);
            $label .= " $price ARS";
        }
        return $label;
    }
}
