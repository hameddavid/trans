<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TreatDegreeRequest;
use App\Http\Resources\DegreeVerificationResource;
use App\Models\DegreeVerification;
use App\Services\DegreeVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class DegreeVerificationController extends Controller
{
    public function __construct(protected DegreeVerificationService $degreeService) {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/degree-verification/pending",
     *     operationId="adminPendingDegreeVerifications",
     *     tags={"Admin Degree Verification"},
     *     summary="List pending/treated degree verifications (paginated)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated list of pending degree verifications",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function pending(Request $request)
    {
        $apps = DegreeVerification::whereIn('status', ['PENDING', 'TREATED'])->latest()->paginate($request->integer('per_page', 15));
        return DegreeVerificationResource::collection($apps);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/degree-verification/recommended",
     *     operationId="adminRecommendedDegreeVerifications",
     *     tags={"Admin Degree Verification"},
     *     summary="List recommended degree verifications (paginated)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated list of recommended degree verifications",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function recommended(Request $request)
    {
        $apps = DegreeVerification::where('status', 'RECOMMENDED')->latest()->paginate($request->integer('per_page', 15));
        return DegreeVerificationResource::collection($apps);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/degree-verification/approved",
     *     operationId="adminApprovedDegreeVerifications",
     *     tags={"Admin Degree Verification"},
     *     summary="List approved degree verifications (paginated)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated list of approved degree verifications",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function approved(Request $request)
    {
        $apps = DegreeVerification::where('status', 'APPROVED')->latest()->paginate($request->integer('per_page', 15));
        return DegreeVerificationResource::collection($apps);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/degree-verification/treat",
     *     operationId="adminTreatDegreeVerification",
     *     tags={"Admin Degree Verification"},
     *     summary="Treat a degree verification request",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"userid","matno"},
     *             @OA\Property(property="userid", type="integer", example=1),
     *             @OA\Property(property="matno", type="string", example="MAT/2020/001")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Degree verification treated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Degree verification treated.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function treat(TreatDegreeRequest $request)
    {
        $this->degreeService->treatVerification($request->user(), $request->userid, $request->matno);
        return response()->json(['status' => 'success', 'message' => 'Degree verification treated.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/degree-verification/recommend",
     *     operationId="adminRecommendDegreeVerification",
     *     tags={"Admin Degree Verification"},
     *     summary="Recommend a degree verification",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Degree recommended",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Degree recommended.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function recommend(Request $request)
    {
        $request->validate(['id' => 'required']);
        $this->degreeService->recommendDegree($request->user(), $request->id);
        return response()->json(['status' => 'success', 'message' => 'Degree recommended.']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/degree-verification/approve",
     *     operationId="adminApproveDegreeVerification",
     *     tags={"Admin Degree Verification"},
     *     summary="Approve a degree verification",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"userid","matno"},
     *             @OA\Property(property="userid", type="integer", example=1),
     *             @OA\Property(property="matno", type="string", example="MAT/2020/001")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Degree verification approved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Degree verification approved.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function approve(TreatDegreeRequest $request)
    {
        $this->degreeService->approveVerification($request->user(), $request->userid, $request->matno);
        return response()->json(['status' => 'success', 'message' => 'Degree verification approved.']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/degree-verification/view-document/{path}",
     *     operationId="adminViewDegreeDocument",
     *     tags={"Admin Degree Verification"},
     *     summary="View a degree verification document (PDF)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="path", in="path", required=true, description="Document path/filename",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="PDF file",
     *         @OA\MediaType(mediaType="application/pdf",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="File not found")
     * )
     */
    public function viewDocument(string $path)
    {
        $filename = basename($path);
        $filePath = public_path("{$filename}.pdf");
        if (!File::exists($filePath)) {
            return response()->json(['status' => 'failed', 'message' => 'File not found.'], 404);
        }
        return Response::make(file_get_contents($filePath), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}.pdf\"",
        ]);
    }
}
