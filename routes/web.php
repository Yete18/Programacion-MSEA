<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\DashboardEstudianteController;
use App\Http\Controllers\DashboardPadreController;
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

Route::middleware('auth.session')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:estudiante')->group(function () {
        Route::get('/dashboard-estudiante', [DashboardEstudianteController::class, 'show']);
        Route::post('/dashboard-estudiante/perfil', [DashboardEstudianteController::class, 'updateProfile']);
        Route::post('/dashboard-estudiante/practica', [DashboardEstudianteController::class, 'recordPractice']);
        Route::post('/dashboard-estudiante/tareas/{idTarea}/entregar', [DashboardEstudianteController::class, 'submitTask']);
    });

    Route::middleware('role:profesor')->group(function () {
        Route::get('/dashboard-profesor', [DashboardProfesorController::class, 'show']);
        Route::post('/dashboard-profesor/tareas', [DashboardProfesorController::class, 'storeTask']);
        Route::post('/dashboard-profesor/entregas/{idEntrega}/calificar', [DashboardProfesorController::class, 'gradeTask']);
        Route::get('/dashboard-profesor/alumnos/{idEstudiante}/progreso', [DashboardProfesorController::class, 'studentProgress']);
    });

    Route::middleware('role:padre')->group(function () {
        Route::get('/dashboard-padre', [DashboardPadreController::class, 'show']);
    });

    Route::middleware('role:director')->group(function () {
        Route::get('/dashboard-admin', [DashboardAdminController::class, 'show']);
        Route::post('/dashboard-admin/perfil', [DashboardAdminController::class, 'updateProfile']);
        Route::post('/dashboard-admin/profesores', [DashboardAdminController::class, 'storeProfesor']);
        Route::post('/dashboard-admin/elencos', [DashboardAdminController::class, 'storeElenco']);
        Route::put('/dashboard-admin/elencos/{idElenco}', [DashboardAdminController::class, 'updateElenco']);
        Route::delete('/dashboard-admin/elencos/{idElenco}', [DashboardAdminController::class, 'destroyElenco']);
        Route::post('/dashboard-admin/elencos/asignar-estudiante', [DashboardAdminController::class, 'assignStudentToElenco']);
        Route::post('/dashboard-admin/profesores/asignar-estudiante', [DashboardAdminController::class, 'assignStudentToProfesor']);
    });
});

Route::get('/forgot-password', [PasswordResetController::class, 'showRequestForm']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendCode']);
Route::get('/verify-code', [PasswordResetController::class, 'showCodeForm']);
Route::post('/verify-code', [PasswordResetController::class, 'verifyCode']);
Route::get('/reset-password', [PasswordResetController::class, 'showResetForm']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
