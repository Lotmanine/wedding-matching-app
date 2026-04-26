<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `status` is already a 16-char varchar in the original whatsapp_messages
 * migration — it accepts the new 'delivered' / 'read' values without schema
 * changes. The Laravel-side validation (FormRequest `in:`) is the single
 * source of truth for valid status values.
 *
 * This migration only adds the (direction, wa_message_id) covering index used
 * by the inbound idempotency lookup in InboundButtonTapController.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->index(['direction', 'wa_message_id'], 'whatsapp_messages_direction_wamid_idx');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropIndex('whatsapp_messages_direction_wamid_idx');
        });
    }
};
