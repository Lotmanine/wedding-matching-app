<?php

declare(strict_types=1);

namespace App\Enums;

enum ServiceType: string
{
    case Photo      = 'photo';
    case Video      = 'video';
    case Catering   = 'catering';
    case Decoration = 'decoration';
    case Pastry     = 'pastry';
    case Venue      = 'venue';
    case Music      = 'music';
    case Beauty     = 'beauty';

    public function label(): string
    {
        return match($this) {
            self::Photo      => __('Photographe'),
            self::Video      => __('Vidéaste'),
            self::Catering   => __('Traiteur'),
            self::Decoration => __('Décoration'),
            self::Pastry     => __('Gâteaux et pâtisseries'),
            self::Venue      => __('Salle des fêtes'),
            self::Music      => __('Musiciens / DJ'),
            self::Beauty     => __('Machta / Coiffure'),
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
