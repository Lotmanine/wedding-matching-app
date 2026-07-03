<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Credit-back on bad leads ("litiges"). A provider who unlocked a lead can
 * flag it (wrong number, no response, fake…); an admin/manager reviews and
 * either refunds the credits spent or rejects the claim. One dispute per
 * provider per lead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('provider_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 30);                 // wrong_number | no_response | fake | other
            $table->text('details')->nullable();
            $table->string('status', 20)->default('pending'); // pending | approved | rejected
            $table->text('admin_note')->nullable();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('credit_ledger_id')->nullable()->constrained('credit_ledger')->nullOnDelete();
            $table->timestamps();

            $table->unique(['service_request_id', 'provider_user_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_disputes');
    }
};
