<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Sede;

class ContextController extends Controller
{
    /**
     * Cambia la empresa activa en la sesión
     */
    public function setEmpresa(Request $request)
    {
        $empresaId = $request->input('empresa_id');

        if ($empresaId === 'all' || $empresaId === 'global') {
            session(['active_empresa_id' => null, 'active_sede_id' => null]);
            return back()->with('info', 'Vista cambiada a: Todas las Empresas (Global).');
        }

        $empresa = Empresa::findOrFail($empresaId);
        session(['active_empresa_id' => $empresa->id]);

        // Asignar primera sede de la empresa si existe
        $primeraSede = $empresa->sedes()->where('activo', true)->first();
        session(['active_sede_id' => $primeraSede ? $primeraSede->id : null]);

        return back()->with('info', 'Empresa activa cambiada a: ' . ($empresa->nombre_comercial ?? $empresa->razon_social));
    }

    /**
     * Cambia la sede activa en la sesión
     */
    public function setSede(Request $request)
    {
        $sedeId = $request->input('sede_id');

        if ($sedeId === 'all' || $sedeId === 'global') {
            session(['active_sede_id' => null]);
            return back()->with('info', 'Vista cambiada a: Todas las Sedes de la Empresa.');
        }

        $sede = Sede::findOrFail($sedeId);
        session(['active_sede_id' => $sede->id, 'active_empresa_id' => $sede->empresa_id]);

        return back()->with('info', 'Sede activa cambiada a: ' . $sede->nombre);
    }
}
