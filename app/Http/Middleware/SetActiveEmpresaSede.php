<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Empresa;
use App\Models\Sede;

class SetActiveEmpresaSede
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // 1. Obtener empresas disponibles para el usuario
        if ($user && $user->isSuperAdmin()) {
            $empresasDisponibles = Empresa::where('activo', true)->get();
        } elseif ($user) {
            $empresasDisponibles = $user->empresas()->where('activo', true)->get();
        } else {
            $empresasDisponibles = Empresa::where('activo', true)->get();
        }

        // 2. Determinar empresa activa y validar pertenencia
        $activeEmpresaId = session('active_empresa_id');
        if ($activeEmpresaId && !$empresasDisponibles->pluck('id')->contains($activeEmpresaId)) {
            $activeEmpresaId = $empresasDisponibles->isNotEmpty() ? $empresasDisponibles->first()->id : null;
            session(['active_empresa_id' => $activeEmpresaId]);
        } elseif ($activeEmpresaId === null && $empresasDisponibles->isNotEmpty() && (!$user || !$user->isSuperAdmin())) {
            $activeEmpresaId = $empresasDisponibles->first()->id;
            session(['active_empresa_id' => $activeEmpresaId]);
        }

        $empresaActiva = $activeEmpresaId ? Empresa::find($activeEmpresaId) : null;

        // 3. Obtener sedes disponibles para la empresa activa
        if ($empresaActiva) {
            if ($user && $user->isSuperAdmin()) {
                $sedesDisponibles = $empresaActiva->sedes()->where('activo', true)->get();
            } elseif ($user) {
                $sedesDisponibles = $user->sedes()->where('empresa_id', $empresaActiva->id)->where('activo', true)->get();
            } else {
                $sedesDisponibles = $empresaActiva->sedes()->where('activo', true)->get();
            }
        } else {
            $sedesDisponibles = collect();
        }

        // 4. Determinar sede activa
        $activeSedeId = session('active_sede_id');
        // Validar que la sede pertenezca a la empresa activa
        if ($activeSedeId && !$sedesDisponibles->pluck('id')->contains($activeSedeId)) {
            $activeSedeId = $sedesDisponibles->isNotEmpty() ? $sedesDisponibles->first()->id : null;
            session(['active_sede_id' => $activeSedeId]);
        } elseif ($activeSedeId === null && $sedesDisponibles->isNotEmpty()) {
            $activeSedeId = $sedesDisponibles->first()->id;
            session(['active_sede_id' => $activeSedeId]);
        }

        $sedeActiva = $activeSedeId ? Sede::find($activeSedeId) : null;

        // 5. Compartir variables globales a todas las vistas Blade
        View::share('empresasDisponibles', $empresasDisponibles);
        View::share('sedesDisponibles', $sedesDisponibles);
        View::share('empresaActiva', $empresaActiva);
        View::share('sedeActiva', $sedeActiva);
        View::share('activeEmpresaId', $activeEmpresaId);
        View::share('activeSedeId', $activeSedeId);

        return $next($request);
    }
}
