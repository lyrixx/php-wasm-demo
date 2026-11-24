<?php

namespace App\Enums;

/**
 * The color of a quiz: keep in sync with the .pop-* classes of
 * public/css/pop.css.
 */
enum PopColor: string
{
    case Cyan = 'cyan';
    case Lime = 'lime';
    case Orange = 'orange';
    case Pink = 'pink';
    case Violet = 'violet';
    case Yellow = 'yellow';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
