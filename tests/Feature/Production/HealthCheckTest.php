<?php

declare(strict_types=1);

namespace Tests\Feature\Production;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_up_returns_200(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_api_health_returns_status_json(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['status', 'app_version', 'db', 'cache', 'queue'],
                'meta',
            ])
            ->assertJsonPath('data.status', 'ok');
    }
}
