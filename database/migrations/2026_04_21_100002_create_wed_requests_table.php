<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wed_requests', function (Blueprint $table) {
            $table->id();
            $table->string('wed_uid', 25)->unique();
            $table->string('status', 30)->default('new');
            $table->string('couple_first_name', 80);
            $table->string('couple_whatsapp', 20);
            $table->string('couple_wilaya_code', 2);
            $table->string('couple_commune', 100)->nullable();
            $table->date('wedding_date')->nullable();
            $table->string('wedding_season', 20)->nullable();
            $table->string('guest_count', 30);
            $table->string('budget_range', 30);
            $table->text('description')->nullable();
            $table->json('services_needed');
            $table->boolean('budget_warning_shown')->default(false);
            $table->string('source', 20)->default('form');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('couple_wilaya_code');
            $table->index('wedding_date');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wed_requests');
    }
};
