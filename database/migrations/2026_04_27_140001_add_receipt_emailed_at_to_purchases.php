<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->timestamp('receipt_emailed_at')->nullable()->after('credits_granted_at');
            $table->string('smtp_message_id', 255)->nullable()->after('receipt_emailed_at');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['receipt_emailed_at', 'smtp_message_id']);
        });
    }
};
