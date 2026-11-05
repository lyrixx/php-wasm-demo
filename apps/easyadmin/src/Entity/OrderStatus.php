<?php

namespace App\Entity;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum OrderStatus: string implements TranslatableInterface
{
    case Cancelled = 'cancelled';
    case Delivered = 'delivered';
    case Paid = 'paid';
    case Pending = 'pending';
    case Shipped = 'shipped';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * The color of its badge, one of EasyAdmin.
     */
    public function badge(): string
    {
        return match ($this) {
            self::Cancelled => 'danger',
            self::Delivered => 'success',
            self::Paid => 'info',
            self::Pending => 'warning',
            self::Shipped => 'primary',
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), locale: $locale);
    }
}
