<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['status', 'token', 'admin']);
    }

    public function test_admin_cannot_login_with_wrong_password(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_inactive_admin_cannot_login(): void
    {
        $admin = Admin::factory()->inactive()->create();

        $response = $this->postJson('/api/v1/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_register(): void
    {
        $response = $this->postJson('/api/v1/admin/register', [
            'surname' => 'Test',
            'firstname' => 'Admin',
            'phone' => '08012345678',
            'email' => 'newadmin@run.edu.ng',
            'role' => '200',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('admin', ['email' => 'newadmin@run.edu.ng']);
    }

    public function test_admin_register_rejects_invalid_role(): void
    {
        $response = $this->postJson('/api/v1/admin/register', [
            'surname' => 'Test',
            'firstname' => 'Admin',
            'phone' => '08012345678',
            'email' => 'bad@run.edu.ng',
            'role' => '999',
        ]);

        $response->assertStatus(422);
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

    public function test_admin_can_reset_password(): void
    {
        $admin = Admin::factory()->create();
        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->postJson('/api/v1/admin/reset-password', [
            'old_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ], ['Authorization' => "Bearer {$token}"]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');
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
