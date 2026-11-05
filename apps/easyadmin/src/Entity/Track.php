<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Track implements \Stringable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tracks'), ORM\JoinColumn(nullable: false)]
    private ?Album $album = null;

    #[ORM\Column, Assert\Positive]
    private int $position = 1;

    #[ORM\Column(length: 120), Assert\NotBlank, Assert\Length(max: 120)]
    private string $title = '';

    #[ORM\Column, Assert\Positive]
    private int $durationInSeconds = 180;

    public function __toString(): string
    {
        return \sprintf('%d. %s (%d:%02d)', $this->position, $this->title, intdiv($this->durationInSeconds, 60), $this->durationInSeconds % 60);
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDurationInSeconds(): int
    {
        return $this->durationInSeconds;
    }

    public function setDurationInSeconds(int $durationInSeconds): void
    {
        $this->durationInSeconds = $durationInSeconds;
    }
}
