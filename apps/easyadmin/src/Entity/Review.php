<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review implements \Stringable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reviews'), ORM\JoinColumn(nullable: false), Assert\NotNull]
    private ?Album $album = null;

    #[ORM\ManyToOne, ORM\JoinColumn(nullable: false), Assert\NotNull]
    private ?Customer $customer = null;

    #[ORM\Column, Assert\Range(min: 1, max: 5)]
    private int $rating = 5;

    #[ORM\Column(type: Types::TEXT), Assert\NotBlank]
    private string $comment = '';

    #[ORM\Column]
    private bool $approved = false;

    #[ORM\Column]
    private \DateTimeImmutable $writtenAt;

    public function __construct()
    {
        $this->writtenAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return \sprintf('%d/5 on %s', $this->rating, $this->album);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): void
    {
        $this->album = $album;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): void
    {
        $this->rating = $rating;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment ?? '';
    }

    public function isApproved(): bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): void
    {
        $this->approved = $approved;
    }

    public function approve(): void
    {
        $this->approved = true;
    }

    public function getWrittenAt(): \DateTimeImmutable
    {
        return $this->writtenAt;
    }

    public function setWrittenAt(\DateTimeImmutable $writtenAt): void
    {
        $this->writtenAt = $writtenAt;
    }
}
