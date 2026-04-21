<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->text('admin_notes')->nullable()->after('onboarding_completed');
            $table->timestamp('suspended_at')->nullable()->after('admin_notes');
            $table->text('suspended_reason')->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn(['admin_notes', 'suspended_at', 'suspended_reason']);
        });
    }
};
