<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('business_name', 150);
            $table->text('business_description')->nullable();
            $table->string('business_type', 30)->nullable();
            $table->string('trade_register', 50)->nullable();
            $table->string('primary_city_code', 2);
            $table->string('price_tier', 20);
            $table->integer('min_price')->nullable();
            $table->integer('max_price')->nullable();
            $table->unsignedTinyInteger('years_experience')->nullable();
            $table->string('portfolio_url', 255)->nullable();
            $table->json('portfolio_images')->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->json('languages')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->integer('leads_received')->default(0);
            $table->integer('leads_accepted')->default(0);
            $table->integer('leads_refused')->default(0);
            $table->decimal('acceptance_rate', 5, 2)->default(0);
            $table->integer('credit_balance')->default(0);
            $table->boolean('onboarding_completed')->default(false);
            $table->timestamps();

            $table->index('primary_city_code');
            $table->index('price_tier');
            $table->index('verified');
            $table->index('rating_avg');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_profiles');
    }
};
