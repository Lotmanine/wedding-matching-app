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

    /**
     * Service types currently offered on the platform. Admins can deactivate
     * a service from /admin/catalog (stored as the `catalog.disabled_services`
     * setting); deactivated services disappear from NEW selections (couple
     * form, provider registration) while existing data keeps working.
     *
     * @return list<self>
     */
    public static function activeCases(): array
    {
        $disabled = (array) \App\Models\Setting::get('catalog.disabled_services', []);

        return array_values(array_filter(
            self::cases(),
            fn (self $c) => ! in_array($c->value, $disabled, true),
        ));
    }

    /**
     * @return list<string>
     */
    public static function activeValues(): array
    {
        return array_map(fn (self $c) => $c->value, self::activeCases());
    }

    public function isActive(): bool
    {
        return in_array($this->value, self::activeValues(), true);
    }
}
