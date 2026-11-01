<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_coverage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('city_code', 2);
            $table->integer('travel_fee')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'city_code'], 'prov_cov_unique');
            $table->index('city_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_coverage');
    }
};
