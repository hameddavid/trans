<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Applicant;
use App\Models\OfficialApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedAdmin(array $attrs = []): array
    {
        $admin = Admin::factory()->create($attrs);
        $token = $admin->createToken('admin-token')->plainTextToken;

        return [$admin, ['Authorization' => "Bearer {$token}"]];
    }

    public function test_admin_can_access_dashboard(): void
    {
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('Dashboard uses MySQL-specific MONTH() function.');
        }

        [$admin, $headers] = $this->authenticatedAdmin();

        $response = $this->getJson('/api/v1/admin/dashboard', $headers);

        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/v1/admin/dashboard');

        $response->assertUnauthorized();
    }

    public function test_admin_can_view_applicants(): void
    {
        [$admin, $headers] = $this->authenticatedAdmin();

        $response = $this->getJson('/api/v1/admin/applicants', $headers);

        $response->assertOk();
    }

    public function test_admin_can_view_payments(): void
    {
        [$admin, $headers] = $this->authenticatedAdmin();

        $response = $this->getJson('/api/v1/admin/payments', $headers);

        $response->assertOk();
    }

    public function test_admin_can_view_pending_applications(): void
    {
        [$admin, $headers] = $this->authenticatedAdmin();

        $response = $this->getJson('/api/v1/admin/applications/pending-official', $headers);

        $response->assertOk();
    }

    public function test_admin_can_view_forgot_matric_requests(): void
    {
        [$admin, $headers] = $this->authenticatedAdmin();

        $response = $this->getJson('/api/v1/admin/forgot-matric-requests', $headers);

        $response->assertOk();
    }

    public function test_admin_can_view_pending_degree_verifications(): void
    {
        [$admin, $headers] = $this->authenticatedAdmin();

        $response = $this->getJson('/api/v1/admin/degree-verification/pending', $headers);

        $response->assertOk();
    }

    public function test_admin_can_view_generated_transcripts(): void
    {
        [$admin, $headers] = $this->authenticatedAdmin();

        $response = $this->getJson('/api/v1/admin/generated-transcripts', $headers);

        $response->assertOk();
    }
}
