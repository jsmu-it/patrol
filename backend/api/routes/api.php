<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CheckpointController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\PatrolController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ShiftController;
use Illuminate\Support\Facades\Route;

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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/me/device-token', [AuthController::class, 'updateDeviceToken']);

    Route::get('/me/available-shifts', [ShiftController::class, 'availableForCurrentUser']);

    Route::middleware('role:SUPERADMIN,ADMIN,PROJECT_ADMIN')->group(function (): void {
        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('shifts', ShiftController::class)->except(['index']);
        Route::apiResource('checkpoints', CheckpointController::class);
        Route::get('projects/{project}/shifts', [ProjectController::class, 'shifts']);
        Route::post('projects/{project}/shifts', [ProjectController::class, 'syncShifts']);
    });

    Route::get('/shifts', [ShiftController::class, 'index']);

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);

    Route::post('/patrol/logs', [PatrolController::class, 'store']);
    Route::get('/patrol/history', [PatrolController::class, 'history']);
    Route::get('/patrol/checkpoint', [PatrolController::class, 'checkCheckpoint']);

    Route::apiResource('leave-requests', LeaveRequestController::class)->only(['index', 'store', 'show']);
});
