<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AssessmentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// V1 Routes
Route::prefix('v1')->group(function () {
    
    // Protected Routes (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        
        // Assessments
        Route::get('/assessments/{id}', [AssessmentController::class, 'show']);

        // User Info (Default)
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
