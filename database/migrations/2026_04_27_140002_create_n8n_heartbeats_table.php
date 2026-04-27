<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('n8n_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->timestamp('run_at');
            $table->string('severity', 16);
            $table->json('snapshot_summary');
            $table->json('alerts_sent');
            $table->timestamps();

            $table->index('run_at', 'n8n_heartbeats_run_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('n8n_heartbeats');
    }
};
