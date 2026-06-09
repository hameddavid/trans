<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Course;
use App\Models\RegistrationResult;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultUploadTest extends TestCase
{
    use RefreshDatabase;

    private function adminHeaders(): array
    {
        $admin = Admin::factory()->create();
        $token = $admin->createToken('admin-token')->plainTextToken;
        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_can_upload_results_for_existing_students(): void
    {
        $headers = $this->adminHeaders();
        Student::factory()->create(['matric_number' => 'RUN/2020/0001']);

        $response = $this->postJson('/api/v1/admin/results/upload', [
            'session' => '2024/2025',
            'semester' => 1,
            'results' => [
                [
                    'matric_number' => 'RUN/2020/0001',
                    'course_code' => 'CSC 301',
                    'ca' => 25,
                    'score' => 50,
                    'total_score' => 75,
                    'grade' => 'B',
                    'course_title' => 'Data Structures',
                    'unit' => 3,
                ],
            ],
        ], $headers);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.created', 1);

        $this->assertDatabaseHas('registrations', [
            'matric_number' => 'RUN/2020/0001',
            'course_code' => 'CSC 301',
            'session_id' => '2024/2025',
        ]);

        $this->assertDatabaseHas('t_course', ['course_code' => 'CSC 301']);
        $this->assertDatabaseHas('settings', ['session' => '2024/2025', 'semester' => 1]);
    }

    public function test_creates_student_on_the_fly(): void
    {
        $headers = $this->adminHeaders();

        $response = $this->postJson('/api/v1/admin/results/upload', [
            'session' => '2024/2025',
            'semester' => 1,
            'results' => [
                [
                    'matric_number' => 'RUN/2024/9999',
                    'course_code' => 'MTH 101',
                    'total_score' => 60,
                    'grade' => 'C',
                    'student' => [
                        'surname' => 'Doe',
                        'firstname' => 'Jane',
                        'prog_code' => '100',
                        'sex' => 'Female',
                        'session_admitted' => '2024/2025',
                    ],
                ],
            ],
        ], $headers);

        $response->assertOk()
            ->assertJsonPath('data.created', 1);

        $this->assertDatabaseHas('t_student_test', [
            'matric_number' => 'RUN/2024/9999',
            'SURNAME' => 'DOE',
        ]);
    }

    public function test_skips_unknown_student_without_student_data(): void
    {
        $headers = $this->adminHeaders();

        $response = $this->postJson('/api/v1/admin/results/upload', [
            'session' => '2024/2025',
            'semester' => 1,
            'results' => [
                [
                    'matric_number' => 'FAKE/0000/0000',
                    'course_code' => 'CSC 101',
                    'total_score' => 50,
                    'grade' => 'D',
                ],
            ],
        ], $headers);

        $response->assertOk()
            ->assertJsonPath('data.created', 0)
            ->assertJsonPath('data.skipped.0.matric_number', 'FAKE/0000/0000');
    }

    public function test_updates_existing_result(): void
    {
        $headers = $this->adminHeaders();
        Student::factory()->create(['matric_number' => 'RUN/2020/0001']);

        $this->postJson('/api/v1/admin/results/upload', [
            'session' => '2024/2025',
            'semester' => 1,
            'results' => [[
                'matric_number' => 'RUN/2020/0001',
                'course_code' => 'CSC 301',
                'total_score' => 50,
                'grade' => 'D',
            ]],
        ], $headers);

        $response = $this->postJson('/api/v1/admin/results/upload', [
            'session' => '2024/2025',
            'semester' => 1,
            'results' => [[
                'matric_number' => 'RUN/2020/0001',
                'course_code' => 'CSC 301',
                'total_score' => 75,
                'grade' => 'B',
            ]],
        ], $headers);

        $response->assertOk()
            ->assertJsonPath('data.updated', 1)
            ->assertJsonPath('data.created', 0);
    }

    public function test_can_retrieve_results(): void
    {
        $headers = $this->adminHeaders();

        $response = $this->getJson('/api/v1/admin/results?session=2024/2025&semester=1', $headers);

        $response->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_can_list_sessions(): void
    {
        $headers = $this->adminHeaders();
        Setting::create(['session' => '2024/2025', 'semester' => 1, 'status' => '']);

        $response = $this->getJson('/api/v1/admin/results/sessions', $headers);

        $response->assertOk()
            ->assertJsonPath('data.0.session', '2024/2025');
    }

    public function test_can_soft_delete_result(): void
    {
        $headers = $this->adminHeaders();
        Student::factory()->create(['matric_number' => 'RUN/2020/0001']);

        $this->postJson('/api/v1/admin/results/upload', [
            'session' => '2024/2025',
            'semester' => 1,
            'results' => [[
                'matric_number' => 'RUN/2020/0001',
                'course_code' => 'CSC 301',
                'total_score' => 75,
                'grade' => 'B',
            ]],
        ], $headers);

        $response = $this->postJson('/api/v1/admin/results/delete', [
            'session' => '2024/2025',
            'semester' => 1,
            'matric_number' => 'RUN/2020/0001',
            'course_code' => 'CSC 301',
        ], $headers);

        $response->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_upload_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/admin/results/upload', [
            'session' => '2024/2025',
            'semester' => 1,
            'results' => [],
        ]);

        $response->assertUnauthorized();
    }

    public function test_upload_validates_session_format(): void
    {
        $headers = $this->adminHeaders();

        $response = $this->postJson('/api/v1/admin/results/upload', [
            'session' => 'invalid',
            'semester' => 1,
            'results' => [['matric_number' => 'X', 'course_code' => 'Y']],
        ], $headers);

        $response->assertStatus(422);
    }
}
