<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds offline-payment support to purchases.
 *
 * - payment_method: 'online_chargily' (default, existing flow) or
 *   'offline_versement' (bank transfer / CCP the provider declares).
 * - payment_reference: the transfer reference the provider enters so an
 *   admin can reconcile the versement against their bank/CCP statement.
 * - proof_path: optional uploaded proof (receipt photo/PDF).
 *
 * Offline purchases sit in status 'pending_verification' until an admin
 * confirms the funds arrived and marks them paid (which grants credits).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('payment_method', 32)->default('online_chargily')->after('gateway');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->string('proof_path')->nullable()->after('payment_reference');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['payment_method']);
            $table->dropColumn(['payment_method', 'payment_reference', 'proof_path']);
        });
    }
};
