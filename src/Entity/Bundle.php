<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\BundleRepository::class)]
class Bundle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $type;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $topRightBadge;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private $countdownTitle;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    private $countdownDescription;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $countdownHours;

    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'bundles')]
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getTopRightBadge(): ?string
    {
        return $this->topRightBadge;
    }

    public function setTopRightBadge(?string $topRightBadge): self
    {
        $this->topRightBadge = $topRightBadge;
        return $this;
    }

    public function getCountdownTitle(): ?string
    {
        return $this->countdownTitle;
    }

    public function setCountdownTitle(?string $countdownTitle): self
    {
        $this->countdownTitle = $countdownTitle;
        return $this;
    }

    public function getCountdownDescription(): ?string
    {
        return $this->countdownDescription;
    }

    public function setCountdownDescription(?string $countdownDescription): self
    {
        $this->countdownDescription = $countdownDescription;
        return $this;
    }

    public function getCountdownHours(): ?int
    {
        return $this->countdownHours;
    }

    public function setCountdownHours(?int $countdownHours): self
    {
        $this->countdownHours = $countdownHours;
        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }
}
