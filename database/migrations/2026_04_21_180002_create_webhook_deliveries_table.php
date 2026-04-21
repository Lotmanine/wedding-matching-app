<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_endpoint_id')->constrained('webhook_endpoints')->cascadeOnDelete();
            $table->string('event_type', 80);
            $table->json('payload');
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('event_type');
            $table->index('webhook_endpoint_id', 'wh_delivery_endpoint_idx');
            $table->index(['delivered_at', 'failed_at'], 'wh_delivery_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
