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
    private const CHUNK_SIZE = 1000;

    public function uploadResults(array $data): array
    {
        $session = $data['session'];
        $semester = (int) $data['semester'];
        $results = $data['results'];

        $this->ensureSettingExists($session, $semester);

        $skipped = [];
        $totalUpserted = 0;

        foreach (array_chunk($results, self::CHUNK_SIZE, true) as $chunk) {
            $chunkResult = $this->processChunk($chunk, $session, $semester);
            $totalUpserted += $chunkResult['upserted'];
            array_push($skipped, ...$chunkResult['skipped']);
        }

        return [
            'session' => $session,
            'semester' => $semester,
            'total_processed' => $totalUpserted,
            'skipped_count' => count($skipped),
            'skipped' => array_slice($skipped, 0, 100),
        ];
    }

    private function processChunk(array $chunk, string $session, int $semester): array
    {
        $matricNumbers = array_unique(array_column($chunk, 'matric_number'));
        $courseCodes = array_unique(array_column($chunk, 'course_code'));

        $existingStudents = Student::whereIn('matric_number', $matricNumbers)
            ->pluck('matric_number')
            ->flip()
            ->all();

        $this->bulkEnsureCourses($chunk, $courseCodes);

        $courseUnitMap = Course::whereIn('course_code', $courseCodes)
            ->pluck('unit_id', 'course_code')
            ->all();

        $newStudents = [];
        foreach ($chunk as $row) {
            if (!isset($existingStudents[$row['matric_number']]) && !empty($row['student'])) {
                $newStudents[$row['matric_number']] = $row['student'];
            }
        }
        if ($newStudents) {
            $this->bulkCreateStudents($newStudents);
            foreach ($newStudents as $matric => $s) {
                $existingStudents[$matric] = true;
            }
        }

        $skipped = [];
        $upsertRows = [];

        foreach ($chunk as $index => $row) {
            if (!isset($existingStudents[$row['matric_number']])) {
                $skipped[] = [
                    'index' => $index,
                    'matric_number' => $row['matric_number'],
                    'reason' => 'Student not found and no student data provided.',
                ];
                continue;
            }

            $upsertRows[] = [
                'matric_number' => $row['matric_number'],
                'session_id' => $session,
                'semester' => $semester,
                'course_code' => $row['course_code'],
                'unit_id' => $row['unit_id'] ?? $courseUnitMap[$row['course_code']] ?? '',
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
        }

        if ($upsertRows) {
            DB::table('registrations')->upsert(
                $upsertRows,
                ['matric_number', 'session_id', 'semester', 'course_code'],
                ['unit_id', 'ca', 'score', 'total_score', 'grade', 'remarks', 'status', 'lecturer_id', 'deleted', 'flag_waver']
            );
        }

        return [
            'upserted' => count($upsertRows),
            'skipped' => $skipped,
        ];
    }

    private function bulkEnsureCourses(array $chunk, array $courseCodes): void
    {
        $existing = Course::whereIn('course_code', $courseCodes)
            ->pluck('course_code')
            ->flip()
            ->all();

        $newCourses = [];
        $seen = [];
        foreach ($chunk as $row) {
            $code = $row['course_code'];
            if (!isset($existing[$code]) && !isset($seen[$code])) {
                $seen[$code] = true;
                $newCourses[] = [
                    'course_code' => $code,
                    'course_title' => $row['course_title'] ?? null,
                    'unit' => $row['unit'] ?? null,
                    'unit_id' => $row['unit_id'] ?? null,
                ];
            }
        }

        if ($newCourses) {
            foreach (array_chunk($newCourses, self::CHUNK_SIZE) as $batch) {
                DB::table('t_course')->insertOrIgnore($batch);
            }
        }
    }

    private function bulkCreateStudents(array $students): void
    {
        $rows = [];
        foreach ($students as $matric => $s) {
            $rows[] = [
                'matric_number' => $matric,
                'SURNAME' => strtoupper($s['surname'] ?? ''),
                'FIRSTNAME' => strtoupper($s['firstname'] ?? ''),
                'EMAIL1' => $s['email'] ?? null,
                'prog_code' => $s['prog_code'] ?? null,
                'sex' => $s['sex'] ?? null,
                'status' => 'active',
                'session_admitted' => $s['session_admitted'] ?? '',
            ];
        }

        foreach (array_chunk($rows, self::CHUNK_SIZE) as $batch) {
            DB::table('t_student_test')->insertOrIgnore($batch);
        }
    }

    private function ensureSettingExists(string $session, int $semester): Setting
    {
        return Setting::firstOrCreate(
            ['session' => $session, 'semester' => $semester],
            ['status' => '']
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

    public function updateMatricNumber(string $oldMatric, string $newMatric): array
    {
        $student = Student::where('matric_number', $oldMatric)->first();
        if (!$student) {
            return ['found' => false];
        }

        $existing = Student::where('matric_number', $newMatric)->first();
        if ($existing) {
            return ['found' => true, 'conflict' => true];
        }

        $tables = [
            'registrations' => 'matric_number',
            'applicants' => 'matric_number',
            'official_applications' => 'matric_number',
            'student_applications' => 'matric_number',
            'admin_applications' => 'matric_number',
            'payment_transaction' => 'matric_number',
        ];

        DB::beginTransaction();

        try {
            $affected = [];

            foreach ($tables as $table => $column) {
                $count = DB::table($table)
                    ->where($column, $oldMatric)
                    ->update([$column => $newMatric]);
                if ($count > 0) {
                    $affected[$table] = $count;
                }
            }

            DB::table('t_student_test')
                ->where('matric_number', $oldMatric)
                ->update(['matric_number' => $newMatric]);

            DB::commit();

            return [
                'found' => true,
                'conflict' => false,
                'old_matric' => $oldMatric,
                'new_matric' => $newMatric,
                'affected_tables' => $affected,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Matric number update failed', [
                'old' => $oldMatric,
                'new' => $newMatric,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function updateFlagWaver(array $updates): array
    {
        $updated = 0;
        $skipped = [];

        foreach (array_chunk($updates, self::CHUNK_SIZE) as $chunk) {
            foreach ($chunk as $row) {
                $matric = $row['matric_number'] ?? '';
                $sessionId = $row['session_id'] ?? '';
                $semester = $row['semester'] ?? null;
                $courseCode = $row['course_code'] ?? null;
                $flagValue = (bool) ($row['flag_waver'] ?? false);

                if (!$matric || !$sessionId) {
                    $skipped[] = [
                        'matric_number' => $matric,
                        'reason' => 'Missing matric_number or session_id',
                    ];
                    continue;
                }

                $query = DB::table('registrations')
                    ->where('matric_number', $matric)
                    ->where('session_id', $sessionId);

                if ($semester !== null && $semester !== '') {
                    $query->where('semester', $semester);
                }
                if ($courseCode !== null && $courseCode !== '') {
                    $query->where('course_code', $courseCode);
                }

                $count = $query->update(['flag_waver' => $flagValue]);

                if ($count > 0) {
                    $updated += $count;
                } else {
                    $skipped[] = [
                        'matric_number' => $matric,
                        'session_id' => $sessionId,
                        'reason' => 'No matching registrations found',
                    ];
                }
            }
        }

        return [
            'updated' => $updated,
            'skipped_count' => count($skipped),
            'skipped' => array_slice($skipped, 0, 50),
        ];
    }

    public function deleteResult(string $session, int $semester, string $matricNumber, string $courseCode): bool
    {
        return DB::table('registrations')
            ->where([
                'matric_number' => $matricNumber,
                'session_id' => $session,
                'semester' => $semester,
                'course_code' => $courseCode,
            ])
            ->update(['deleted' => 'Y']) > 0;
    }
}
