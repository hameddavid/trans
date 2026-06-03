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

    public function pending(Request $request)
    {
        $apps = DegreeVerification::whereIn('status', ['PENDING', 'TREATED'])->latest()->paginate($request->integer('per_page', 15));
        return DegreeVerificationResource::collection($apps);
    }

    public function recommended(Request $request)
    {
        $apps = DegreeVerification::where('status', 'RECOMMENDED')->latest()->paginate($request->integer('per_page', 15));
        return DegreeVerificationResource::collection($apps);
    }

    public function approved(Request $request)
    {
        $apps = DegreeVerification::where('status', 'APPROVED')->latest()->paginate($request->integer('per_page', 15));
        return DegreeVerificationResource::collection($apps);
    }

    public function treat(TreatDegreeRequest $request)
    {
        $this->degreeService->treatVerification($request->user(), $request->userid, $request->matno);
        return response()->json(['status' => 'success', 'message' => 'Degree verification treated.']);
    }

    public function recommend(Request $request)
    {
        $request->validate(['id' => 'required']);
        $this->degreeService->recommendDegree($request->user(), $request->id);
        return response()->json(['status' => 'success', 'message' => 'Degree recommended.']);
    }

    public function approve(TreatDegreeRequest $request)
    {
        $this->degreeService->approveVerification($request->user(), $request->userid, $request->matno);
        return response()->json(['status' => 'success', 'message' => 'Degree verification approved.']);
    }

    public function viewDocument(string $path)
    {
        $filePath = public_path("{$path}.pdf");
        if (!File::exists($filePath)) {
            return response()->json(['status' => 'failed', 'message' => 'File not found.'], 404);
        }
        return Response::make(file_get_contents($filePath), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$path}.pdf\"",
        ]);
    }
}
