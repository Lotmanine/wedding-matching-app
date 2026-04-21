<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WilayaSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/seed-data/algeria-cities.json');

        if (! is_file($path)) {
            throw new RuntimeException("Seed data file not found: {$path}");
        }

        $raw = file_get_contents($path);
        $data = json_decode($raw, true);

        if (! is_array($data) || ! isset($data['wilayas'], $data['communes'])) {
            throw new RuntimeException('Invalid JSON structure — expected keys: wilayas, communes');
        }

        $now = now();

        DB::transaction(function () use ($data, $now) {
            DB::table('communes')->delete();
            DB::table('wilayas')->delete();

            $wilayaRows = [];
            foreach ($data['wilayas'] as $w) {
                $id = (int) $w['wilaya_id'];
                $wilayaRows[] = [
                    'id'         => $id,
                    'code'       => str_pad((string) $id, 2, '0', STR_PAD_LEFT),
                    'name_fr'    => $w['wilaya_name_latin'],
                    'name_ar'    => $w['wilaya_name_arabic'],
                    'region'     => null,
                    'active'     => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($wilayaRows, 100) as $chunk) {
                Wilaya::insert($chunk);
            }

            $communeRows = [];
            foreach ($data['communes'] as $c) {
                $communeRows[] = [
                    'id'          => (int) $c['commune_id'],
                    'wilaya_id'   => (int) $c['wilaya_id'],
                    'name_fr'     => $c['commune_name_latin'],
                    'name_ar'     => $c['commune_name_arabic'],
                    'postal_code' => null,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
            foreach (array_chunk($communeRows, 500) as $chunk) {
                Commune::insert($chunk);
            }
        });

        $wilayaCount = Wilaya::count();
        $communeCount = Commune::count();

        $this->command?->info("Seeded {$wilayaCount} wilayas and {$communeCount} communes");
    }
}
