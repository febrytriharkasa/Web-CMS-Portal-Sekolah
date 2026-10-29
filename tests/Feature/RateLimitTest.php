<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_route_is_rate_limited(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->get('/cari?q=sekolah')->assertOk();
        }

        $this->get('/cari?q=sekolah')->assertStatus(429);
    }

    public function test_public_pages_allowed_within_limit(): void
    {
        $this->get('/')->assertOk();
        $this->get('/berita')->assertOk();
        $this->get('/guru-tendik')->assertOk();
    }
}
