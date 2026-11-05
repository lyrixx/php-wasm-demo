<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
class Album implements \Stringable
{
    public const LOW_STOCK = 5;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120), Assert\NotBlank, Assert\Length(max: 120)]
    private string $title = '';

    #[ORM\ManyToOne(inversedBy: 'albums'), ORM\JoinColumn(nullable: false), Assert\NotNull]
    private ?Artist $artist = null;

    #[ORM\ManyToOne(inversedBy: 'albums'), ORM\JoinColumn(nullable: false), Assert\NotNull]
    private ?Genre $genre = null;

    #[ORM\Column, Assert\Range(min: 1948, max: 2100)]
    private int $releaseYear = 2026;

    #[ORM\Column]
    private Format $format = Format::Lp;

    #[ORM\Column, Assert\PositiveOrZero]
    private int $priceInCents = 0;

    #[ORM\Column, Assert\PositiveOrZero]
    private int $stock = 0;

    #[ORM\Column]
    private bool $featured = false;

    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    /**
     * @var Collection<int, Track>
     */
    #[ORM\OneToMany(targetEntity: Track::class, mappedBy: 'album', cascade: ['persist', 'remove'], orphanRemoval: true), ORM\OrderBy(['position' => 'ASC']), Assert\Valid]
    private Collection $tracks;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'album', cascade: ['remove'])]
    private Collection $reviews;

    public function __construct()
    {
        $this->tracks = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function __toString(): string
    {
        return \sprintf('%s – %s', $this->artist?->getName() ?? '?', $this->title);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artist): void
    {
        $this->artist = $artist;
    }

    public function getGenre(): ?Genre
    {
        return $this->genre;
    }

    public function setGenre(?Genre $genre): void
    {
        $this->genre = $genre;
    }

    public function getReleaseYear(): int
    {
        return $this->releaseYear;
    }

    public function setReleaseYear(int $releaseYear): void
    {
        $this->releaseYear = $releaseYear;
    }

    public function getFormat(): Format
    {
        return $this->format;
    }

    public function setFormat(Format $format): void
    {
        $this->format = $format;
    }

    public function getPriceInCents(): int
    {
        return $this->priceInCents;
    }

    public function setPriceInCents(int $priceInCents): void
    {
        $this->priceInCents = $priceInCents;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function isLowStock(): bool
    {
        return $this->stock <= self::LOW_STOCK;
    }

    public function restock(int $copies): void
    {
        $this->stock += $copies;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): void
    {
        $this->featured = $featured;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description ?? '';
    }

    /**
     * @return Collection<int, Track>
     */
    public function getTracks(): Collection
    {
        return $this->tracks;
    }

    public function addTrack(Track $track): void
    {
        $track->setAlbum($this);
        $this->tracks->add($track);
    }

    public function removeTrack(Track $track): void
    {
        $this->tracks->removeElement($track);
    }

    public function getDurationInSeconds(): int
    {
        return array_sum($this->tracks->map(static fn (Track $track): int => $track->getDurationInSeconds())->toArray());
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function getAverageRating(): ?float
    {
        $ratings = $this->reviews->map(static fn (Review $review): int => $review->getRating())->toArray();

        return [] === $ratings ? null : array_sum($ratings) / \count($ratings);
    }
}
