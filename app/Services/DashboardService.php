<?php

namespace App\Services;

use App\Models\ComprobanteCompra;
use App\Models\ComprobanteVenta;
use App\Models\Area;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    /**
     * Obtiene métricas y KPIs financieros según el contexto activo de Empresa y Sede
     */
    public function getMetricas(?int $empresaId = null, ?int $sedeId = null): array
    {
        $hoy = Carbon::today()->toDateString();
        $proximos7Dias = Carbon::today()->addDays(7)->toDateString();
        $user = Auth::user();

        // 1. Query base para Ventas (CxC)
        $queryVentas = ComprobanteVenta::with(['empresa', 'sede', 'cliente', 'area']);
        if ($empresaId) {
            $queryVentas->where('empresa_id', $empresaId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $queryVentas->whereIn('empresa_id', $user->empresas()->pluck('empresas.id'));
        }

        if ($sedeId) {
            $queryVentas->where('sede_id', $sedeId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $sedesPermitidas = $user->sedes()->pluck('sedes.id');
            if ($sedesPermitidas->isNotEmpty()) {
                $queryVentas->whereIn('sede_id', $sedesPermitidas);
            }
        }

        // 2. Query base para Compras (CxP)
        $queryCompras = ComprobanteCompra::with(['empresa', 'sede', 'proveedor', 'area']);
        if ($empresaId) {
            $queryCompras->where('empresa_id', $empresaId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $queryCompras->whereIn('empresa_id', $user->empresas()->pluck('empresas.id'));
        }

        if ($sedeId) {
            $queryCompras->where('sede_id', $sedeId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $sedesPermitidas = $user->sedes()->pluck('sedes.id');
            if ($sedesPermitidas->isNotEmpty()) {
                $queryCompras->whereIn('sede_id', $sedesPermitidas);
            }
        }

        // 3. Totales de Cuentas por Cobrar (Soles y Dólares)
        $ventasPendientes = (clone $queryVentas)->whereIn('estado_pago', ['PENDIENTE', 'CON_ADELANTO'])->get();
        $totalCxcPen = $ventasPendientes->where('moneda', 'PEN')->sum('saldo_pendiente');
        $totalCxcUsd = $ventasPendientes->where('moneda', 'USD')->sum('saldo_pendiente');
        $totalCobradoPen = (clone $queryVentas)->where('moneda', 'PEN')->sum('monto_cobrado');

        // 4. Totales de Cuentas por Pagar (Soles y Dólares)
        $comprasPendientes = (clone $queryCompras)->whereIn('estado_pago', ['PENDIENTE', 'CON_ADELANTO'])->get();
        $totalCxpPen = $comprasPendientes->where('moneda', 'PEN')->sum('saldo_pendiente');
        $totalCxpUsd = $comprasPendientes->where('moneda', 'USD')->sum('saldo_pendiente');
        $totalPagadoPen = (clone $queryCompras)->where('moneda', 'PEN')->sum('monto_pagado');

        // 5. Alertas de Vencimiento
        $vencidasCxc = $ventasPendientes->filter(fn($v) => $v->fecha_vencimiento < $hoy)->count();
        $porVencerCxc = $ventasPendientes->filter(fn($v) => $v->fecha_vencimiento >= $hoy && $v->fecha_vencimiento <= $proximos7Dias)->count();

        $vencidasCxp = $comprasPendientes->filter(fn($c) => $c->fecha_vencimiento < $hoy)->count();
        $porVencerCxp = $comprasPendientes->filter(fn($c) => $c->fecha_vencimiento >= $hoy && $c->fecha_vencimiento <= $proximos7Dias)->count();

        // 6. Lista unificada de Alertas Prioritarias (Por vencer o Vencidas)
        $alertas = collect();

        foreach ($ventasPendientes as $v) {
            if ($v->fecha_vencimiento <= $proximos7Dias) {
                $alertas->push([
                    'tipo' => 'CxC (Cobrar)',
                    'tipo_class' => 'primary',
                    'empresa_nombre' => $v->empresa->nombre_comercial ?? $v->empresa->razon_social ?? 'Empresa',
                    'sede_nombre' => $v->sede->nombre ?? 'Sede',
                    'entidad' => $v->cliente->razon_social_nombre ?? 'Cliente',
                    'documento' => $v->cliente->numero_documento ?? '',
                    'telefono' => $v->cliente->telefono ?? '',
                    'nro_comprobante' => $v->tipo_comprobante . ' ' . $v->serie_correlativo,
                    'fecha_vencimiento' => $v->fecha_vencimiento,
                    'dias_restantes' => $v->dias_restantes,
                    'moneda' => $v->moneda,
                    'monto_total' => $v->monto_total,
                    'monto_amortizado' => $v->monto_cobrado,
                    'saldo_pendiente' => $v->saldo_pendiente,
                    'estado_pago' => $v->estado_pago,
                    'color' => $v->semaforo_color,
                    'texto_semaforo' => $v->semaforo_texto,
                    'url' => route('clientes.index'),
                ]);
            }
        }

        foreach ($comprasPendientes as $c) {
            if ($c->fecha_vencimiento <= $proximos7Dias) {
                $alertas->push([
                    'tipo' => 'CxP (Pagar)',
                    'tipo_class' => 'warning',
                    'empresa_nombre' => $c->empresa->nombre_comercial ?? $c->empresa->razon_social ?? 'Empresa',
                    'sede_nombre' => $c->sede->nombre ?? 'Sede',
                    'entidad' => $c->proveedor->razon_social ?? 'Proveedor',
                    'documento' => $c->proveedor->ruc ?? '',
                    'telefono' => $c->proveedor->telefono ?? '',
                    'nro_comprobante' => $c->tipo_comprobante . ' ' . $c->serie_correlativo,
                    'fecha_vencimiento' => $c->fecha_vencimiento,
                    'dias_restantes' => $c->dias_restantes,
                    'moneda' => $c->moneda,
                    'monto_total' => $c->monto_total,
                    'monto_amortizado' => $c->monto_pagado,
                    'saldo_pendiente' => $c->saldo_pendiente,
                    'estado_pago' => $c->estado_pago,
                    'color' => $c->semaforo_color,
                    'texto_semaforo' => $c->semaforo_texto,
                    'url' => route('proveedores.index'),
                ]);
            }
        }

        // Ordenar alertas: primero las vencidas (dias_restantes menor), luego las que vencen pronto
        $alertasOrdenadas = $alertas->sortBy('dias_restantes')->values();

        // 7. Distribución por Áreas (Taller, Ventas, Repuestos, Cigüeñal, etc.)
        $areas = Area::where('activo', true)->get();
        $distribucionAreas = [];
        foreach ($areas as $area) {
            $totalAreaVentas = (clone $queryVentas)->where('area_id', $area->id)->sum('monto_total');
            $totalAreaCompras = (clone $queryCompras)->where('area_id', $area->id)->sum('monto_total');
            $distribucionAreas[] = [
                'area_id' => $area->id,
                'nombre' => $area->nombre_area,
                'total_ventas' => $totalAreaVentas,
                'total_compras' => $totalAreaCompras,
            ];
        }

        return [
            'total_cxc_pen' => $totalCxcPen,
            'total_cxc_usd' => $totalCxcUsd,
            'total_cobrado_pen' => $totalCobradoPen,
            'total_cxp_pen' => $totalCxpPen,
            'total_cxp_usd' => $totalCxpUsd,
            'total_pagado_pen' => $totalPagadoPen,
            'vencidas_cxc_count' => $vencidasCxc,
            'por_vencer_cxc_count' => $porVencerCxc,
            'vencidas_cxp_count' => $vencidasCxp,
            'por_vencer_cxp_count' => $porVencerCxp,
            'total_alertas_count' => $vencidasCxc + $porVencerCxc + $vencidasCxp + $porVencerCxp,
            'alertas' => $alertasOrdenadas,
            'distribucion_areas' => $distribucionAreas,
        ];
    }
}
