<?php

declare(strict_types=1);

namespace Tests\Feature\Production;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_response_includes_baseline_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    public function test_non_production_does_not_set_hsts(): void
    {
        $this->get('/')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_non_production_does_not_set_csp(): void
    {
        $this->get('/')->assertHeaderMissing('Content-Security-Policy');
    }
}
