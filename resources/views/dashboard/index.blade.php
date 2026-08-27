@extends('layouts.app')

@section('title', 'Dashboard Financiero y Alertas')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-gauge text-primary me-2"></i> Tablero de Control Contable
        </h4>
        <p class="text-muted small mb-0">
            Mostrando información de: 
            <span class="badge bg-primary">{{ $empresaActiva->nombre_comercial ?? $empresaActiva->razon_social ?? '🌐 Todas las Empresas (Global)' }}</span>
            @if($sedeActiva)
                <span class="badge bg-success"><i class="fa-solid fa-location-dot me-1"></i> {{ $sedeActiva->nombre }}</span>
            @else
                <span class="badge bg-secondary">Todas las Sedes</span>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('proveedores.create') }}" class="btn btn-sm btn-outline-primary">
            <i class="fa-solid fa-plus me-1"></i> Factura Compra
        </a>
        <a href="{{ route('clientes.create') }}" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Factura Venta
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <!-- Cuentas por Cobrar (Clientes) -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Cuentas por Cobrar (CxC)</div>
                    <div class="fs-4 fw-bold text-primary mt-1">S/ {{ number_format($metricas['total_cxc_pen'], 2) }}</div>
                    @if($metricas['total_cxc_usd'] > 0)
                        <div class="small text-muted fw-semibold">$ {{ number_format($metricas['total_cxc_usd'], 2) }} USD</div>
                    @endif
                </div>
                <div class="icon-box bg-primary-subtle text-primary">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top d-flex justify-content-between small text-muted">
                <span>Cobrado: S/ {{ number_format($metricas['total_cobrado_pen'], 2) }}</span>
                <a href="{{ route('clientes.index') }}" class="text-decoration-none fw-bold text-primary">Ver detalle &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Cuentas por Pagar (Proveedores) -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Cuentas por Pagar (CxP)</div>
                    <div class="fs-4 fw-bold text-danger mt-1">S/ {{ number_format($metricas['total_cxp_pen'], 2) }}</div>
                    @if($metricas['total_cxp_usd'] > 0)
                        <div class="small text-muted fw-semibold">$ {{ number_format($metricas['total_cxp_usd'], 2) }} USD</div>
                    @endif
                </div>
                <div class="icon-box bg-danger-subtle text-danger">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top d-flex justify-content-between small text-muted">
                <span>Pagado: S/ {{ number_format($metricas['total_pagado_pen'], 2) }}</span>
                <a href="{{ route('proveedores.index') }}" class="text-decoration-none fw-bold text-danger">Ver detalle &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Por Vencer (Próximos 7 días) -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Próximos a Vencer (7 días)</div>
                    <div class="fs-4 fw-bold text-warning mt-1">{{ $metricas['por_vencer_cxc_count'] + $metricas['por_vencer_cxp_count'] }}</div>
                    <div class="small text-muted">CxC: {{ $metricas['por_vencer_cxc_count'] }} | CxP: {{ $metricas['por_vencer_cxp_count'] }}</div>
                </div>
                <div class="icon-box bg-warning-subtle text-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top small text-warning fw-semibold">
                <i class="fa-regular fa-clock me-1"></i> Requiere programación
            </div>
        </div>
    </div>

    <!-- Vencidas Hoy o Anteriores -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Vencidas (Mora)</div>
                    <div class="fs-4 fw-bold text-danger mt-1">{{ $metricas['vencidas_cxc_count'] + $metricas['vencidas_cxp_count'] }}</div>
                    <div class="small text-muted">CxC: {{ $metricas['vencidas_cxc_count'] }} | CxP: {{ $metricas['vencidas_cxp_count'] }}</div>
                </div>
                <div class="icon-box bg-danger-subtle text-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top small text-danger fw-semibold">
                <i class="fa-solid fa-bell me-1"></i> Atención prioritaria
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Tabla Central de Alertas Inmediatas -->
    <div class="col-12 col-xl-8">
        <div class="msa-card">
            <div class="msa-card-header bg-light">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-traffic-light text-danger fs-5"></i>
                    <span>Bandeja de Alertas de Vencimiento</span>
                    <span class="badge bg-danger rounded-pill">{{ count($metricas['alertas']) }}</span>
                </div>
                <small class="text-muted">Ordenadas por prioridad de vencimiento</small>
            </div>
            <div class="msa-card-body p-0">
                @if(count($metricas['alertas']) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted text-uppercase">
                                <tr>
                                    <th>Semáforo</th>
                                    <th>Tipo</th>
                                    <th>Entidad (Cliente / Proveedor)</th>
                                    <th>Comprobante</th>
                                    <th>Vencimiento</th>
                                    <th class="text-end">Saldo Pendiente</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($metricas['alertas'] as $alerta)
                                    <tr>
                                        <td>
                                            @if($alerta['color'] === 'danger')
                                                <span class="badge badge-semaforo-rojo px-2 py-1">
                                                    <i class="fa-solid fa-circle text-danger me-1"></i> {{ $alerta['texto_semaforo'] }}
                                                </span>
                                            @elseif($alerta['color'] === 'warning')
                                                <span class="badge badge-semaforo-amarillo px-2 py-1">
                                                    <i class="fa-solid fa-circle text-warning me-1"></i> {{ $alerta['texto_semaforo'] }}
                                                </span>
                                            @else
                                                <span class="badge badge-semaforo-verde px-2 py-1">
                                                    <i class="fa-solid fa-circle text-success me-1"></i> {{ $alerta['texto_semaforo'] }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $alerta['tipo_class'] }}-subtle text-{{ $alerta['tipo_class'] }} border border-{{ $alerta['tipo_class'] }}-subtle">
                                                {{ $alerta['tipo'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $alerta['entidad'] }}</div>
                                            <small class="text-muted">{{ $alerta['documento'] }} &bull; {{ $alerta['sede_nombre'] }}</small>
                                        </td>
                                        <td>
                                            <span class="font-monospace fw-semibold">{{ $alerta['nro_comprobante'] }}</span>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold">{{ \Carbon\Carbon::parse($alerta['fecha_vencimiento'])->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="text-end">
                                            <div class="fw-bold text-dark">{{ $alerta['moneda'] === 'USD' ? '$' : 'S/' }} {{ number_format($alerta['saldo_pendiente'], 2) }}</div>
                                            @if($alerta['monto_amortizado'] > 0)
                                                <small class="text-success">Adelanto: {{ $alerta['moneda'] === 'USD' ? '$' : 'S/' }} {{ number_format($alerta['monto_amortizado'], 2) }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ $alerta['url'] }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fa-solid fa-circle-check text-success fs-1 mb-2"></i>
                        <p class="mb-0">¡Excelente! No hay comprobantes con vencimientos críticos en los próximos 7 días.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Desglose por Áreas (Centros de Costos) -->
    <div class="col-12 col-xl-4">
        <div class="msa-card h-100">
            <div class="msa-card-header bg-light">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-cubes text-primary fs-5"></i>
                    <span>Áreas & Centros de Costo</span>
                </div>
            </div>
            <div class="msa-card-body">
                <p class="text-muted small mb-3">Distribución de facturación y compras por área operativa:</p>
                <div class="list-group list-group-flush">
                    @foreach($metricas['distribucion_areas'] as $area)
                        <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                    @if($area['nombre'] == 'Taller')
                                        <i class="fa-solid fa-wrench text-primary"></i>
                                    @elseif($area['nombre'] == 'Repuestos')
                                        <i class="fa-solid fa-gear text-info"></i>
                                    @elseif($area['nombre'] == 'Cigüeñal')
                                        <i class="fa-solid fa-screwdriver-wrench text-warning"></i>
                                    @elseif($area['nombre'] == 'Ventas')
                                        <i class="fa-solid fa-cart-shopping text-success"></i>
                                    @else
                                        <i class="fa-solid fa-folder text-secondary"></i>
                                    @endif
                                    {{ $area['nombre'] }}
                                </div>
                                <small class="text-muted">Ventas: S/ {{ number_format($area['total_ventas'], 2) }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-light text-dark border">
                                    Compras: S/ {{ number_format($area['total_compras'], 2) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
