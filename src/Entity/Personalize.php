<?php

namespace App\Entity;

use App\Repository\PersonalizeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonalizeRepository::class)]
class Personalize
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $primarycolor = null;

    #[ORM\Column(length: 20)]
    private ?string $secondarycolor = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $tertiaryColor = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $companyName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getPrimarycolor(): ?string
    {
        return $this->primarycolor;
    }

    public function setPrimarycolor(?string $primarycolor): static
    {
        $this->primarycolor = $primarycolor;

        return $this;
    }

    public function getSecondarycolor(): ?string
    {
        return $this->secondarycolor;
    }

    public function setSecondarycolor(string $secondarycolor): static
    {
        $this->secondarycolor = $secondarycolor;

        return $this;
    }

    public function getTertiaryColor(): ?string
    {
        return $this->tertiaryColor;
    }

    public function setTertiaryColor(?string $tertiaryColor): static
    {
        $this->tertiaryColor = $tertiaryColor;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }
}
