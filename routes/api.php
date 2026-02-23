<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WhatsappController;

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

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public ticket routes
Route::get('/tickets/categories', [TicketController::class, 'create']);
Route::post('/tickets', [TicketController::class, 'store']);
Route::post('/tickets/check-status', [TicketController::class, 'checkStatus']);
Route::get('/tickets/{ticket}', [TicketController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // User tickets
    Route::get('/tickets', [TicketController::class, 'index']);
    
    // Admin only routes
    Route::middleware('admin')->group(function () {
        // Admin dashboard
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/admin/tickets', [AdminController::class, 'tickets']);
        Route::put('/admin/tickets/{ticket}/status', [AdminController::class, 'updateTicketStatus']);
        Route::delete('/admin/tickets/{ticket}', [AdminController::class, 'deleteTicket']);
        Route::get('/admin/export-tickets', [AdminController::class, 'exportTickets']);
        Route::get('/admin/category-stats', [AdminController::class, 'categoryStats']);
        
        // Admin ticket management
        Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);
        
        // Replies management
        Route::apiResource('replies', ReplyController::class);
        
        // WhatsApp notifications management
        Route::prefix('whatsapp')->group(function () {
            Route::post('/test-connection', [WhatsappController::class, 'testConnection']);
            Route::post('/send-daily-summary', [WhatsappController::class, 'sendDailySummary']);
            Route::post('/send-custom-message', [WhatsappController::class, 'sendCustomMessage']);
            Route::post('/resend-ticket-notification/{ticket}', [WhatsappController::class, 'resendTicketNotification']);
            Route::get('/settings', [WhatsappController::class, 'getSettings']);
            Route::post('/test-user-notification', [WhatsappController::class, 'testUserNotification']);
            Route::get('/logs', [WhatsappController::class, 'getNotificationLogs']);
        });
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});