<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class N8nHeartbeat extends Model
{
    protected $table = 'n8n_heartbeats';

    protected $fillable = ['run_at', 'severity', 'snapshot_summary', 'alerts_sent'];

    protected function casts(): array
    {
        return [
            'run_at'           => 'datetime',
            'snapshot_summary' => 'array',
            'alerts_sent'      => 'array',
        ];
    }

    public function scopeLastBeat(Builder $query): Builder
    {
        return $query->latest('run_at');
    }

    public function scopeWithSeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }
}
