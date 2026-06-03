<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Applicant;
use Illuminate\Support\Facades\DB;

class TranscriptService
{
    protected StudentDataService $studentData;

    public function __construct(StudentDataService $studentData)
    {
        $this->studentData = $studentData;
    }

    public function generateTranscriptData(string $matricNumber, string $transcriptType, string $recipient = ''): array
    {
        $matno = str_replace(' ', '', $matricNumber);
        $sessions = $this->studentData->getStudentSessions($matno);

        if ($sessions->isEmpty()) {
            throw new \RuntimeException('No academic sessions found for this student.');
        }

        $student = Student::where('matric_number', $matno)->firstOrFail();
        $progDetails = $this->studentData->getProgrammeDetails($student->prog_code);

        $progName = $progDetails->programme ?? '';
        $dept = $progDetails->department ?? '';
        $fac = $progDetails->college ?? '';

        $cummSumPointUnit = 0.0;
        $cummSumUnit = 0.0;
        $pageNo = 0;
        $cgpa = 0.0;
        $date = date('F j, Y, g:i a');

        $response = '';

        foreach ($sessions as $sessionIndex => $session) {
            $pageNo++;
            $response .= $this->buildTableHeader($student, $matno, $transcriptType, $recipient, $progName, $dept, $fac, $pageNo);
            $results = $this->fetchResultsForSession($matno, $session);

            $semester = 0;
            $sumPointUnit = 0.0;
            $sumUnit = 0.0;

            foreach ($results as $result) {
                if ($semester !== $result->semester && $semester === 0) {
                    $response .= $this->openSemesterTable($session, $result->semester);
                }

                if ($semester !== $result->semester && $semester !== 0) {
                    $cummSumPointUnit += $sumPointUnit;
                    $cummSumUnit += $sumUnit;
                    $gpa = $sumUnit > 0 ? $sumPointUnit / $sumUnit : 0;
                    $cgpa = $cummSumUnit > 0 ? $cummSumPointUnit / $cummSumUnit : 0;

                    $response .= $this->closeSemesterTable($sumUnit, $sumPointUnit, $gpa, $cummSumUnit, $cummSumPointUnit, $cgpa);
                    $response .= $this->openSemesterTable($session, $result->semester);

                    $sumPointUnit = 0.0;
                    $sumUnit = 0.0;
                }

                $gradePoint = $this->gradeToPoints(strtoupper($result->grade)) * $result->unit;

                $response .= '<tr>
                    <td>' . $result->course_code . '</td>
                    <td>' . $result->course_title . '</td>
                    <td>' . $this->formatStatus($result->status) . '</td>
                    <td align="center">' . $result->unit . '</td>
                    <td align="center">' . $result->total_score . '</td>
                    <td align="center">' . $result->grade . '</td>
                    <td align="center">' . $gradePoint . '</td>
                </tr>';

                if (!isset($result->flag_waver) || $result->flag_waver != 1) {
                    $sumUnit += $result->unit;
                    $sumPointUnit += $gradePoint;
                }

                $semester = $result->semester;
            }

            $cummSumPointUnit += $sumPointUnit;
            $cummSumUnit += $sumUnit;
            $gpa = $sumUnit > 0 ? $sumPointUnit / $sumUnit : 0;
            $cgpa = $cummSumUnit > 0 ? $cummSumPointUnit / $cummSumUnit : 0;

            $response .= $this->closeSemesterTable($sumUnit, $sumPointUnit, $gpa, $cummSumUnit, $cummSumPointUnit, $cgpa);

            $isLastSession = $sessionIndex === $sessions->count() - 1;
            if ($isLastSession) {
                $qualification = $this->getQualification($student, $progName, $fac);
                $response .= $this->buildAcademicSummary($student, $qualification, $cgpa, $progName, $transcriptType, $date);
            } else {
                $response .= '<div class="footer_4">Any alteration renders this transcript invalid<br>Generated on ' . $date . '<br></div></div>';
            }
        }

        $response = str_replace('pageno', $pageNo, $response);

        return [
            'first_session_in_sch' => $sessions->first(),
            'last_session_in_sch' => $sessions->last(),
            'years_spent' => $sessions->count(),
            'qualification' => $this->getQualification($student, $progName, $fac),
            'prog_name' => $progName,
            'dept' => $this->studentData->findAndReplaceString2($dept),
            'fac' => $this->studentData->findAndReplaceString2($fac),
            'cgpa' => number_format($cgpa, 2, '.', ''),
            'class_of_degree' => $this->classOfDegree($cgpa, $progName),
            'result' => $response,
        ];
    }

