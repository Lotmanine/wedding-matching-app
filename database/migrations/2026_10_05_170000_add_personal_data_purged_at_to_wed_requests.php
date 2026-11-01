<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks couple requests whose personal data has been erased by
 * `wedding:cleanup-expired` (soft-deleting the row alone left the first name
 * and WhatsApp number in the database).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wed_requests', function (Blueprint $table) {
            $table->timestamp('personal_data_purged_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('wed_requests', function (Blueprint $table) {
            $table->dropColumn('personal_data_purged_at');
        });
    }
};
