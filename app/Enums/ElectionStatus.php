<?php

namespace App\Enums;

enum ElectionStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Open = 'open';
    case Suspended = 'suspended';
    case Closed = 'closed';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Scheduled => 'Programmé',
            self::Open => 'Ouvert',
            self::Suspended => 'Suspendu',
            self::Closed => 'Clôturé',
            self::Published => 'Publié',
        };
    }
}
