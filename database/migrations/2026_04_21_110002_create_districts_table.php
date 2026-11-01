<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedTinyInteger('city_id');
            $table->string('name', 120);
            $table->string('postal_code', 5)->nullable();
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities')->cascadeOnDelete();
            $table->index('city_id');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
