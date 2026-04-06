<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $carrierName = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $carrierPrice = null;

    #[ORM\Column(type: 'text')]
    private ?string $delivery = null;

    #[ORM\OneToMany(mappedBy: 'bindedOrder', targetEntity: OrderDetails::class)]
    private Collection $orderDetails;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $stripeSession = null;

    #[ORM\Column(type: 'integer')]
    private ?int $state = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isRead = false;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $grossProfit = 0;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
        $this->isRead = false;
        $this->grossProfit = 0;
    }

    public function getGrossProfit(): ?float
    {
        return $this->grossProfit;
    }

    public function setGrossProfit(?float $grossProfit): self
    {
        $this->grossProfit = $grossProfit;

        return $this;
    }

    /**
     * Recalculates the gross profit based on current order details.
     * Useful when creating the order.
     */
    public function calculateGrossProfit(): float
    {
        $profit = 0;
        foreach ($this->getOrderDetails() as $detail) {
            $cost = $detail->getPurchaseCost() ?? 0;
            $profit += ($detail->getPrice() - $cost) * $detail->getQuantity();
        }
        $this->grossProfit = $profit;
        return $profit;
    }

    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCarrierName(): ?string
    {
        return $this->carrierName;
    }

    public function setCarrierName(string $carrierName): self
    {
        $this->carrierName = $carrierName;

        return $this;
    }

    public function getCarrierPrice(): ?string
    {
        return $this->carrierPrice;
    }

    public function setCarrierPrice(string $carrierPrice): self
    {
        $this->carrierPrice = $carrierPrice;

        return $this;
    }

    public function getDelivery(): ?string
    {
        return $this->delivery;
    }

    public function setDelivery(string $delivery): self
    {
        $this->delivery = $delivery;

        return $this;
    }

    /**
     * @return Collection|OrderDetails[]
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): self
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails[] = $orderDetail;
            $orderDetail->setBindedOrder($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): self
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getBindedOrder() === $this) {
                $orderDetail->setBindedOrder(null);
            }
        }

        return $this;
    }

    public function getTotal():float
    {
        $total = 0;
        foreach ($this->getOrderDetails() as $product) {
            $total += $product->getTotal();
        }
        return $total;
  
    }

    public function getTotalQuantity():float
    {
        $total = 0;
        foreach ($this->getOrderDetails() as $product) {
            $total += $product->getQuantity();
        }
        return $total;
  
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getStripeSession(): ?string
    {
        return $this->stripeSession;
    }

    public function setStripeSession(?string $stripeSession): self
    {
        $this->stripeSession = $stripeSession;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Virtual property for EasyAdmin index summary
     */
    public function getProductSummary(): string
    {
        $summary = [];
        foreach ($this->getOrderDetails() as $detail) {
            $variants = $detail->getVariants() ? " (".$detail->getVariants().")" : "";
            $summary[] = $detail->getProduct() . $variants . " x" . $detail->getQuantity();
        }
        return implode(", ", $summary);
    }

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $receiptFilename = null;

    public function getReceiptFilename(): ?string
    {
        return $this->receiptFilename;
    }

    public function setReceiptFilename(?string $receiptFilename): self
    {
        $this->receiptFilename = $receiptFilename;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateGrossProfit(): void
    {
        $this->calculateGrossProfit();
    }
}
