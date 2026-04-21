<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('couple')->after('password');
            $table->string('phone', 20)->nullable()->after('role');
            $table->string('whatsapp', 20)->nullable()->after('phone');
            $table->string('wilaya_code', 2)->nullable()->after('whatsapp');
            $table->string('status', 30)->default('active')->after('wilaya_code');
            $table->timestamp('last_active_at')->nullable()->after('status');

            $table->index('role');
            $table->index('status');
            $table->index('wilaya_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropIndex(['wilaya_code']);
            $table->dropColumn(['role', 'phone', 'whatsapp', 'wilaya_code', 'status', 'last_active_at']);
        });
    }
};
