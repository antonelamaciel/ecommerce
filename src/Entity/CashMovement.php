<?php

namespace App\Entity;

use App\Repository\CashMovementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CashMovementRepository::class)]
class CashMovement
{
    public const TYPE_INGRESS = 'ingreso';
    public const TYPE_EGRESS = 'egreso';

    public const REASON_SALE = 'venta';
    public const REASON_PURCHASE = 'compra mercadería';
    public const REASON_SHIPPING = 'envío';
    public const REASON_OWN_INGRESS = 'ingreso propio (de mi cuenta)';
    public const REASON_WITHDRAWAL = 'retiro';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $reason = null;

    #[ORM\Column(type: 'float')]
    private ?float $amount = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $orderReference = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getOrderReference(): ?string
    {
        return $this->orderReference;
    }

    public function setOrderReference(?string $orderReference): self
    {
        $this->orderReference = $orderReference;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
