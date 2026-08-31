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
    private function monthExpression(): string
    {
        $driver = DB::getDriverName();
        return $driver === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/dashboard",
     *     operationId="adminDashboard",
     *     tags={"Admin Dashboard"},
     *     summary="Get dashboard statistics including services, revenue, charts, and recent activities",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Dashboard data",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="services", type="object",
     *                 @OA\Property(property="officialTranscript", type="object",
     *                     @OA\Property(property="label", type="string"),
     *                     @OA\Property(property="total", type="integer"),
     *                     @OA\Property(property="pending", type="integer"),
     *                     @OA\Property(property="recommended", type="integer"),
     *                     @OA\Property(property="approved", type="integer"),
     *                     @OA\Property(property="failed", type="integer")
     *                 ),
     *                 @OA\Property(property="studentCopy", type="object"),
     *                 @OA\Property(property="proficiency", type="object"),
     *                 @OA\Property(property="degreeVerification", type="object"),
     *                 @OA\Property(property="adminGenerated", type="object")
     *             ),
     *             @OA\Property(property="revenue", type="object",
     *                 @OA\Property(property="transcript", type="string"),
     *                 @OA\Property(property="degree", type="string"),
     *                 @OA\Property(property="total", type="string")
     *             ),
     *             @OA\Property(property="charts", type="object",
     *                 @OA\Property(property="monthlyLabels", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="monthlySeries", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="statusDistribution", type="object")
     *             ),
     *             @OA\Property(property="recentActivities", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $monthExpr = $this->monthExpression();

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

        $monthlyOfficial = OfficialApplication::selectRaw("{$monthExpr} as m, COUNT(*) as c")
            ->groupByRaw("{$monthExpr}")->pluck('c', 'm');
        $monthlyStudent = StudentApplication::selectRaw("{$monthExpr} as m, COUNT(*) as c")
            ->groupByRaw("{$monthExpr}")->pluck('c', 'm');
        $monthlyDegree = DegreeVerification::selectRaw("{$monthExpr} as m, COUNT(*) as c")
            ->groupByRaw("{$monthExpr}")->pluck('c', 'm');
        $monthlyAdmin = AdminApplication::selectRaw("{$monthExpr} as m, COUNT(*) as c")
            ->groupByRaw("{$monthExpr}")->pluck('c', 'm');

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

    /**
     * @OA\Get(
     *     path="/api/v1/admin/transcript-activities",
     *     operationId="adminTranscriptActivities",
     *     tags={"Admin Dashboard"},
     *     summary="Get monthly transcript activity counts (array of 12 integers)",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Monthly activity counts",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(type="integer"),
     *             example={0, 5, 12, 8, 15, 20, 18, 22, 10, 7, 3, 1}
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function transcriptActivities()
    {
        $monthExpr = $this->monthExpression();

        $monthly = DB::table('official_applications')
            ->selectRaw("{$monthExpr} as m, COUNT(*) as c")
            ->groupByRaw("{$monthExpr}")
            ->pluck('c', 'm');

        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $monthly[$i] ?? 0;
        }
        return response()->json($data);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/transcript-locations",
     *     operationId="adminTranscriptLocations",
     *     tags={"Admin Dashboard"},
     *     summary="Get transcript destination counts",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Destination counts",
     *         @OA\JsonContent(type="array",
     *             @OA\Items(type="object",
     *                 @OA\Property(property="destination", type="string", example="USA"),
     *                 @OA\Property(property="number", type="integer", example=42)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
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
