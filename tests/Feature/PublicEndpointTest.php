<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_programmes(): void
    {
        $response = $this->getJson('/api/v1/public/programmes');

        $response->assertOk();
    }

    public function test_can_get_destinations_and_amounts(): void
    {
        $response = $this->getJson('/api/v1/public/destinations');

        $response->assertOk();
    }

    public function test_verify_transcript_requires_reference(): void
    {
        $response = $this->postJson('/api/v1/public/verify-transcript', []);

        $response->assertStatus(422);
    }

    public function test_degree_verification_requires_fields(): void
    {
        $response = $this->postJson('/api/v1/public/degree-verification', []);

        $response->assertStatus(422);
    }

    public function test_spa_catch_all_returns_html(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }
}
