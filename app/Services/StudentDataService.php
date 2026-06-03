<?php

namespace App\Services;

use App\Models\Student;
use App\Models\CollegeDept;
use App\Models\RegistrationResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentDataService
{
    public function findStudent(string $matricNumber): ?Student
    {
        return Student::where('matric_number', $matricNumber)->first();
    }

    public function getAvailableProgrammes(): Collection
    {
        return CollegeDept::select('programme', 'department', 'college')->get();
    }

    public function listProgrammes(): Collection
    {
        return CollegeDept::select('prog_code', 'programme', 'department', 'college')
            ->orderBy('programme')
            ->get();
    }

    public function verifyStudentStatus(string $matricNumber): bool
    {
        $blockedStatuses = ['DIED', 'EXPELLED', 'SUSPENDED', 'SUSPENSION', 'WITHDREW'];
        $student = Student::where('matric_number', $matricNumber)->first();

        if (!$student) return false;

        return !in_array(strtoupper($student->STATUS ?? $student->status), $blockedStatuses);
    }

    public function verifyStudentHasResults(string $matricNumber): bool
    {
        return RegistrationResult::where(['matric_number' => $matricNumber, 'deleted' => 'N'])->exists();
    }

    public function getStudentSessions(string $matricNumber): Collection
    {
        return DB::table('registrations')
            ->distinct()
            ->where(['matric_number' => $matricNumber, 'deleted' => 'N'])
            ->orderBy('session_id', 'ASC')
            ->pluck('session_id');
    }

    public function getProgrammeDetails(string $progCode): ?object
    {
        return CollegeDept::where('prog_code', $progCode)->first();
    }

    public function findAndReplaceString(string $value): string
    {
        return str_replace(
            ['AND', 'OF', 'IN', 'THE'],
            ['and', 'of', 'in', 'the'],
            ucwords(strtolower($value))
        );
    }

    public function findAndReplaceString2(string $value): string
    {
        return ucwords(strtolower($value));
    }
}
