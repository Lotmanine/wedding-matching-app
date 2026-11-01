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
            self::Photo      => __('Photographer'),
            self::Video      => __('Videographer'),
            self::Catering   => __('Catering'),
            self::Decoration => __('Decoration'),
            self::Pastry     => __('Cakes & pastries'),
            self::Venue      => __('Wedding venue'),
            self::Music      => __('Musicians & DJ'),
            self::Beauty     => __('Hair & makeup'),
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
