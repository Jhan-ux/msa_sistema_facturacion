@extends('layouts.app')

@section('title', 'Proveedores y Cuentas por Pagar (CxP)')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-truck-field text-danger me-2"></i> Proveedores & Cuentas por Pagar (CxP)
        </h4>
        <p class="text-muted small mb-0">Control de facturas de compras, gastos operativos, adelantos a proveedores y vencimientos.</p>
    </div>
    <div>
        <a href="{{ route('proveedores.create') }}" class="btn btn-danger shadow-sm fw-semibold">
            <i class="fa-solid fa-plus-circle me-1"></i> Registrar Factura Proveedor
        </a>
    </div>
</div>

<!-- Resumen Rápido -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card border-danger border-opacity-25 bg-danger bg-opacity-10">
            <div class="text-danger small fw-bold text-uppercase">Deuda Pendiente (Soles)</div>
            <div class="fs-4 fw-bold text-danger mt-1">S/ {{ number_format($totalPendientePen, 2) }}</div>
            <small class="text-muted">Monto total por cancelar a proveedores en PEN</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card border-info border-opacity-25 bg-info bg-opacity-10">
            <div class="text-info-emphasis small fw-bold text-uppercase">Deuda Pendiente (Dólares)</div>
            <div class="fs-4 fw-bold text-info mt-1">$ {{ number_format($totalPendienteUsd, 2) }} USD</div>
            <small class="text-muted">Monto total por cancelar a proveedores en USD</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card border-success border-opacity-25 bg-success bg-opacity-10">
            <div class="text-success small fw-bold text-uppercase">Total Pagado / Amortizado</div>
            <div class="fs-4 fw-bold text-success mt-1">S/ {{ number_format($totalPagadoPen, 2) }}</div>
            <small class="text-muted">Adelantos y liquidaciones realizadas</small>
        </div>
    </div>
</div>

<!-- Filtros de Búsqueda -->
<div class="msa-card mb-4">
    <div class="msa-card-body p-3">
        <form method="GET" action="{{ route('proveedores.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted">ESTADO DE PAGO</label>
                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Todos los Estados --</option>
                    <option value="PENDIENTE" {{ request('estado') == 'PENDIENTE' ? 'selected' : '' }}>⏳ Pendiente</option>
                    <option value="CON_ADELANTO" {{ request('estado') == 'CON_ADELANTO' ? 'selected' : '' }}>💳 Con Adelanto</option>
                    <option value="PAGADO" {{ request('estado') == 'PAGADO' ? 'selected' : '' }}>✅ Pagado</option>
                    <option value="VENCIDOS" {{ request('estado') == 'VENCIDOS' ? 'selected' : '' }}>🚨 Vencidos</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted">ÁREA / CENTRO COSTO</label>
                <select name="area_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Todas las Áreas --</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                            {{ $area->nombre_area }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-muted">MONEDA</label>
                <select name="moneda" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Todas --</option>
                    <option value="PEN" {{ request('moneda') == 'PEN' ? 'selected' : '' }}>Soles (S/)</option>
                    <option value="USD" {{ request('moneda') == 'USD' ? 'selected' : '' }}>Dólares ($)</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-muted">FECHA DESDE</label>
                <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
                <a href="{{ route('proveedores.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla Principal de Facturas de Proveedores -->
