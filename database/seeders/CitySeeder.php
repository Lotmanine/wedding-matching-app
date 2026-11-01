<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /** code => [country, name, districts] */
    private const CITIES = [
        'NY' => ['US', 'New York', ['Manhattan', 'Brooklyn', 'Queens', 'The Bronx']],
        'LA' => ['US', 'Los Angeles', ['Hollywood', 'Downtown', 'Venice', 'Westwood']],
        'CH' => ['US', 'Chicago', ['The Loop', 'Lincoln Park', 'Hyde Park', 'Wicker Park']],
        'MI' => ['US', 'Miami', ['Brickell', 'Wynwood', 'Coconut Grove', 'Little Havana']],
        'PA' => ['FR', 'Paris', ['Le Marais', 'Montmartre', 'Saint-Germain-des-Prés', 'Belleville']],
        'LY' => ['FR', 'Lyon', ['Presqu\'île', 'Vieux Lyon', 'La Croix-Rousse', 'Confluence']],
        'MA' => ['FR', 'Marseille', ['Le Panier', 'La Joliette', 'Noailles', 'Endoume']],
        'NI' => ['FR', 'Nice', ['Vieux Nice', 'Cimiez', 'Le Port', 'Libération']],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            DB::table('districts')->delete();
            DB::table('cities')->delete();

            $cityId = 0;
            $districtId = 0;

            foreach (self::CITIES as $code => [$country, $name, $districts]) {
                $city = City::create([
                    'id'      => ++$cityId,
                    'code'    => $code,
                    'country' => $country,
                    'name'    => $name,
                    'active'  => true,
                ]);

                foreach ($districts as $district) {
                    District::create([
                        'id'      => ++$districtId,
                        'city_id' => $city->id,
                        'name'    => $district,
                    ]);
                }
            }
        });

        $this->command?->info('Seeded ' . City::count() . ' cities and ' . District::count() . ' districts');
    }
}
