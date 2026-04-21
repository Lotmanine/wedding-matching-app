<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wilayas', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('code', 2)->unique();
            $table->string('name_fr', 100);
            $table->string('name_ar', 100);
            $table->string('region', 30)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('name_fr');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayas');
    }
};
