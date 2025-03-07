<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LicenseType: string implements HasIcon, HasLabel
{
    case Composer = 'composer';
    case Individual = 'individual';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Composer => 'Composer',
            self::Individual => 'Individual',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Composer => 'heroicon-o-archive-box',
            self::Individual => 'heroicon-o-at-symbol',
        };
    }
}
