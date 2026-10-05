<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Couple requests used to store the full IP address. New requests store it
 * anonymised; this brings the rows already in the table in line with the
 * privacy policy. The original addresses cannot be restored.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('wed_requests')->whereNotNull('ip_address')->orderBy('id')
            ->each(function ($row): void {
                $anonymised = IpUtils::anonymize((string) $row->ip_address);
                if ($anonymised !== $row->ip_address) {
                    DB::table('wed_requests')->where('id', $row->id)->update(['ip_address' => $anonymised]);
                }
            });
    }

    public function down(): void
    {
        // Irreversible by design.
    }
};
