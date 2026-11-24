<?php

namespace App\Enums;

enum Rating: string
{
    case PopStar = 'Pop star';
    case RisingStar = 'Rising star';
    case GarageBand = 'Garage band';
    case KaraokeRookie = 'Karaoke rookie';

    public static function for(int $correctCount, int $questionCount): self
    {
        $ratio = 0 === $questionCount ? 0 : $correctCount / $questionCount;

        return match (true) {
            $ratio >= 0.9 => self::PopStar,
            $ratio >= 0.6 => self::RisingStar,
            $ratio >= 0.3 => self::GarageBand,
            default => self::KaraokeRookie,
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::PopStar => '🌟',
            self::RisingStar => '🚀',
            self::GarageBand => '🎸',
            self::KaraokeRookie => '🎤',
        };
    }
}
