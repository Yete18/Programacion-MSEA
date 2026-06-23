<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignStudentToElencoRequest;
use App\Http\Requests\AssignStudentToProfesorRequest;
use App\Http\Requests\StoreElencoRequest;
use App\Http\Requests\StoreProfesorRequest;
use App\Http\Requests\UpdateDirectorProfileRequest;
use App\Http\Requests\UpdateElencoRequest;
use App\Services\AdminDashboardService;

class DashboardAdminController extends Controller
{
    public function show(AdminDashboardService $adminDashboardService)
    {
        if (session('rol') !== 'director') {
            return redirect('/login')->with('error', 'Inicia sesion como director para continuar');
        }

        return view('dashboard-admin', $adminDashboardService->viewData((int) session('usuario_id')));
    }

    public function downloadReport(string $tipo, AdminDashboardService $adminDashboardService)
    {
        if (session('rol') !== 'director') {
            return redirect('/login')->with('error', 'Inicia sesion como director para continuar');
        }

        $report = $adminDashboardService->reportData($tipo);

        abort_if($report === null, 404);

        return response()->streamDownload(function () use ($report) {
            $output = fopen('php://output', 'w');
            fputcsv($output, $report['headers']);

            foreach ($report['rows'] as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $report['filename'], [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function updateProfile(UpdateDirectorProfileRequest $request, AdminDashboardService $adminDashboardService)
    {
        if (session('rol') !== 'director') {
            return redirect('/login')->with('error', 'Inicia sesion como director para continuar');
        }

        $guardoTrayectoria = $adminDashboardService->updateProfile((int) session('usuario_id'), $request->validated());

        $mensaje = $guardoTrayectoria
            ? 'Perfil del director actualizado correctamente.'
            : 'Perfil actualizado. La trayectoria queda pendiente hasta agregar la columna usuarios.trayectoria.';

        return redirect('/dashboard-admin#perfil')->with('success', $mensaje);
    }

    public function storeProfesor(StoreProfesorRequest $request, AdminDashboardService $adminDashboardService)
    {
        if (session('rol') !== 'director') {
            return redirect('/login')->with('error', 'Inicia sesion como director para continuar');
        }

        if (! $adminDashboardService->storeProfesor($request->validated())) {
            return back()->withInput()->with('error', 'No existe el rol profesor en la base de datos.');
        }

        return redirect('/dashboard-admin')->with('success', 'Profesor registrado correctamente.');
    }

    public function storeElenco(StoreElencoRequest $request, AdminDashboardService $adminDashboardService)
    {
        if (session('rol') !== 'director') {
            return redirect('/login')->with('error', 'Inicia sesion como director para continuar');
        }

        $resultado = $adminDashboardService->storeElenco($request->validated());

        if ($resultado !== true) {
            return back()->withInput()->with('error', $resultado);
        }

        return redirect('/dashboard-admin#elencos')->with('success', 'Elenco registrado correctamente.');
    }

    public function updateElenco(UpdateElencoRequest $request, int $idElenco, AdminDashboardService $adminDashboardService)
    {
        if (session('rol') !== 'director') {
            return redirect('/login')->with('error', 'Inicia sesion como director para continuar');
        }

        $adminDashboardService->updateElenco($idElenco, $request->validated());

        return redirect('/dashboard-admin#elencos')->with('success', 'Elenco actualizado correctamente.');
    }

    public function destroyElenco(int $idElenco, AdminDashboardService $adminDashboardService)
    {
        if (session('rol') !== 'director') {
            return redirect('/login')->with('error', 'Inicia sesion como director para continuar');
        }

        $adminDashboardService->destroyElenco($idElenco);

        return redirect('/dashboard-admin#elencos')->with('success', 'Elenco eliminado. Sus estudiantes quedaron sin elenco asignado.');
    }

    public function assignStudentToElenco(AssignStudentToElencoRequest $request, AdminDashboardService $adminDashboardService)
    {
        if (session('rol') !== 'director') {
            return redirect('/login')->with('error', 'Inicia sesion como director para continuar');
        }

        $validated = $request->validated();
        $resultado = $adminDashboardService->assignStudentToElenco(
            (int) $validated['id_estudiante'],
            $validated['id_elenco'] ?? null
        );

        if ($resultado !== true) {
            return back()->with('error', $resultado);
        }

        return redirect('/dashboard-admin#elencos')->with('success', 'Estudiante asignado correctamente.');
    }

    public function assignStudentToProfesor(AssignStudentToProfesorRequest $request, AdminDashboardService $adminDashboardService)
    {
        if (session('rol') !== 'director') {
            return redirect('/login')->with('error', 'Inicia sesion como director para continuar');
        }

        $validated = $request->validated();
        $resultado = $adminDashboardService->assignStudentToProfesor(
            (int) $validated['id_estudiante'],
            $validated['id_profesor'] ?? null
        );

        if ($resultado !== true) {
            return back()->with('error', $resultado);
        }

        return redirect('/dashboard-admin#estudiantes')->with('success', 'Profesor asignado correctamente.');
    }
}
