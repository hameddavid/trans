<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Applicant;
use Illuminate\Support\Facades\DB;

class TranscriptService
{
    protected StudentDataService $studentData;
    protected SignatoryService $signatoryService;

    public function __construct(StudentDataService $studentData, SignatoryService $signatoryService)
    {
        $this->studentData = $studentData;
        $this->signatoryService = $signatoryService;
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
                $response .= '<div class="footer_4">Any alteration renders this transcript invalid<br>Generated on ' . $date . '</div></div>';
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

        if (str_ends_with($facUpper, 'SCIENCE') || str_ends_with($facUpper, 'SCIENCES')) {
            if ($progUpper === 'NURSING SCIENCE') {
                return 'Bachelor of Nursing Science';
            }
            if ($progUpper === 'PHYSIOTHERAPY') {
                return 'Bachelor of Physiotherapy';
            }
            return "Bachelor of Science in {$progFormatted}";
        }
        if ($progUpper === 'ARCHITECTURE') {
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
        $logoPath = public_path('assets/images/run_logo_big.png');

        return '<div class="page">
            <div class="header">
                <img src="' . $logoPath . '" class="logo"/>
                <h1>REDEEMER\'S UNIVERSITY</h1>
                <h5>P.M.B. 230, Ede, Osun State, Nigeria</h5>
                <h5>Tel: +234 902 859 5221 &nbsp;&middot;&nbsp; Website: run.edu.ng &nbsp;&middot;&nbsp; Email: transcripts@run.edu.ng</h5>
                <h2>' . $transType . '</h2>
                <p id="recipient_h">Intended Recipient: ' . e($recipientName) . '</p>
                <h6>Page ' . $pageNo . ' of pageno</h6>
            </div>
            <div class="gold-accent"></div>
            <div class="header2">
                <table>
                    <tr>
                        <td>Name: <strong>' . e($student->SURNAME . ' ' . $student->FIRSTNAME) . '</strong></td>
                        <td></td>
                        <td>Matriculation Number: <strong>' . e($matno) . '</strong></td>
                    </tr>
                    <tr>
                        <td>Faculty: <strong>' . e($fac) . '</strong></td>
                        <td>Department: <strong>' . e($dept) . '</strong></td>
                        <td>Programme: <strong>' . e($progName) . '</strong></td>
                    </tr>
                </table>
            </div>';
    }

    protected function openSemesterTable(string $session, int $semester): string
    {
        return '<table class="result_table">
            <tr><td colspan="7" class="semester-label">' . e($session) . ' &mdash; ' . $this->formatSemester($semester) . ' Semester</td></tr>
            <tr>
                <th>Course Code</th>
                <th>Course Title</th>
                <th>Status</th>
                <th align="center">Unit</th>
                <th align="center">Score</th>
                <th align="center">Grade</th>
                <th align="center">Grade Pt.</th>
            </tr>';
    }

    protected function closeSemesterTable(float $sumUnit, float $sumPointUnit, float $gpa, float $cummUnit, float $cummPointUnit, float $cgpa): string
    {
        return '</table>
            <table class="result_table2">
                <tr>
                    <td style="width:25%;"><strong>Semester</strong></td>
                    <td style="width:25%;">TU: <strong>' . number_format($sumUnit, 0) . '</strong></td>
                    <td style="width:25%;">TGP: <strong>' . number_format($sumPointUnit, 0) . '</strong></td>
                    <td style="width:25%;">GPA: <strong>' . number_format($gpa, 2, '.', '') . '</strong></td>
                </tr>
                <tr>
                    <td><strong>Cumulative</strong></td>
                    <td>CTU: <strong>' . number_format($cummUnit, 0) . '</strong></td>
                    <td>CTGP: <strong>' . number_format($cummPointUnit, 0) . '</strong></td>
                    <td>CGPA: <strong>' . number_format($cgpa, 2, '.', '') . '</strong></td>
                </tr>
            </table>';
    }

    protected function buildAcademicSummary(Student $student, string $qualification, float $cgpa, string $progName, string $type, string $date): string
    {
        $response = '<div class="summary-divider"></div>
            <table class="result_table2">
                <caption>Overall Academic Summary</caption>
                <tr>
                    <td style="width:40%;"><strong>Status</strong></td>
                    <td>' . e($student->status ?? $student->STATUS ?? '') . '</td>
                </tr>
                <tr>
                    <td><strong>Qualification Obtained</strong></td>
                    <td>' . e($qualification) . '</td>
                </tr>';

        if (strtoupper($student->status ?? $student->STATUS ?? '') === 'GRADUATED') {
            $response .= '<tr>
                <td><strong>Class of Degree</strong></td>
                <td>' . e($this->classOfDegree($cgpa, $progName)) . '</td>
            </tr>';
        }

        $response .= '</table>
            <table class="result_table2">
                <caption>Grading Key</caption>
                <tr><td style="width:33%;">A =&gt; 70 &ndash; 100 =&gt; 5</td><td style="width:33%;">4.50 &ndash; 5.00: Excellent</td><td style="width:34%;">TU: Total Units</td></tr>
                <tr><td>B =&gt; 60 &ndash; 69 =&gt; 4</td><td>3.50 &ndash; 4.49: Very Good</td><td>TGP: Total Grade Point</td></tr>
                <tr><td>C =&gt; 50 &ndash; 59 =&gt; 3</td><td>2.50 &ndash; 3.49: Good</td><td>GPA: Grade Point Average</td></tr>
                <tr><td>D =&gt; 45 &ndash; 49 =&gt; 2</td><td>1.50 &ndash; 2.49: Average</td><td>CTU: Cumulative Total Units</td></tr>
                <tr><td>E =&gt; 40 &ndash; 44 =&gt; 1</td><td>1.00 &ndash; 1.49: Fair</td><td>CTGP: Cumulative Total Grade Pt.</td></tr>
                <tr><td>F =&gt; 0 &ndash; 39 =&gt; 0</td><td>0.00 &ndash; 0.99: Poor</td><td>CGPA: Cumulative GPA</td></tr>
            </table>';

        if (strtoupper($type) === 'OFFICIAL') {
            $sig = $this->signatoryService->getSignatory('transcript');

            $response .= '<div class="footer_">';
            if (!empty($sig['signature_path'])) {
                $response .= '<img src="' . e($sig['signature_path']) . '" style="height: 45px; margin-bottom: 4px;"><br>';
            } else {
                $response .= '________________________________<br>';
            }
            $response .= e($sig['name']) . '<br>'
                . e($sig['title']) . '<br>'
                . 'For: ' . e($sig['for'])
                . '</div>';
        }

        $response .= '<div class="footer_4">
            Any alteration renders this transcript invalid<br>
            Generated on ' . $date . '
        </div></div>';

        return $response;
    }
}
