<?php

namespace App\Entity;

use App\Repository\OrderDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderDetailsRepository::class)]
class OrderDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $bindedOrder = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $product = null;

    #[ORM\Column(type: 'integer')]
    private ?int $quantity = null;

    #[ORM\Column(type: 'float')]
    private ?float $price = null;

    #[ORM\Column(type: 'float')]
    private ?float $total = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $variants = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Product $productObject = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVariants(): ?string
    {
        return $this->variants;
    }

    public function setVariants(?string $variants): self
    {
        $this->variants = $variants;

        return $this;
    }

    public function getBindedOrder(): ?Order
    {
        return $this->bindedOrder;
    }

    public function setBindedOrder(?Order $bindedOrder): self
    {
        $this->bindedOrder = $bindedOrder;

        return $this;
    }

    public function getProduct(): ?string
    {
        return $this->product;
    }

    public function setProduct(string $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getProductObject(): ?Product
    {
        return $this->productObject;
    }

    public function setProductObject(?Product $productObject): self
    {
        $this->productObject = $productObject;

        return $this;
    }

    public function __toString()
    {
        return $this->getProduct() . 'x' . $this->getQuantity();
    }
}
