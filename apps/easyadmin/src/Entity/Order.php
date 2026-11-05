<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

// "order" is a word of SQL
#[ORM\Entity(repositoryClass: OrderRepository::class), ORM\Table(name: 'shop_order')]
class Order implements \Stringable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $reference;

    #[ORM\ManyToOne(inversedBy: 'orders'), ORM\JoinColumn(nullable: false), Assert\NotNull]
    private ?Customer $customer = null;

    #[ORM\Column]
    private OrderStatus $status = OrderStatus::Pending;

    #[ORM\Column]
    private \DateTimeImmutable $placedAt;

    /**
     * @var Collection<int, OrderLine>
     */
    #[ORM\OneToMany(targetEntity: OrderLine::class, mappedBy: 'order', cascade: ['persist', 'remove'], orphanRemoval: true), Assert\Valid, Assert\Count(min: 1)]
    private Collection $lines;

    public function __construct()
    {
        $this->reference = strtoupper(\sprintf('WE-%s', bin2hex(random_bytes(3))));
        $this->placedAt = new \DateTimeImmutable();
        $this->lines = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->reference;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): void
    {
        $this->status = $status;
    }

    public function canShip(): bool
    {
        return OrderStatus::Paid === $this->status;
    }

    public function ship(): void
    {
        if (!$this->canShip()) {
            throw new \LogicException(\sprintf('The order %s is %s, only a paid order ships.', $this->reference, $this->status->value));
        }

        $this->status = OrderStatus::Shipped;
    }

    public function getPlacedAt(): \DateTimeImmutable
    {
        return $this->placedAt;
    }

    public function setPlacedAt(\DateTimeImmutable $placedAt): void
    {
        $this->placedAt = $placedAt;
    }

    /**
     * @return Collection<int, OrderLine>
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    public function addLine(OrderLine $line): void
    {
        $line->setOrder($this);
        $this->lines->add($line);
    }

    public function removeLine(OrderLine $line): void
    {
        $this->lines->removeElement($line);
    }

    public function getTotalInCents(): int
    {
        return array_sum($this->lines->map(static fn (OrderLine $line): int => $line->getTotalInCents())->toArray());
    }
}
