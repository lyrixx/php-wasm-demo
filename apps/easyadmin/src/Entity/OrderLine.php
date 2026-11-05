<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class OrderLine implements \Stringable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lines'), ORM\JoinColumn(nullable: false)]
    private ?Order $order = null;

    #[ORM\ManyToOne, ORM\JoinColumn(nullable: false), Assert\NotNull]
    private ?Album $album = null;

    #[ORM\Column, Assert\Positive]
    private int $quantity = 1;

    #[ORM\Column, Assert\PositiveOrZero]
    private int $unitPriceInCents = 0;

    public function __toString(): string
    {
        return \sprintf('%d × %s', $this->quantity, $this->album);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): void
    {
        $this->order = $order;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): void
    {
        $this->album = $album;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getUnitPriceInCents(): int
    {
        return $this->unitPriceInCents;
    }

    public function setUnitPriceInCents(int $unitPriceInCents): void
    {
        $this->unitPriceInCents = $unitPriceInCents;
    }

    public function getTotalInCents(): int
    {
        return $this->quantity * $this->unitPriceInCents;
    }
}
