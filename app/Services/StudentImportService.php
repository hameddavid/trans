<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentImportService
{
    private const CHUNK_SIZE = 1000;

    /**
     * Batch upsert students — create new ones, update existing ones (match on matric_number).
     */
    public function importStudents(array $students): array
    {
        $created = 0;
        $updated = 0;
        $skipped = [];

        foreach (array_chunk($students, self::CHUNK_SIZE, true) as $chunk) {
            $chunkResult = $this->processImportChunk($chunk);
            $created += $chunkResult['created'];
            $updated += $chunkResult['updated'];
            array_push($skipped, ...$chunkResult['skipped']);
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'total_processed' => $created + $updated,
            'skipped_count' => count($skipped),
            'skipped' => array_slice($skipped, 0, 100),
        ];
    }

    private function processImportChunk(array $chunk): array
    {
        $matricNumbers = array_unique(array_column($chunk, 'matric_number'));

        $existingStudents = Student::whereIn('matric_number', $matricNumbers)
            ->pluck('matric_number')
            ->flip()
            ->all();

        $skipped = [];
        $upsertRows = [];

        foreach ($chunk as $index => $row) {
            if (empty($row['matric_number'])) {
                $skipped[] = [
                    'index' => $index,
                    'matric_number' => $row['matric_number'] ?? '',
                    'reason' => 'Missing matric_number.',
                ];
                continue;
            }

            $upsertRows[] = [
                'matric_number' => $row['matric_number'],
                'SURNAME' => strtoupper($row['surname'] ?? ''),
                'FIRSTNAME' => strtoupper($row['firstname'] ?? ''),
                'EMAIL1' => $row['email'] ?? $row['email1'] ?? null,
                'prog_code' => $row['prog_code'] ?? null,
                'sex' => $row['sex'] ?? '',
                'status' => $row['status'] ?? 'active',
                'session_admitted' => $row['session_admitted'] ?? '',
                'CURRENT_LEVEL' => $row['current_level'] ?? null,
            ];
        }

        $created = 0;
        $updated = 0;

        if ($upsertRows) {
            // Count existing before upsert to determine created vs updated
            $existingCount = count(array_filter($upsertRows, function ($row) use ($existingStudents) {
                return isset($existingStudents[$row['matric_number']]);
            }));

            DB::table('t_student_test')->upsert(
                $upsertRows,
                ['matric_number'],
                ['SURNAME', 'FIRSTNAME', 'EMAIL1', 'prog_code', 'sex', 'status', 'session_admitted', 'CURRENT_LEVEL']
            );

            $updated = $existingCount;
            $created = count($upsertRows) - $existingCount;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * Batch update students with new level and academic status.
     */
    public function promoteStudents(array $promotions): array
    {
        $promoted = 0;
        $notFound = [];

        foreach (array_chunk($promotions, self::CHUNK_SIZE, true) as $chunk) {
            $chunkResult = $this->processPromoteChunk($chunk);
            $promoted += $chunkResult['promoted'];
            array_push($notFound, ...$chunkResult['not_found']);
        }

        return [
            'promoted' => $promoted,
            'not_found_count' => count($notFound),
            'not_found' => array_slice($notFound, 0, 100),
        ];
    }

    private function processPromoteChunk(array $chunk): array
    {
        $matricNumbers = array_unique(array_column($chunk, 'matric_number'));

        $existingStudents = Student::whereIn('matric_number', $matricNumbers)
            ->pluck('matric_number')
            ->flip()
            ->all();

        $promoted = 0;
        $notFound = [];

        DB::beginTransaction();

        try {
            foreach ($chunk as $index => $row) {
                if (!isset($existingStudents[$row['matric_number']])) {
                    $notFound[] = [
                        'index' => $index,
                        'matric_number' => $row['matric_number'],
                        'reason' => 'Student not found.',
                    ];
                    continue;
                }

                $updateData = [
                    'CURRENT_LEVEL' => $row['new_level'],
                ];

                if (!empty($row['session'])) {
                    $updateData['session_admitted'] = $row['session'];
                }

                DB::table('t_student_test')
                    ->where('matric_number', $row['matric_number'])
                    ->update($updateData);

                $promoted++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Student promotion failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        return [
            'promoted' => $promoted,
            'not_found' => $notFound,
        ];
    }
}
