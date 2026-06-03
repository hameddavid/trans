<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_applicant_can_register_with_valid_matric(): void
    {
        Student::factory()->create(['matric_number' => 'RUN/2020/0001']);

        $response = $this->postJson('/api/v1/applicant/register', [
            'matric_number' => 'RUN/2020/0001',
            'email' => 'test@example.com',
            'phone' => '08011111111',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['status', 'token', 'applicant']);

        $this->assertDatabaseHas('applicants', ['matric_number' => 'RUN/2020/0001']);
    }

    public function test_applicant_cannot_register_with_invalid_matric(): void
    {
        $response = $this->postJson('/api/v1/applicant/register', [
            'matric_number' => 'FAKE/0000/0000',
            'email' => 'test@example.com',
            'phone' => '08011111111',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_applicant_cannot_register_twice(): void
    {
        $student = Student::factory()->create(['matric_number' => 'RUN/2020/0001']);
        Applicant::factory()->create(['matric_number' => 'RUN/2020/0001']);

        $response = $this->postJson('/api/v1/applicant/register', [
            'matric_number' => 'RUN/2020/0001',
            'email' => 'another@example.com',
            'phone' => '08022222222',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_applicant_can_login(): void
    {
        Student::factory()->create(['matric_number' => 'RUN/2020/0001']);
        Applicant::factory()->create(['matric_number' => 'RUN/2020/0001']);

        $response = $this->postJson('/api/v1/applicant/login', [
            'matno' => 'RUN/2020/0001',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['status', 'token', 'applicant']);
    }

    public function test_applicant_cannot_login_with_wrong_password(): void
    {
        Applicant::factory()->create(['matric_number' => 'RUN/2020/0001']);

        $response = $this->postJson('/api/v1/applicant/login', [
            'matno' => 'RUN/2020/0001',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_applicant_can_get_profile(): void
    {
        $applicant = Applicant::factory()->create();
        $token = $applicant->createToken('applicant-token')->plainTextToken;

        $response = $this->getJson('/api/v1/applicant/me', [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertOk()
            ->assertJsonStructure(['applicant']);
    }

    public function test_applicant_me_returns_401_without_token(): void
    {
        $response = $this->getJson('/api/v1/applicant/me');

        $response->assertUnauthorized();
    }

    public function test_applicant_can_reset_password(): void
    {
        $applicant = Applicant::factory()->create();
        $token = $applicant->createToken('applicant-token')->plainTextToken;

        $response = $this->postJson('/api/v1/applicant/reset-password', [
            'old_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ], ['Authorization' => "Bearer {$token}"]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_applicant_can_logout(): void
    {
        $applicant = Applicant::factory()->create();
        $token = $applicant->createToken('applicant-token')->plainTextToken;

        $response = $this->postJson('/api/v1/applicant/logout', [], [
            'Authorization' => "Bearer {$token}",
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_applicant_forgot_password_returns_success_for_any_email(): void
    {
        $response = $this->postJson('/api/v1/applicant/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');
    }
}
