@extends('layouts.app')

@section('title', 'Reporte Contable - Cuentas por Cobrar')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i> Reporte Consolidado: Cuentas por Cobrar (Clientes)
        </h4>
        <p class="text-muted small mb-0">Exportación a Excel / PDF e impresión de estados de cobranzas y deudas de clientes.</p>
    </div>
</div>

<!-- Filtros del Reporte -->
<div class="msa-card mb-4">
    <div class="msa-card-body p-3">
        <form method="GET" action="{{ route('reportes.cxc') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted">ESTADO</label>
                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Todos --</option>
                    <option value="PENDIENTE" {{ request('estado') == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                    <option value="CON_ADELANTO" {{ request('estado') == 'CON_ADELANTO' ? 'selected' : '' }}>Con Adelanto</option>
                    <option value="PAGADO" {{ request('estado') == 'PAGADO' ? 'selected' : '' }}>Cobrado / Pagado</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted">ÁREA</label>
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
                <label class="form-label small fw-bold text-muted">DESDE</label>
                <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-muted">HASTA</label>
                <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> Filtrar</button>
                <a href="{{ route('reportes.cxc') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla del Reporte -->
<div class="msa-card">
    <div class="msa-card-body p-4">
        <!-- Membrete -->
        <div class="border-bottom pb-3 mb-4 text-center">
            <h4 class="fw-bold mb-1">{{ $empresaActiva->razon_social ?? 'GRUPO EMPRESARIAL MSA' }}</h4>
            <div class="small text-muted">
                RUC: {{ $empresaActiva->ruc ?? '20601234567' }} | Sede: {{ $sedeActiva->nombre ?? 'Consolidado General' }} | Fecha de Emisión: {{ date('d/m/Y H:i') }}
            </div>
            <h5 class="fw-bold mt-2 text-primary">ESTADO DE CUENTAS POR COBRAR A CLIENTES (CARTERA)</h5>
        </div>

        <table id="tablaReporteCxC" class="table table-bordered table-striped align-middle w-100">
            <thead class="table-dark small text-uppercase">
                <tr>
                    <th>#</th>
                    <th>Empresa / Sede</th>
                    <th>Doc / Cliente</th>
                    <th>Teléfono</th>
                    <th>Comprobante</th>
                    <th>Área</th>
                    <th>F. Emisión</th>
                    <th>F. Venc.</th>
                    <th class="text-end">Monto Total</th>
                    <th class="text-end">Adelanto Cobrado</th>
                    <th class="text-end">Saldo Pendiente</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $sumTotal = 0;
                    $sumCobrado = 0;
                    $sumSaldo = 0;
                @endphp
                @foreach($comprobantes as $i => $comp)
                    @php
                        $sumTotal += $comp->monto_total;
                        $sumCobrado += $comp->monto_cobrado;
                        $sumSaldo += $comp->saldo_pendiente;
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="small">{{ $comp->empresa->nombre_comercial ?? $comp->empresa->razon_social }} - {{ $comp->sede->nombre }}</td>
                        <td>
                            <div class="fw-bold">{{ $comp->cliente->razon_social_nombre }}</div>
                            <small class="text-muted font-monospace">{{ $comp->cliente->numero_documento }}</small>
                        </td>
                        <td class="small font-monospace">{{ $comp->cliente->telefono ?? '-' }}</td>
                        <td class="font-monospace">{{ $comp->tipo_comprobante }} {{ $comp->serie_correlativo }}</td>
                        <td>{{ $comp->area->nombre_area }}</td>
                        <td class="small">{{ $comp->fecha_emision->format('d/m/Y') }}</td>
                        <td class="small {{ $comp->dias_restantes < 0 && $comp->saldo_pendiente > 0 ? 'text-danger fw-bold' : '' }}">
                            {{ $comp->fecha_vencimiento->format('d/m/Y') }}
                        </td>
                        <td class="text-end fw-bold">{{ $comp->moneda }} {{ number_format($comp->monto_total, 2) }}</td>
                        <td class="text-end text-success fw-semibold">{{ $comp->moneda }} {{ number_format($comp->monto_cobrado, 2) }}</td>
                        <td class="text-end text-primary fw-bold">{{ $comp->moneda }} {{ number_format($comp->saldo_pendiente, 2) }}</td>
                        <td>
                            @if($comp->estado_pago === 'PAGADO')
                                <span class="badge bg-success">COBRADO</span>
                            @elseif($comp->estado_pago === 'CON_ADELANTO')
                                <span class="badge bg-info text-dark">CON ADELANTO</span>
                            @else
                                <span class="badge bg-warning text-dark">PENDIENTE</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="8" class="text-end text-uppercase">Totales Cartera:</td>
                    <td class="text-end">S/ {{ number_format($sumTotal, 2) }}</td>
                    <td class="text-end text-success">S/ {{ number_format($sumCobrado, 2) }}</td>
                    <td class="text-end text-primary">S/ {{ number_format($sumSaldo, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
