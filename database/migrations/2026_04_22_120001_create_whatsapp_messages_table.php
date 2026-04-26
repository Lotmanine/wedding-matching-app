<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->string('service_request_uid', 25);
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->string('recipient_phone_e164', 20);
            $table->string('direction', 16)->default('outbound');
            $table->string('event_type', 64);
            $table->string('template_intent', 64)->nullable();
            $table->string('role', 16);
            $table->string('wa_message_id', 120)->nullable()->unique();
            $table->string('status', 16)->default('queued');
            $table->text('error')->nullable();
            $table->json('payload_snapshot')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('service_request_uid', 'wa_msg_sr_idx');
            $table->index('provider_id', 'wa_msg_provider_idx');
            $table->index(['service_request_uid', 'event_type'], 'wa_msg_sr_event_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
