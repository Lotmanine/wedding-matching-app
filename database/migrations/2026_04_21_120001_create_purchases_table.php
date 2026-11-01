<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_uid', 25)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('unlock_pack_id')->constrained('unlock_packs');
            $table->integer('amount');
            $table->string('currency', 3)->default('USD');
            $table->integer('unlocks_count');
            $table->string('status', 20)->default('pending');
            $table->string('gateway', 20)->default('chargily');
            $table->string('gateway_checkout_id', 100)->nullable();
            $table->string('gateway_payment_id', 100)->nullable();
            $table->json('gateway_metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason', 100)->nullable();
            $table->timestamp('credits_granted_at')->nullable();
            $table->foreignId('credit_ledger_id')->nullable()->constrained('credit_ledger')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('gateway_checkout_id', 'pur_gateway_checkout_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
