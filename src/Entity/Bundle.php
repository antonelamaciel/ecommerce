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

    #[ORM\Column(type: 'boolean')]
    private $isPromoActive = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $countdownEndsAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $discountPercentage;

    #[ORM\Column(type: 'boolean')]
    private $isBannerActive = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $bannerText;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private $bannerColor;

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
        
        // If the promo is already active, we update the end time to start from now with the new hours
        if ($this->isPromoActive && $countdownHours !== null) {
            $this->countdownEndsAt = (new \DateTimeImmutable())->modify('+' . $countdownHours . ' hours');
        } elseif (!$this->isPromoActive) {
            // If inactive, ensure we don't have an old end time
            $this->countdownEndsAt = null;
        }

        return $this;
    }

    public function isPromoActive(): ?bool
    {
        return $this->isPromoActive;
    }

    public function setIsPromoActive(bool $isPromoActive): self
    {
        // When transitioning to active, we set the end time starting from NOW
        if ($isPromoActive && !$this->isPromoActive) {
            if ($this->countdownHours !== null) {
                $this->countdownEndsAt = (new \DateTimeImmutable())->modify('+' . $this->countdownHours . ' hours');
            }
        } elseif (!$isPromoActive) {
            // When deactivating (or keeping inactive), we clear the end time (reset)
            $this->countdownEndsAt = null;
        }

        $this->isPromoActive = $isPromoActive;
        return $this;
    }

    public function getCountdownEndsAt(): ?\DateTimeImmutable
    {
        return $this->countdownEndsAt;
    }

    public function setCountdownEndsAt(?\DateTimeImmutable $countdownEndsAt): self
    {
        $this->countdownEndsAt = $countdownEndsAt;
        return $this;
    }

    public function getDiscountPercentage(): ?int
    {
        return $this->discountPercentage;
    }

    public function setDiscountPercentage(?int $discountPercentage): self
    {
        $this->discountPercentage = $discountPercentage;
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
    public function isBannerActive(): ?bool
    {
        return $this->isBannerActive;
    }

    public function setIsBannerActive(bool $isBannerActive): self
    {
        $this->isBannerActive = $isBannerActive;
        return $this;
    }

    public function getBannerText(): ?string
    {
        return $this->bannerText;
    }

    public function setBannerText(?string $bannerText): self
    {
        $this->bannerText = $bannerText;
        return $this;
    }

    public function getBannerColor(): ?string
    {
        return $this->bannerColor;
    }

    public function setBannerColor(?string $bannerColor): self
    {
        $this->bannerColor = $bannerColor;
        return $this;
    }
}