<div class="msa-card">
    <div class="msa-card-header">
        <span><i class="fa-solid fa-list-check me-2 text-danger"></i> Comprobantes de Compras ({{ $comprobantes->count() }})</span>
    </div>
    <div class="msa-card-body p-3">
        <table id="tablaProveedores" class="table table-hover align-middle w-100">
            <thead class="table-light small text-muted text-uppercase">
                <tr>
                    <th>Semáforo</th>
                    <th>Empresa / Sede</th>
                    <th>Proveedor</th>
                    <th>Comprobante</th>
                    <th>Área</th>
                    <th>Emisión</th>
                    <th>Vencimiento</th>
                    <th class="text-end">Monto Total</th>
                    <th class="text-end">Pagado / Adelanto</th>
                    <th class="text-end">Saldo Pendiente</th>
                    <th>Estado</th>
                    <th class="text-center no-export">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sumTotalPen = 0;
                    $sumPagadoPen = 0;
                    $sumSaldoPen = 0;

                    $sumTotalUsd = 0;
                    $sumPagadoUsd = 0;
                    $sumSaldoUsd = 0;
                @endphp
                @foreach($comprobantes as $comp)
                    @php
                        if ($comp->moneda === 'USD') {
                            $sumTotalUsd += $comp->monto_total;
                            $sumPagadoUsd += $comp->monto_pagado;
                            $sumSaldoUsd += $comp->saldo_pendiente;
                        } else {
                            $sumTotalPen += $comp->monto_total;
                            $sumPagadoPen += $comp->monto_pagado;
                            $sumSaldoPen += $comp->saldo_pendiente;
                        }
                    @endphp
                    <tr>
                        <td>
                            @if($comp->semaforo_color === 'danger')
                                <span class="badge badge-semaforo-rojo px-2 py-1">
                                    <i class="fa-solid fa-circle text-danger me-1"></i> {{ $comp->semaforo_texto }}
                                </span>
                            @elseif($comp->semaforo_color === 'warning')
                                <span class="badge badge-semaforo-amarillo px-2 py-1">
                                    <i class="fa-solid fa-circle text-warning me-1"></i> {{ $comp->semaforo_texto }}
                                </span>
                            @else
                                <span class="badge badge-semaforo-verde px-2 py-1">
                                    <i class="fa-solid fa-circle text-success me-1"></i> {{ $comp->semaforo_texto }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold small text-dark">{{ $comp->empresa->nombre_comercial ?? $comp->empresa->razon_social }}</div>
                            <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i> {{ $comp->sede->nombre }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $comp->proveedor->razon_social }}</div>
                            <small class="text-muted font-monospace"><i class="fa-regular fa-id-card me-1"></i> {{ $comp->proveedor->ruc }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace">
                                {{ $comp->tipo_comprobante }} {{ $comp->serie_correlativo }}
                            </span>
                            @if($comp->descripcion)
                                <div class="small text-muted text-truncate" style="max-width: 180px;" title="{{ $comp->descripcion }}">
                                    {{ $comp->descripcion }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border">
                                {{ $comp->area->nombre_area }}
                            </span>
                        </td>
                        <td class="small">{{ $comp->fecha_emision->format('d/m/Y') }}</td>
                        <td class="small fw-semibold">{{ $comp->fecha_vencimiento->format('d/m/Y') }}</td>
                        <td class="text-end fw-bold text-dark">
                            {{ $comp->moneda === 'USD' ? '$' : 'S/' }} {{ number_format($comp->monto_total, 2) }}
                        </td>
                        <td class="text-end fw-semibold text-success">
                            {{ $comp->moneda === 'USD' ? '$' : 'S/' }} {{ number_format($comp->monto_pagado, 2) }}
                        </td>
                        <td class="text-end fw-bold {{ $comp->saldo_pendiente > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ $comp->moneda === 'USD' ? '$' : 'S/' }} {{ number_format($comp->saldo_pendiente, 2) }}
                        </td>
                        <td>
                            @if($comp->estado_pago === 'PAGADO')
                                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> PAGADO</span>
                            @elseif($comp->estado_pago === 'CON_ADELANTO')
                                <span class="badge bg-info text-dark"><i class="fa-solid fa-clock-rotate-left me-1"></i> CON ADELANTO</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="fa-regular fa-hourglass me-1"></i> PENDIENTE</span>
                            @endif
                        </td>
                        <td class="text-center no-export">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-success" onclick="abrirModalAbonosCompra({{ $comp->id }})" title="Ver / Registrar Adelantos">
                                    <i class="fa-solid fa-money-bill-wave me-1"></i> Adelantos ({{ $comp->pagos->count() }})
                                </button>
                                <form action="{{ route('proveedores.destroy', $comp->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta factura de compra?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Eliminar Comprobante">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="7" class="text-end text-uppercase">Total Consolidado Soles (PEN):</td>
                    <td class="text-end">S/ {{ number_format($sumTotalPen, 2) }}</td>
                    <td class="text-end text-success">S/ {{ number_format($sumPagadoPen, 2) }}</td>
                    <td class="text-end text-danger">S/ {{ number_format($sumSaldoPen, 2) }}</td>
                    <td></td>
                    <td class="no-export"></td>
                </tr>
                @if($sumTotalUsd > 0)
                <tr>
                    <td colspan="7" class="text-end text-uppercase text-info-emphasis">Total Consolidado Dólares (USD):</td>
                    <td class="text-end text-info-emphasis">$ {{ number_format($sumTotalUsd, 2) }}</td>
                    <td class="text-end text-success">$ {{ number_format($sumPagadoUsd, 2) }}</td>
                    <td class="text-end text-danger">$ {{ number_format($sumSaldoUsd, 2) }}</td>
                    <td></td>
                    <td class="no-export"></td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>
@endsection