    public function fetchResultsForSession(string $matno, string $session)
    {
        return DB::table('t_course')
            ->join('registrations', 't_course.unit_id', '=', 'registrations.unit_id')
            ->select(
                'registrations.session_id', 'registrations.semester',
                'registrations.course_code', 'registrations.status',
                'registrations.ca', 'registrations.score',
                'registrations.total_score', 'registrations.grade',
                'registrations.flag_waver',
                't_course.course_title', 't_course.unit'
            )
            ->where(DB::raw("CONCAT(registrations.course_code,registrations.unit_id)"), DB::raw("CONCAT(t_course.course_code,t_course.unit_id)"))
            ->where('registrations.session_id', $session)
            ->where('registrations.matric_number', $matno)
            ->where('registrations.deleted', 'N')
            ->orderBy('registrations.session_id', 'ASC')
            ->orderBy('registrations.semester', 'ASC')
            ->orderBy('registrations.course_code', 'ASC')
            ->get();
    }

    public function gradeToPoints(string $grade): int
    {
        return (int) strpos('FEDCBA', $grade);
    }

    public function formatSemester(int $semester): string
    {
        return match ($semester) {
            1 => 'First',
            2 => 'Second',
            default => '',
        };
    }

    public function formatStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'C' => 'Compulsory',
            'E' => 'Elective',
            default => '',
        };
    }

    public function classOfDegree(float $cgpa, string $progName): string
    {
        $cgpa = (float) number_format($cgpa, 2, '.', '');
        $specialProgrammes = ['NURSING SCIENCE', 'PHYSIOTHERAPY', 'MEDICAL LABORATORY SCIENCE'];

        if (in_array(strtoupper($progName), $specialProgrammes)) {
            if ($cgpa >= 4.5) return 'Pass With Distinction';
            if ($cgpa >= 3.5) return 'Pass With Credit';
            if ($cgpa >= 2.5) return 'Pass';
            return 'Out of range';
        }

        if ($cgpa >= 4.5) return 'First Class (Honours)';
        if ($cgpa >= 3.5) return 'Second Class (Honours) Upper Division';
        if ($cgpa >= 2.4) return 'Second Class (Honours) Lower Division';
        if ($cgpa >= 1.5) return 'Third Class (Honours)';
        if ($cgpa >= 1.0) return 'Pass';
        return '';
    }

    public function getQualification(Student $student, string $progName, string $fac): string
    {
        if (strtoupper($student->status ?? '') !== 'GRADUATED') {
            return '';
        }

        $facUpper = strtoupper($fac);
        $progUpper = strtoupper($progName);
        $progFormatted = $this->studentData->findAndReplaceString($progName);

        if (str_ends_with($facUpper, 'SCIENCES') && $progUpper === 'NURSING SCIENCE') {
            return 'Bachelor of Nursing Science';
        }
        if (str_ends_with($facUpper, 'SCIENCES') && $progUpper === 'PHYSIOTHERAPY') {
            return 'Bachelor of Physiotherapy';
        }
        if ($progUpper === 'ARCHITECTURE') {
            return "Bachelor of Science in {$progFormatted}";
        }
        if (str_ends_with($facUpper, 'SCIENCES')) {
            return "Bachelor of Science in {$progFormatted}";
        }
        if (str_contains($facUpper, 'LAW')) {
            return 'Bachelor of Laws';
        }
        if (str_contains($facUpper, 'ENGINEERING')) {
            return "Bachelor of Engineering in {$progFormatted}";
        }

        return "Bachelor of Arts in {$progFormatted}";
    }

    protected function buildTableHeader(Student $student, string $matno, string $type, string $recipient, string $progName, string $dept, string $fac, int $pageNo): string
    {
        $transType = strtoupper($type) === 'OFFICIAL' ? 'Official Transcript' : "Student's Proof of Result";
        $recipientName = strtoupper($type) === 'OFFICIAL' ? $recipient : ($student->SURNAME . ' ' . $student->FIRSTNAME);

        return '<div class="page">
            <div class="header">
                <img src="' . public_path('assets/images/run_logo_big.png') . '" class="logo"/>
                <h1>REDEEMER\'S UNIVERSITY</h1>
                <h5>P.M.B. 230, Ede, Osun State, Nigeria</h5>
                <h5>Tel: +234 902 859 5221, Website: run.edu.ng, Email: transcripts@run.edu.ng</h5><br>
                <h2>' . $transType . '</h2>
                <h5 id="recipient_h">Intended Recipient: ' . $recipientName . '</h5>
                <h6>Page ' . $pageNo . ' of pageno</h6>
            </div>
            <div class="golden_streak"></div>
            <div class="header2">
                <table>
                    <tr>
                        <td>Name: <strong>' . $student->SURNAME . ' ' . $student->FIRSTNAME . '</strong></td>
                        <td></td>
                        <td>Matriculation Number: <strong>' . $matno . '</strong></td>
                    </tr>
                    <tr>
                        <td>Faculty: <strong>' . $fac . '</strong></td>
                        <td>Department: <strong>' . $dept . '</strong></td>
                        <td>Programme: <strong>' . $progName . '</strong></td>
                    </tr>
                </table>
            </div>';
    }

    protected function openSemesterTable(string $session, int $semester): string
    {
        return '<table class="result_table">
            <caption>Session: ' . $session . ', Semester: ' . $this->formatSemester($semester) . '</caption>
            <tr>
                <th>Course Code</th>
                <th>Course Title</th>
                <th>Status</th>
                <th>Unit</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Grade Point</th>
            </tr>';
    }

    protected function closeSemesterTable(float $sumUnit, float $sumPointUnit, float $gpa, float $cummUnit, float $cummPointUnit, float $cgpa): string
    {
        return '</table>
            <table class="result_table2">
                <tr>
                    <td><strong>Semester</strong></td>
                    <td>TU: <strong>' . $sumUnit . '</strong></td>
                    <td>TGP: <strong>' . $sumPointUnit . '</strong></td>
                    <td>GPA: <strong>' . number_format($gpa, 2, '.', '') . '</strong></td>
                </tr>
                <tr>
                    <td><strong>Cumulative</strong></td>
                    <td>CTU: <strong>' . $cummUnit . '</strong></td>
                    <td>CTGP: <strong>' . $cummPointUnit . '</strong></td>
                    <td>CGPA: <strong>' . number_format($cgpa, 2, '.', '') . '</strong></td>
                </tr>
            </table>';
    }

    protected function buildAcademicSummary(Student $student, string $qualification, float $cgpa, string $progName, string $type, string $date): string
    {
        $response = '<br><hr style="border-top: 1px dotted black;">
            <table class="result_table2">
                <caption>Overall Academic Summary</caption>
                <tr>
                    <td><strong>Status</strong></td>
                    <td>' . ($student->status ?? $student->STATUS ?? '') . '</td>
                </tr>
                <tr>
                    <td><strong>Qualification Obtained</strong></td>
                    <td>' . $qualification . '</td>
                </tr>';

        if (strtoupper($student->status ?? $student->STATUS ?? '') === 'GRADUATED') {
            $response .= '<tr>
                <td><strong>Class of Degree</strong></td>
                <td>' . $this->classOfDegree($cgpa, $progName) . '</td>
            </tr>';
        }

        $response .= '</table>
            <table class="result_table2">
                <caption>Key</caption>
                <tr><td>A => 100 - 70 => 5</td><td>4.50 - 5.00 => Excellent</td><td>TU: Total Units</td></tr>
                <tr><td>B => 69 - 60 => 4</td><td>3.50 - 4.49 => Very Good</td><td>TGP: Total Grade Point</td></tr>
                <tr><td>C => 59 - 50 => 3</td><td>2.50 - 3.49 => Good</td><td>GPA: Grade Point Average</td></tr>
                <tr><td>D => 49 - 45 => 2</td><td>1.50 - 2.49 => Average</td><td>CTU: Cumulative Total Units</td></tr>
                <tr><td>E => 44 - 40 => 1</td><td>1.00 - 1.49 => Fair</td><td>CTGP: Cumulative Total Grade Point</td></tr>
                <tr><td>F => 39 - 0 => 0</td><td>0.00 - 0.99 => Poor</td><td>CGPA: Cumulative Grade Point Average</td></tr>
            </table>';

        if (strtoupper($type) === 'OFFICIAL') {
            $response .= '<div class="footer_">
                ________________________________<br>
                S. A. Ogunlade<br>
                Deputy Registrar, Academic Affairs Division<br>
                For: Registrar
            </div>';
        }

        $response .= '<div class="footer_4">
            Any alteration renders this transcript invalid<br>
            Generated on ' . $date . '<br>
        </div></div>';

        return $response;
    }
}
