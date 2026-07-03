<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guest count is now optional on the couple form — it is only asked (and
 * required) when a headcount-dependent service (traiteur / salle) is chosen.
 * The column must therefore allow NULL. Photographer-only requests, etc.,
 * would otherwise fail the NOT NULL constraint on submit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wed_requests', function (Blueprint $table) {
            $table->string('guest_count', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('wed_requests', function (Blueprint $table) {
            $table->string('guest_count', 30)->nullable(false)->change();
        });
    }
};
