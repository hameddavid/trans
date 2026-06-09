<?php

namespace App\Services;

use App\Models\Course;
use App\Models\RegistrationResult;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultUploadService
{
    public function uploadResults(array $data): array
    {
        $session = $data['session'];
        $semester = $data['semester'];
        $results = $data['results'];

        $this->ensureSettingExists($session, $semester);

        $created = 0;
        $updated = 0;
        $skipped = [];

        DB::beginTransaction();

        try {
            foreach ($results as $index => $row) {
                $student = $this->ensureStudentExists($row);
                if (!$student) {
                    $skipped[] = [
                        'index' => $index,
                        'matric_number' => $row['matric_number'],
                        'reason' => 'Could not resolve or create student record.',
                    ];
                    continue;
                }

                $course = $this->ensureCourseExists($row);

                $existing = RegistrationResult::where([
                    'matric_number' => $row['matric_number'],
                    'session_id' => $session,
                    'semester' => $semester,
                    'course_code' => $row['course_code'],
                ])->first();

                $record = [
                    'matric_number' => $row['matric_number'],
                    'session_id' => $session,
                    'semester' => $semester,
                    'course_code' => $row['course_code'],
                    'unit_id' => $row['unit_id'] ?? $course->unit_id ?? '',
                    'ca' => $row['ca'] ?? -1,
                    'score' => $row['score'] ?? -1,
                    'total_score' => $row['total_score'] ?? 0,
                    'grade' => $row['grade'] ?? '',
                    'remarks' => $this->gradeToRemarks($row['grade'] ?? ''),
                    'status' => $row['status'] ?? 'C',
                    'lecturer_id' => $row['lecturer_id'] ?? '',
                    'deleted' => 'N',
                    'flag_waver' => $row['flag_waver'] ?? 0,
                ];

                if ($existing) {
                    $existing->update($record);
                    $updated++;
                } else {
                    RegistrationResult::create($record);
                    $created++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Result upload failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        return [
            'session' => $session,
            'semester' => $semester,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'total_processed' => $created + $updated,
        ];
    }

    private function ensureSettingExists(string $session, int $semester): Setting
    {
        return Setting::firstOrCreate(
            ['session' => $session, 'semester' => $semester],
            ['status' => '']
        );
    }

    private function ensureStudentExists(array $row): ?Student
    {
        $student = Student::where('matric_number', $row['matric_number'])->first();

        if ($student) {
            return $student;
        }

        if (empty($row['student'])) {
            return null;
        }

        $s = $row['student'];
        return Student::create([
            'matric_number' => $row['matric_number'],
            'SURNAME' => strtoupper($s['surname'] ?? ''),
            'FIRSTNAME' => strtoupper($s['firstname'] ?? ''),
            'EMAIL1' => $s['email'] ?? null,
            'prog_code' => $s['prog_code'] ?? null,
            'sex' => $s['sex'] ?? null,
            'status' => 'active',
            'session_admitted' => $s['session_admitted'] ?? '',
        ]);
    }

    private function ensureCourseExists(array $row): Course
    {
        return Course::firstOrCreate(
            ['course_code' => $row['course_code']],
            [
                'course_title' => $row['course_title'] ?? null,
                'unit' => $row['unit'] ?? null,
                'unit_id' => $row['unit_id'] ?? null,
            ]
        );
    }

    private function gradeToRemarks(string $grade): string
    {
        return match (strtoupper($grade)) {
            'A' => 'Excellent',
            'B' => 'Very Good',
            'C' => 'Good',
            'D' => 'Fair',
            'E' => 'Pass',
            'F' => 'Poor',
            default => '',
        };
    }

    public function getSessionResults(string $session, int $semester, ?string $matricNumber = null): mixed
    {
        $query = RegistrationResult::with('student')
            ->where('session_id', $session)
            ->where('semester', $semester)
            ->where('deleted', 'N');

        if ($matricNumber) {
            $query->where('matric_number', $matricNumber);
        }

        return $query->orderBy('matric_number')->get();
    }

    public function deleteResult(string $session, int $semester, string $matricNumber, string $courseCode): bool
    {
        $record = RegistrationResult::where([
            'matric_number' => $matricNumber,
            'session_id' => $session,
            'semester' => $semester,
            'course_code' => $courseCode,
        ])->first();

        if (!$record) {
            return false;
        }

        $record->update(['deleted' => 'Y']);
        return true;
    }
}
