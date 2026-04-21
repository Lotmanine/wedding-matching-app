<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('communes', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedTinyInteger('wilaya_id');
            $table->string('name_fr', 120);
            $table->string('name_ar', 120);
            $table->string('postal_code', 5)->nullable();
            $table->timestamps();

            $table->foreign('wilaya_id')->references('id')->on('wilayas')->cascadeOnDelete();
            $table->index('wilaya_id');
            $table->index('name_fr');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communes');
    }
};
