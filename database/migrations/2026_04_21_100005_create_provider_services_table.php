<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('service_type', 30);
            $table->timestamps();

            $table->unique(['user_id', 'service_type'], 'prov_svc_unique');
            $table->index('service_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_services');
    }
};
