<?php

/**
 * Add this use statement at the top of routes/api.php (with the other controller imports):
 *
 *   use App\Http\Controllers\Api\V1\Admin\StudentImportController;
 *
 * Then add the following route group inside the existing
 * Route::prefix('admin')->middleware('auth:admin') block,
 * e.g. after the 'results' prefix group:
 */

Route::prefix('students')->group(function () {
    Route::post('import', [StudentImportController::class, 'import']);
    Route::post('promote', [StudentImportController::class, 'promote']);
});

/**
 * Also add this inside the 'results' prefix group (alongside the existing upload/delete routes):
 *
 *   Route::post('update-flag-waver', [ResultUploadController::class, 'updateFlagWaver']);
 */
