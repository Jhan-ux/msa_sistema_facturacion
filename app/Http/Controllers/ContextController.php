<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Empresa;
use App\Models\Sede;

class ContextController extends Controller
{
    /**
     * Cambia la empresa activa en la sesión con validación de permisos
     */
    public function setEmpresa(Request $request)
    {
        $user = Auth::user();
        $empresaId = $request->input('empresa_id');

        if ($empresaId === 'all' || $empresaId === 'global') {
            if (!$user->isSuperAdmin()) {
                return back()->with('error', 'Solo los Superadministradores tienen autorización para la vista global.');
            }
            session(['active_empresa_id' => null, 'active_sede_id' => null]);
            return back()->with('info', 'Vista cambiada a: Todas las Empresas (Global).');
        }

        $empresa = Empresa::findOrFail($empresaId);

        // Validar que el usuario tenga acceso a la empresa solicitada
        if (!$user->isSuperAdmin() && !$user->empresas()->where('empresas.id', $empresa->id)->exists()) {
            return back()->with('error', 'No tiene permisos para acceder a la empresa seleccionada.');
        }

        session(['active_empresa_id' => $empresa->id]);

        // Asignar primera sede autorizada para el usuario
        if ($user->isSuperAdmin()) {
            $primeraSede = $empresa->sedes()->where('activo', true)->first();
        } else {
            $primeraSede = $user->sedes()->where('empresa_id', $empresa->id)->where('activo', true)->first();
        }

        session(['active_sede_id' => $primeraSede ? $primeraSede->id : null]);

        return back()->with('info', 'Empresa activa cambiada a: ' . ($empresa->nombre_comercial ?? $empresa->razon_social));
    }

    /**
     * Cambia la sede activa en la sesión con validación de permisos
     */
    public function setSede(Request $request)
    {
        $user = Auth::user();
        $sedeId = $request->input('sede_id');

        if ($sedeId === 'all' || $sedeId === 'global') {
            session(['active_sede_id' => null]);
            return back()->with('info', 'Vista cambiada a: Todas las Sedes de la Empresa.');
        }

        $sede = Sede::findOrFail($sedeId);

        // Validar que el usuario tenga acceso a la sede solicitada
        if (!$user->isSuperAdmin() && !$user->sedes()->where('sedes.id', $sede->id)->exists()) {
            return back()->with('error', 'No tiene permisos para acceder a la sede seleccionada.');
        }

        session(['active_sede_id' => $sede->id, 'active_empresa_id' => $sede->empresa_id]);

        return back()->with('info', 'Sede activa cambiada a: ' . $sede->nombre);
    }
}
