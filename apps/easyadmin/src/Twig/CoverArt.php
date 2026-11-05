<?php

namespace App\Twig;

use App\Entity\Album;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Twig\Attribute\AsTwigFilter;

/**
 * The cover of an album: a SVG, drawn from its title. The shop has no image to
 * store, the same album always has the same cover.
 */
final readonly class CoverArt
{
    private const SIZE = 100;

    #[AsTwigFilter('cover_art')]
    public function dataUri(Album $album): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($this->svg($album));
    }

    private function svg(Album $album): string
    {
        // crc32() is negative on a 32-bit PHP, as here: Mt19937 takes it as it is
        $random = new Randomizer(new Mt19937(crc32($album->getTitle() . $album->getArtist())));
        $hue = $random->getInt(0, 359);
        $background = \sprintf('hsl(%d 65%% 22%%)', $hue);
        $accent = \sprintf('hsl(%d 80%% 62%%)', ($hue + $random->getInt(120, 200)) % 360);
        $light = \sprintf('hsl(%d 90%% 85%%)', ($hue + 40) % 360);
        $shapes = match ($random->getInt(0, 3)) {
            0 => $this->grooves($random, $accent, $light),
            1 => $this->sunburst($random, $accent),
            2 => $this->stripes($random, $accent, $light),
            3 => $this->bubbles($random, $accent, $light),
        };

        return \sprintf('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d"><rect width="%1$d" height="%1$d" fill="%2$s"/>%3$s</svg>', self::SIZE, $background, $shapes);
    }

    private function grooves(Randomizer $random, string $accent, string $light): string
    {
        $x = $random->getInt(30, 70);
        $y = $random->getInt(30, 70);
        $shapes = '';
        for ($radius = 8; $radius < 90; $radius += $random->getInt(5, 11)) {
            $shapes .= \sprintf('<circle cx="%d" cy="%d" r="%d" fill="none" stroke="%s" stroke-width="%.1f"/>', $x, $y, $radius, $accent, $random->getInt(5, 25) / 10);
        }

        return $shapes . \sprintf('<circle cx="%d" cy="%d" r="5" fill="%s"/>', $x, $y, $light);
    }

    private function sunburst(Randomizer $random, string $accent): string
    {
        $rays = $random->getInt(7, 16);
        $x = $random->getInt(20, 80);
        $shapes = '';
        for ($ray = 0; $ray < $rays; ++$ray) {
            $from = 2 * M_PI * $ray / $rays;
            $to = $from + M_PI / $rays;
            $shapes .= \sprintf('<path d="M%d 100L%.1f %.1fL%.1f %.1fZ" fill="%s"/>', $x, $x + 150 * cos($from), 100 + 150 * sin($from), $x + 150 * cos($to), 100 + 150 * sin($to), $accent);
        }

        return $shapes;
    }

    private function stripes(Randomizer $random, string $accent, string $light): string
    {
        $shapes = '';
        for ($y = -20; $y < 120; $y += $random->getInt(9, 22)) {
            $shapes .= \sprintf('<rect x="-30" y="%d" width="170" height="%d" fill="%s"/>', $y, $random->getInt(3, 9), 0 === $random->getInt(0, 1) ? $accent : $light);
        }

        return \sprintf('<g transform="rotate(%d 50 50)">%s</g>', $random->getInt(-45, 45), $shapes);
    }

    private function bubbles(Randomizer $random, string $accent, string $light): string
    {
        $shapes = '';
        for ($bubble = $random->getInt(6, 12); $bubble > 0; --$bubble) {
            $shapes .= \sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s" opacity="0.8"/>', $random->getInt(0, 100), $random->getInt(0, 100), $random->getInt(4, 24), 0 === $random->getInt(0, 1) ? $accent : $light);
        }

        return $shapes;
    }
}
