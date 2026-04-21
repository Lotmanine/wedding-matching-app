<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('service_uid', 25)->unique();
            $table->foreignId('wed_request_id')->constrained('wed_requests')->cascadeOnDelete();
            $table->string('service_type', 30);
            $table->string('status', 30)->default('new');
            $table->unsignedTinyInteger('current_round')->default(0);
            $table->timestamp('round_started_at')->nullable();
            $table->json('round_1_provider_ids')->nullable();
            $table->json('round_2_provider_ids')->nullable();
            $table->json('round_3_provider_ids')->nullable();
            $table->json('all_notified_provider_ids')->nullable();
            $table->json('refused_by_provider_ids')->nullable();
            $table->foreignId('unlocked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('unlocked_at')->nullable();
            $table->string('unlocked_via', 20)->nullable();
            $table->integer('credits_charged')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('service_type');
            $table->index('current_round');
            $table->index('round_started_at');
            $table->index('unlocked_by_user_id', 'svc_unlocked_by_idx');
            $table->unique(['wed_request_id', 'service_type'], 'svc_wed_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
