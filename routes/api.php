<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications', [\App\Http\Controllers\NotificationController::class, 'store']);
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/policies/accept', [\App\Http\Controllers\NotificationController::class, 'acceptPolicies']);
    Route::get('/dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'stats']);
    Route::post('/chunks/upload', [\App\Http\Controllers\ChunkUploadController::class, 'uploadChunk']);

    // List/Show routes (Vigilante & Admin)
    Route::get('/apartments', [ApartmentController::class, 'index']);
    Route::get('/apartments/{apartment}', [ApartmentController::class, 'show']);
    Route::get('/residents', [ResidentController::class, 'index']);
    Route::get('/residents/{resident}', [ResidentController::class, 'show']);
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);
    Route::get('/parking/status', [\App\Http\Controllers\ParkingController::class, 'status']);
    Route::get('/parking/history', [\App\Http\Controllers\ParkingController::class, 'history']);
    Route::post('/parking/entry', [\App\Http\Controllers\ParkingController::class, 'registerEntry']);
    Route::post('/parking/exit', [\App\Http\Controllers\ParkingController::class, 'registerExit']);
    
    Route::get('/people/search/{document}', [\App\Http\Controllers\PeopleController::class, 'showByDocument']);
    Route::apiResource('people', \App\Http\Controllers\PeopleController::class)->except(['destroy']);
    Route::apiResource('visits', \App\Http\Controllers\VisitController::class);
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index']);
    Route::get('/messages', [\App\Http\Controllers\MessageController::class, 'index']);
    Route::get('/messages/sent', [\App\Http\Controllers\MessageController::class, 'sent']);
    Route::get('/messages/{message}', [\App\Http\Controllers\MessageController::class, 'show']);
    Route::get('/surveys', [\App\Http\Controllers\SurveyController::class, 'index']);
    Route::get('/surveys/{survey}', [\App\Http\Controllers\SurveyController::class, 'show']);
    Route::get('/coexistence-reports', [\App\Http\Controllers\CoexistenceReportController::class, 'index']);
    Route::get('/coexistence-reports/{coexistence_report}', [\App\Http\Controllers\CoexistenceReportController::class, 'show']);
    Route::get('/surveys', [\App\Http\Controllers\SurveyController::class, 'index']);
    Route::get('/surveys/{survey}', [\App\Http\Controllers\SurveyController::class, 'show']);

    // Payments (Shared Admin/Resident)
    Route::get('/admin-payments', [\App\Http\Controllers\AdminPaymentController::class, 'index']);
    Route::get('/admin-payments/{admin_payment}', [\App\Http\Controllers\AdminPaymentController::class, 'show']);

    // Admin-only routes (Create, Update, Delete)
    Route::middleware('role:admin')->group(function () {
        Route::post('/vigilantes', [\App\Http\Controllers\VigilanteController::class, 'store']);

        Route::post('/apartments', [ApartmentController::class, 'store']);
        Route::put('/apartments/{apartment}', [ApartmentController::class, 'update']);
        Route::delete('/apartments/{apartment}', [ApartmentController::class, 'destroy']);

        Route::post('/residents', [ResidentController::class, 'store']);
        Route::put('/residents/{resident}', [ResidentController::class, 'update']);
        Route::delete('/residents/{resident}', [ResidentController::class, 'destroy']);

        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);

        Route::post('/parking/settings', [\App\Http\Controllers\ParkingController::class, 'updateSettings']);

        Route::post('/messages', [\App\Http\Controllers\MessageController::class, 'store']);
        Route::post('/messages/{message}/read', [\App\Http\Controllers\MessageController::class, 'markAsRead']);

        Route::delete('/people/{person}', [\App\Http\Controllers\PeopleController::class, 'destroy']);

        Route::post('/admin-payments', [\App\Http\Controllers\AdminPaymentController::class, 'store']);
        Route::put('/admin-payments/{admin_payment}', [\App\Http\Controllers\AdminPaymentController::class, 'update']);
        Route::delete('/admin-payments/{admin_payment}', [\App\Http\Controllers\AdminPaymentController::class, 'destroy']);

        Route::post('/coexistence-reports', [\App\Http\Controllers\CoexistenceReportController::class, 'store']);
        Route::put('/coexistence-reports/{coexistence_report}', [\App\Http\Controllers\CoexistenceReportController::class, 'update']);
        Route::delete('/coexistence-reports/{coexistence_report}', [\App\Http\Controllers\CoexistenceReportController::class, 'destroy']);

        Route::post('/surveys', [\App\Http\Controllers\SurveyController::class, 'store']);
        Route::put('/surveys/{survey}', [\App\Http\Controllers\SurveyController::class, 'update']);
        Route::delete('/surveys/{survey}', [\App\Http\Controllers\SurveyController::class, 'destroy']);
    });
});
