<?php

namespace App\Entity;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Format: string implements TranslatableInterface
{
    case BoxSet = 'box_set';
    case DoubleLp = 'double_lp';
    case Ep = 'ep';
    case Lp = 'lp';
    case Single = 'single';

    public function label(): string
    {
        return match ($this) {
            self::BoxSet => 'Box set',
            self::DoubleLp => '2×LP',
            self::Ep => 'EP',
            self::Lp => 'LP',
            self::Single => '7" single',
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), locale: $locale);
    }
}
