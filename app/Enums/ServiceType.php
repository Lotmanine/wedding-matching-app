<?php

declare(strict_types=1);

namespace App\Enums;

enum ServiceType: string
{
    case Photo      = 'photo';
    case Video      = 'video';
    case Traiteur   = 'traiteur';
    case Decoration = 'decoration';
    case Patisserie = 'patisserie';
    case Salle      = 'salle';
    case Musique    = 'musique';
    case Machta     = 'machta';

    public function label(): string
    {
        return match($this) {
            self::Photo      => 'Photographe',
            self::Video      => 'Vidéaste',
            self::Traiteur   => 'Traiteur',
            self::Decoration => 'Décoration',
            self::Patisserie => 'Pâtisserie',
            self::Salle      => 'Salle des fêtes',
            self::Musique    => 'Musiciens / DJ',
            self::Machta     => 'Machta / Coiffure',
        };
    }
}
