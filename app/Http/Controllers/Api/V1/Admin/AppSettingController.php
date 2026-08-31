<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/app-settings",
     *     operationId="adminListAppSettings",
     *     tags={"Admin Settings"},
     *     summary="List application settings, optionally filtered by group",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="group", in="query", required=false, description="Filter settings by group",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="List of settings",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="key", type="string"),
     *                     @OA\Property(property="value", type="string"),
     *                     @OA\Property(property="group", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $group = $request->query('group');

        $query = AppSetting::query();
        if ($group) {
            $query->where('group', $group);
        }

        return response()->json(['data' => $query->orderBy('group')->orderBy('key')->get()]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/app-settings",
     *     operationId="adminUpdateAppSettings",
     *     tags={"Admin Settings"},
     *     summary="Update application settings",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             required={"settings"},
     *             @OA\Property(property="settings", type="array",
     *                 @OA\Items(type="object",
     *                     required={"key","value"},
     *                     @OA\Property(property="key", type="string", example="site_name"),
     *                     @OA\Property(property="value", type="string", example="Transcript Portal")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Settings updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Settings updated.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'present|string',
        ]);

        foreach ($request->settings as $setting) {
            AppSetting::where('key', $setting['key'])->update(['value' => $setting['value']]);
        }

        AppSetting::clearCache();

        return response()->json(['message' => 'Settings updated.']);
    }
}
