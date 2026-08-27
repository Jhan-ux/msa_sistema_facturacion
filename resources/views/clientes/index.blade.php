@extends('layouts.app')

@section('title', 'Clientes y Cuentas por Cobrar (CxC)')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-users text-primary me-2"></i> Clientes & Cuentas por Cobrar (CxC)
        </h4>
        <p class="text-muted small mb-0">Control de facturas a clientes, adelantos recibidos, saldos pendientes y cobranzas vía WhatsApp.</p>
    </div>
    <div>
        <a href="{{ route('clientes.create') }}" class="btn btn-primary shadow-sm fw-semibold">
            <i class="fa-solid fa-file-circle-plus me-1"></i> Registrar Factura / Venta a Cliente
        </a>
    </div>
</div>

<!-- Resumen Financiero de Cobranzas -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card border-primary border-opacity-25 bg-primary bg-opacity-10">
            <div class="text-primary small fw-bold text-uppercase">Total por Cobrar (Soles)</div>
            <div class="fs-4 fw-bold text-primary mt-1">S/ {{ number_format($totalPendientePen, 2) }}</div>
            <small class="text-muted">Cartera pendiente de cobro en Soles</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card border-info border-opacity-25 bg-info bg-opacity-10">
            <div class="text-info-emphasis small fw-bold text-uppercase">Total por Cobrar (Dólares)</div>
            <div class="fs-4 fw-bold text-info mt-1">$ {{ number_format($totalPendienteUsd, 2) }} USD</div>
            <small class="text-muted">Cartera pendiente de cobro en USD</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card border-success border-opacity-25 bg-success bg-opacity-10">
            <div class="text-success small fw-bold text-uppercase">Total Adelantos / Cobrado</div>
            <div class="fs-4 fw-bold text-success mt-1">S/ {{ number_format($totalCobradoPen, 2) }}</div>
            <small class="text-muted">Ingresos recibidos en cuenta/efectivo</small>
        </div>
    </div>
</div>

<!-- Filtros de Búsqueda -->
<div class="msa-card mb-4">
    <div class="msa-card-body p-3">
        <form method="GET" action="{{ route('clientes.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted">ESTADO DE COBRO</label>
                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Todos los Estados --</option>
                    <option value="PENDIENTE" {{ request('estado') == 'PENDIENTE' ? 'selected' : '' }}>⏳ Pendiente</option>
                    <option value="CON_ADELANTO" {{ request('estado') == 'CON_ADELANTO' ? 'selected' : '' }}>💳 Con Adelanto</option>
                    <option value="PAGADO" {{ request('estado') == 'PAGADO' ? 'selected' : '' }}>✅ Cobrado / Pagado</option>
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
                <a href="{{ route('clientes.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla Principal de Facturas de Clientes -->
<div class="msa-card">
    <div class="msa-card-header">
        <span><i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i> Comprobantes Emitidos a Clientes ({{ $comprobantes->count() }})</span>
    </div>
    <div class="msa-card-body p-3">
        <table id="tablaClientes" class="table table-hover align-middle w-100">
            <thead class="table-light small text-muted text-uppercase">
                <tr>
                    <th>Semáforo</th>
                    <th>Empresa / Sede</th>
                    <th>Cliente</th>
                    <th>Comprobante</th>
                    <th>Área</th>
                    <th>Contacto / Cobranza</th>
                    <th>Emisión / Vence</th>
                    <th class="text-end">Monto Total</th>
                    <th class="text-end">Adelantado</th>
                    <th class="text-end">Saldo x Cobrar</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comprobantes as $comp)
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
                            <div class="fw-bold text-dark">{{ $comp->cliente->razon_social_nombre }}</div>
                            <small class="text-muted font-monospace">{{ $comp->cliente->tipo_documento }}: {{ $comp->cliente->numero_documento }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace">
                                {{ $comp->tipo_comprobante }} {{ $comp->serie_correlativo }}
                            </span>
                            @if($comp->descripcion)
                                <div class="small text-muted text-truncate" style="max-width: 170px;" title="{{ $comp->descripcion }}">
                                    {{ $comp->descripcion }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border">
                                {{ $comp->area->nombre_area }}
                            </span>
                        </td>
                        <td>
                            @if(!empty($comp->cliente->telefono))
                                <div class="d-flex align-items-center gap-1">
                                    <span class="small font-monospace">{{ $comp->cliente->telefono }}</span>
                                    @if($comp->saldo_pendiente > 0 && !empty($comp->whatsapp_link))
                                        <a href="{{ $comp->whatsapp_link }}" target="_blank" class="btn btn-sm btn-success py-0 px-2" title="Enviar recordatorio de cobro por WhatsApp">
                                            <i class="fa-brands fa-whatsapp"></i> Cobrar
                                        </a>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted small">Sin teléfono</span>
                            @endif
                        </td>
                        <td class="small">
                            <div>E: {{ $comp->fecha_emision->format('d/m/Y') }}</div>
                            <div class="fw-semibold text-danger">V: {{ $comp->fecha_vencimiento->format('d/m/Y') }}</div>
                        </td>
                        <td class="text-end fw-bold text-dark">
                            {{ $comp->moneda === 'USD' ? '$' : 'S/' }} {{ number_format($comp->monto_total, 2) }}
                        </td>
                        <td class="text-end fw-semibold text-success">
                            {{ $comp->moneda === 'USD' ? '$' : 'S/' }} {{ number_format($comp->monto_cobrado, 2) }}
                        </td>
                        <td class="text-end fw-bold {{ $comp->saldo_pendiente > 0 ? 'text-primary' : 'text-muted' }}">
                            {{ $comp->moneda === 'USD' ? '$' : 'S/' }} {{ number_format($comp->saldo_pendiente, 2) }}
                        </td>
                        <td>
                            @if($comp->estado_pago === 'PAGADO')
                                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> COBRADO</span>
                            @elseif($comp->estado_pago === 'CON_ADELANTO')
                                <span class="badge bg-info text-dark"><i class="fa-solid fa-clock-rotate-left me-1"></i> CON ADELANTO</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="fa-regular fa-hourglass me-1"></i> PENDIENTE</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="abrirModalAbonosVenta({{ $comp->id }})" title="Ver / Registrar Cobros">
                                    <i class="fa-solid fa-hand-holding-dollar me-1"></i> Cobros ({{ $comp->pagos->count() }})
                                </button>
                                <form action="{{ route('clientes.destroy', $comp->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este comprobante de venta?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
