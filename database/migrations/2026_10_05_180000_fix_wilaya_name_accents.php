<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The seed data spelled wilaya names without their French accents
 * ("Medea", "Bejaia"…). These names are shown to couples and providers.
 */
return new class extends Migration
{
    private const NAMES = [
        '06' => ['Bejaia', 'Béjaïa'],
        '08' => ['Bechar', 'Béchar'],
        '12' => ['Tebessa', 'Tébessa'],
        '19' => ['Setif', 'Sétif'],
        '20' => ['Saida', 'Saïda'],
        '22' => ['Sidi Bel Abbes', 'Sidi Bel Abbès'],
        '26' => ['Medea', 'Médéa'],
        '35' => ['Boumerdes', 'Boumerdès'],
        '44' => ['Ain Defla', 'Aïn Defla'],
        '45' => ['Naama', 'Naâma'],
        '46' => ['Ain Temouchent', 'Aïn Témouchent'],
        '47' => ['Ghardaia', 'Ghardaïa'],
        '52' => ['Beni Abbes', 'Béni Abbès'],
        '64' => ['Bou Saada', 'Bou Saâda'],
        '68' => ['Ain Oussara', 'Aïn Oussara'],
    ];

    public function up(): void
    {
        foreach (self::NAMES as $code => [$plain, $accented]) {
            DB::table('wilayas')->where('code', $code)->where('name_fr', $plain)->update(['name_fr' => $accented]);
        }
    }

    public function down(): void
    {
        foreach (self::NAMES as $code => [$plain, $accented]) {
            DB::table('wilayas')->where('code', $code)->where('name_fr', $accented)->update(['name_fr' => $plain]);
        }
    }
};
