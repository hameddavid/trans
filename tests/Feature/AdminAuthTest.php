<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = Admin::factory()->create(['email' => 'staff@run.edu.ng']);

        Http::fake([
            'staff.run.edu.ng/*' => Http::response([
                'status' => 'ok',
                'lastname' => $admin->surname,
                'firstname' => $admin->firstname,
                'middlename' => '',
                'title' => 'Mr',
                'designation' => 'Lecturer',
                'dept' => 'Computer Science',
                'userid' => 'STF001',
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'staff@run.edu.ng',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['status', 'token', 'admin']);
    }

    public function test_admin_cannot_login_with_wrong_password(): void
    {
        $admin = Admin::factory()->create();

        Http::fake([
            'staff.run.edu.ng/*' => Http::response([
                'status' => 'failed',
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_inactive_admin_cannot_login(): void
    {
        $admin = Admin::factory()->inactive()->create(['email' => 'inactive@run.edu.ng']);

        Http::fake([
            'staff.run.edu.ng/*' => Http::response([
                'status' => 'ok',
                'lastname' => $admin->surname,
                'firstname' => $admin->firstname,
                'middlename' => '',
                'title' => 'Mr',
                'designation' => 'Lecturer',
                'dept' => 'Computer Science',
                'userid' => 'STF002',
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'inactive@run.edu.ng',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_unregistered_staff_gets_access_request_logged(): void
    {
        Http::fake([
            'staff.run.edu.ng/*' => Http::response([
                'status' => 'ok',
                'lastname' => 'Doe',
                'firstname' => 'Jane',
                'middlename' => '',
                'title' => 'Mrs',
                'designation' => 'Lecturer',
                'dept' => 'Physics',
                'userid' => 'STF099',
            ], 200),
        ]);

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => 'unknown@run.edu.ng',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('admin_access_requests', ['email' => 'unknown@run.edu.ng']);
    }

    public function test_admin_can_get_profile(): void
    {
        $admin = Admin::factory()->create();
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->getJson('/api/v1/admin/me', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertOk()
            ->assertJsonStructure(['admin']);
    }

    public function test_admin_me_returns_401_without_token(): void
    {
        $response = $this->getJson('/api/v1/admin/me');

        $response->assertUnauthorized();
    }

    public function test_admin_reset_password_is_disabled(): void
    {
        $admin = Admin::factory()->create();
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->postJson('/api/v1/admin/reset-password', [
            'old_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ], ['Authorization' => "Bearer {$token}"]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['old_password']);
    }

    public function test_admin_can_logout(): void
    {
        $admin = Admin::factory()->create();
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->postJson('/api/v1/admin/logout', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');
    }
}
