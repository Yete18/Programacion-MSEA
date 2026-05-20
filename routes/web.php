<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\DashboardEstudianteController;
use App\Http\Controllers\DashboardProfesorController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('index');
});

Route::get('/login', function () {
    return view('login');
});

Route::post('/login', [AuthController::class, 'login']);

Route::get('/registro', function () {
    return view('registro');
});

Route::post('/registro', [AuthController::class, 'register']);

Route::get('/dashboard-estudiante', [DashboardEstudianteController::class, 'show']);
Route::post('/dashboard-estudiante/perfil', [DashboardEstudianteController::class, 'updateProfile']);
Route::get('/dashboard-profesor', [DashboardProfesorController::class, 'show']);
Route::get('/dashboard-admin', [DashboardAdminController::class, 'show']);
Route::post('/dashboard-admin/profesores', [DashboardAdminController::class, 'storeProfesor']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/forgot-password', [PasswordResetController::class, 'showRequestForm']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendCode']);
Route::get('/verify-code', [PasswordResetController::class, 'showCodeForm']);
Route::post('/verify-code', [PasswordResetController::class, 'verifyCode']);
Route::get('/reset-password', [PasswordResetController::class, 'showResetForm']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
