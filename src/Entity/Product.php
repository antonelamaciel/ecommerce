<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\PrePersist]
    public function generateSlug(): void
    {
        if (!$this->slug) {
            $base = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->name)));
            $this->slug = $base . '-' . bin2hex(random_bytes(3));
        }
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Este campo es obligatorio")]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $image;

    #[ORM\Column(type: 'json', nullable: true)]
    private $images = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $subtitle;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

    #[ORM\Column(type: 'float')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Este campo es obligatorio")]
    private $price;

    #[ORM\Column(type: 'float', nullable: true)]
    private $oldPrice;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Este campo es obligatorio")]
    private $category;

    #[ORM\Column(type: 'boolean')]
    private $isInHome;

    #[ORM\ManyToMany(targetEntity: Subcategory::class, inversedBy: 'products')]
    private $subcategories;

    #[ORM\ManyToMany(targetEntity: Bundle::class, mappedBy: 'products')]
    private $bundles;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $stock;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductOption::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $options;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $purchaseCost = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $purchaseDate = null;

    #[ORM\ManyToOne(targetEntity: Supplier::class, inversedBy: 'products')]
    private ?Supplier $supplier = null;

    public function __construct()
    {
        $this->subcategories = new ArrayCollection();
        $this->bundles = new ArrayCollection();
        $this->options = new ArrayCollection();
    }

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getPurchaseCost(): ?float
    {
        return $this->purchaseCost;
    }

    public function setPurchaseCost(?float $purchaseCost): self
    {
        $this->purchaseCost = $purchaseCost;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeInterface
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(?\DateTimeInterface $purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): self
    {
        $this->images = $images;

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): self
    {
        $this->subtitle = $subtitle;

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
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getOldPrice(): ?float
    {
        return $this->oldPrice;
    }

    public function setOldPrice(?float $oldPrice): self
    {
        $this->oldPrice = $oldPrice;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getIsInHome(): ?bool
    {
        return $this->isInHome;
    }

    public function setIsInHome(bool $isInHome): self
    {
        $this->isInHome = $isInHome;

        return $this;
    }

    /**
     * @return Collection|Subcategory[]
     */
    public function getSubcategories(): Collection
    {
        return $this->subcategories;
    }

    public function addSubcategory(Subcategory $subcategory): self
    {
        if (!$this->subcategories->contains($subcategory)) {
            $this->subcategories[] = $subcategory;
        }

        return $this;
    }

    public function removeSubcategory(Subcategory $subcategory): self
    {
        $this->subcategories->removeElement($subcategory);

        return $this;
    }

    /**
     * @return Collection|Bundle[]
     */
    public function getBundles(): Collection
    {
        return $this->bundles;
    }

    public function addBundle(Bundle $bundle): self
    {
        if (!$this->bundles->contains($bundle)) {
            $this->bundles[] = $bundle;
            $bundle->addProduct($this);
        }

        return $this;
    }

    public function removeBundle(Bundle $bundle): self
    {
        if ($this->bundles->removeElement($bundle)) {
            $bundle->removeProduct($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(ProductOption $option): static
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
            $option->setProduct($this);
        }

        return $this;
    }

    public function removeOption(ProductOption $option): static
    {
        if ($this->options->removeElement($option)) {
            // set the owning side to null (unless already changed)
            if ($option->getProduct() === $this) {
                $option->setProduct(null);
            }
        }

        return $this;
    }

    public function getMaxDiscount(): int
    {
        $maxDiscount = 0;
        foreach ($this->bundles as $bundle) {
            if ($bundle->getDiscountPercentage() > $maxDiscount) {
                $maxDiscount = (int) $bundle->getDiscountPercentage();
            }
        }
        return $maxDiscount;
    }
    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }
}
