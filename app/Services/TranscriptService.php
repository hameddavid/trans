<?php
namespace App\Services;

class TranscriptService
{
    public function generateAdminTranscript($request)
    {
        $matno = str_replace(' ', '', $request->matno);

        $student = Student::where('matric_number', $matno)->firstOrFail();

        $sessions = $this->getSessions($matno);

        $programme = $this->getProgramme($matno);

        $results = $this->getResultsBySession($matno, $sessions);

        $summary = $this->calculateSummary($results);

        return [
            'student' => $student,
            'sessions' => $sessions,
            'programme' => $programme,
            'summary' => $summary,
            'cgpa' => $summary['cgpa']
        ];
    }




    private function calculateGpa($results)
{
    $units = 0;
    $points = 0;

    foreach ($results as $result) {
        $units += $result->unit;
        $points += $this->gradePoint($result->grade) * $result->unit;
    }

    return $units > 0 ? $points / $units : 0;
}
}