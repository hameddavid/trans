<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficialApplication;
use App\Models\StudentApplication;
use App\Models\AdminApplication;
use App\Models\DegreeVerification;
use App\Models\Payment;
use App\Models\Payment4Degree;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $officialStats = OfficialApplication::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN app_status = 'PENDING' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN app_status = 'RECOMMENDED' THEN 1 ELSE 0 END) as recommended,
            SUM(CASE WHEN app_status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN app_status = 'FAILED' THEN 1 ELSE 0 END) as failed
        ")->first();

        $studentStats = StudentApplication::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN transcript_type = 'STUDENT' THEN 1 ELSE 0 END) as student_copy,
            SUM(CASE WHEN transcript_type = 'PROFICIENCY' THEN 1 ELSE 0 END) as proficiency,
            SUM(CASE WHEN app_status = 'PENDING' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN app_status = 'RECOMMENDED' THEN 1 ELSE 0 END) as recommended,
            SUM(CASE WHEN app_status = 'APPROVED' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN app_status = 'FAILED' THEN 1 ELSE 0 END) as failed
        ")->first();

        $degreeStats = DegreeVerification::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'RECOMMENDED' THEN 1 ELSE 0 END) as recommended,
            SUM(CASE WHEN status = 'APPROVED' THEN 1 ELSE 0 END) as approved
        ")->first();

        $adminStats = AdminApplication::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN transcript_type = 'OFFICIAL' THEN 1 ELSE 0 END) as official,
            SUM(CASE WHEN transcript_type = 'STUDENT' THEN 1 ELSE 0 END) as student_copy
        ")->first();

        $transcriptPayments = Payment::where('status_msg', 'success')->sum('amount');
        $degreePayments = Payment4Degree::where('status_msg', 'success')->sum('amount');

        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $monthlyOfficial = OfficialApplication::selectRaw('MONTH(created_at) as m, COUNT(*) as c')
            ->groupByRaw('MONTH(created_at)')->pluck('c', 'm');
        $monthlyStudent = StudentApplication::selectRaw('MONTH(created_at) as m, COUNT(*) as c')
            ->groupByRaw('MONTH(created_at)')->pluck('c', 'm');
        $monthlyDegree = DegreeVerification::selectRaw('MONTH(created_at) as m, COUNT(*) as c')
            ->groupByRaw('MONTH(created_at)')->pluck('c', 'm');
        $monthlyAdmin = AdminApplication::selectRaw('MONTH(created_at) as m, COUNT(*) as c')
            ->groupByRaw('MONTH(created_at)')->pluck('c', 'm');

        $monthlyOfficialData = [];
        $monthlyStudentData = [];
        $monthlyDegreeData = [];
        $monthlyAdminData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyOfficialData[] = $monthlyOfficial[$i] ?? 0;
            $monthlyStudentData[] = $monthlyStudent[$i] ?? 0;
            $monthlyDegreeData[] = $monthlyDegree[$i] ?? 0;
            $monthlyAdminData[] = $monthlyAdmin[$i] ?? 0;
        }

        $recentApps = OfficialApplication::with('applicant')
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->map(fn ($app) => [
                'id' => $app->application_id,
                'date' => $app->updated_at?->format('m/d/Y'),
                'student' => $app->applicant ? trim($app->applicant->surname . ' ' . $app->applicant->firstname) : $app->matric_number,
                'type' => 'Official Transcript',
                'action' => $app->app_status,
                'admin' => $app->approved_by ?? $app->recommended_by ?? '-',
            ]);

        return response()->json([
            'services' => [
                'officialTranscript' => [
                    'label' => 'Official Transcript',
                    'total' => (int) ($officialStats->total ?? 0),
                    'pending' => (int) ($officialStats->pending ?? 0),
                    'recommended' => (int) ($officialStats->recommended ?? 0),
                    'approved' => (int) ($officialStats->approved ?? 0),
                    'failed' => (int) ($officialStats->failed ?? 0),
                ],
                'studentCopy' => [
                    'label' => "Student's Copy",
                    'total' => (int) ($studentStats->student_copy ?? 0),
                    'pending' => (int) ($studentStats->pending ?? 0),
                    'recommended' => (int) ($studentStats->recommended ?? 0),
                    'approved' => (int) ($studentStats->approved ?? 0),
                    'failed' => (int) ($studentStats->failed ?? 0),
                ],
                'proficiency' => [
                    'label' => 'Proficiency in English',
                    'total' => (int) ($studentStats->proficiency ?? 0),
                ],
                'degreeVerification' => [
                    'label' => 'Degree Verification',
                    'total' => (int) ($degreeStats->total ?? 0),
                    'pending' => (int) ($degreeStats->pending ?? 0),
                    'recommended' => (int) ($degreeStats->recommended ?? 0),
                    'approved' => (int) ($degreeStats->approved ?? 0),
                ],
                'adminGenerated' => [
                    'label' => 'Admin Generated',
                    'total' => (int) ($adminStats->total ?? 0),
                    'official' => (int) ($adminStats->official ?? 0),
                    'studentCopy' => (int) ($adminStats->student_copy ?? 0),
                ],
            ],
            'revenue' => [
                'transcript' => number_format($transcriptPayments, 0),
                'degree' => number_format($degreePayments, 0),
                'total' => number_format($transcriptPayments + $degreePayments, 0),
            ],
            'charts' => [
                'monthlyLabels' => $monthlyLabels,
                'monthlySeries' => [
                    ['name' => 'Official Transcript', 'data' => $monthlyOfficialData],
                    ['name' => "Student's Copy / Proficiency", 'data' => $monthlyStudentData],
                    ['name' => 'Degree Verification', 'data' => $monthlyDegreeData],
                    ['name' => 'Admin Generated', 'data' => $monthlyAdminData],
                ],
                'statusDistribution' => [
                    'labels' => ['Pending', 'Recommended', 'Approved', 'Failed'],
                    'series' => [
                        (int) (($officialStats->pending ?? 0) + ($studentStats->pending ?? 0)),
                        (int) (($officialStats->recommended ?? 0) + ($studentStats->recommended ?? 0)),
                        (int) (($officialStats->approved ?? 0) + ($studentStats->approved ?? 0)),
                        (int) (($officialStats->failed ?? 0) + ($studentStats->failed ?? 0)),
                    ],
                ],
            ],
            'recentActivities' => $recentApps,
        ]);
    }

    public function transcriptActivities()
    {
        $monthly = DB::table('official_applications')
            ->selectRaw('MONTH(created_at) as m, COUNT(*) as c')
            ->groupByRaw('MONTH(created_at)')
            ->pluck('c', 'm');

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $monthly[$i] ?? 0;
        }
        return response()->json($data);
    }

    public function transcriptLocations()
    {
        $locations = DB::table('official_applications')
            ->select('destination', DB::raw('COUNT(destination) as number'))
            ->groupBy('destination')
            ->orderByRaw('COUNT(destination) DESC')
            ->get();
        return response()->json($locations);
    }
}
