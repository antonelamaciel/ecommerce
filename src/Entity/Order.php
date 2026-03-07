<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
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

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
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

    public function addOderDetail(OrderDetails $oderDetail): self
    {
        if (!$this->orderDetails->contains($oderDetail)) {
            $this->orderDetails[] = $oderDetail;
            $oderDetail->setBindedOrder($this);
        }

        return $this;
    }

    public function removeOderDetail(OrderDetails $oderDetail): self
    {
        if ($this->orderDetails->removeElement($oderDetail)) {
            // set the owning side to null (unless already changed)
            if ($oderDetail->getBindedOrder() === $this) {
                $oderDetail->setBindedOrder(null);
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
}
