<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ComprobanteCompra;
use App\Models\ComprobanteVenta;
use App\Models\Area;

class ReporteController extends Controller
{
    /**
     * Reporte consolidado de Cuentas por Pagar (Proveedores)
     */
    public function cuentasPorPagar(Request $request)
    {
        $user = Auth::user();
        $empresaId = session('active_empresa_id');
        $sedeId = session('active_sede_id');

        $query = ComprobanteCompra::with(['empresa', 'sede', 'proveedor', 'area', 'pagos']);

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $query->whereIn('empresa_id', $user->empresas()->pluck('empresas.id'));
        }

        if ($sedeId) {
            $query->where('sede_id', $sedeId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $sedesPermitidas = $user->sedes()->pluck('sedes.id');
            if ($sedesPermitidas->isNotEmpty()) {
                $query->whereIn('sede_id', $sedesPermitidas);
            }
        }

        if ($request->filled('estado')) {
            $query->where('estado_pago', $request->estado);
        }
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_emision', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_emision', '<=', $request->fecha_hasta);
        }

        $comprobantes = $query->orderBy('fecha_vencimiento', 'asc')->get();
        $areas = Area::where('activo', true)->get();

        return view('reportes.cuentas_por_pagar', compact('comprobantes', 'areas'));
    }

    /**
     * Reporte consolidado de Cuentas por Cobrar (Clientes)
     */
    public function cuentasPorCobrar(Request $request)
    {
        $user = Auth::user();
        $empresaId = session('active_empresa_id');
        $sedeId = session('active_sede_id');

        $query = ComprobanteVenta::with(['empresa', 'sede', 'cliente', 'area', 'pagos']);

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $query->whereIn('empresa_id', $user->empresas()->pluck('empresas.id'));
        }

        if ($sedeId) {
            $query->where('sede_id', $sedeId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $sedesPermitidas = $user->sedes()->pluck('sedes.id');
            if ($sedesPermitidas->isNotEmpty()) {
                $query->whereIn('sede_id', $sedesPermitidas);
            }
        }

        if ($request->filled('estado')) {
            $query->where('estado_pago', $request->estado);
        }
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_emision', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_emision', '<=', $request->fecha_hasta);
        }

        $comprobantes = $query->orderBy('fecha_vencimiento', 'asc')->get();
        $areas = Area::where('activo', true)->get();

        return view('reportes.cuentas_por_cobrar', compact('comprobantes', 'areas'));
    }
}
