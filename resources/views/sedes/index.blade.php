@extends('layouts.app')

@section('title', 'Gestión de Sedes y Sucursales')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Sedes</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-location-dot text-primary me-2"></i> Gestión de Sedes & Sucursales
        </h4>
        <p class="text-muted small mb-0">Administración de sucursales, locales comerciales y talleres operativos por empresa.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary shadow-sm fw-semibold">
            <i class="fa-solid fa-building me-1"></i> Ver Empresas
        </a>
        <a href="{{ route('sedes.create') }}" class="btn btn-primary shadow-sm fw-semibold">
            <i class="fa-solid fa-plus-circle me-1"></i> Nueva Sede
        </a>
    </div>
</div>

<!-- Resumen Rápido -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card border-primary border-opacity-25 bg-primary bg-opacity-10">
            <div class="text-primary small fw-bold text-uppercase">Total Sedes Registradas</div>
            <div class="fs-4 fw-bold text-primary mt-1">{{ $totalSedes }}</div>
            <small class="text-muted">Distribuidas en todas las empresas</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card border-success border-opacity-25 bg-success bg-opacity-10">
            <div class="text-success small fw-bold text-uppercase">Sedes Activas</div>
            <div class="fs-4 fw-bold text-success mt-1">{{ $totalSedesActivas }}</div>
            <small class="text-muted">Habilitadas para registrar comprobantes</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card border-info border-opacity-25 bg-info bg-opacity-10">
            <div class="text-info-emphasis small fw-bold text-uppercase">Empresas con Sedes</div>
            <div class="fs-4 fw-bold text-info mt-1">{{ $empresas->count() }}</div>
            <small class="text-muted">Empresas en el catálogo</small>
        </div>
    </div>
</div>

<!-- Filtros de Búsqueda -->
<div class="msa-card mb-4">
    <div class="msa-card-body p-3">
        <form method="GET" action="{{ route('sedes.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold text-muted">FILTRAR POR EMPRESA</label>
                <select name="empresa_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">-- Todas las Empresas --</option>
                    @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}" {{ request('empresa_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nombre_comercial ?? $emp->razon_social }} ({{ $emp->ruc }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold text-muted">BUSCAR SEDE</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nombre, código, ciudad, dirección..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-muted">ESTADO</label>
                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Todos --</option>
                    <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activas</option>
                    <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivas</option>
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
                @if(request()->hasAny(['empresa_id', 'search', 'estado']))
                    <a href="{{ route('sedes.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar Filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Sedes -->
<div class="msa-card">
    <div class="msa-card-header bg-light d-flex align-items-center justify-content-between">
        <span class="fw-bold text-dark"><i class="fa-solid fa-list me-2 text-primary"></i> Directorio de Sedes</span>
        <span class="badge bg-primary rounded-pill">{{ $sedes->count() }} sedes</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tablaSedes">
            <thead class="table-light">
                <tr>
                    <th>Empresa Perteneciente</th>
                    <th>Nombre de la Sede</th>
                    <th>Código</th>
                    <th>Ubicación (Ciudad / Dirección)</th>
                    <th>Teléfono</th>
                    <th class="text-center">Comprobantes</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end" style="min-width: 120px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sedes as $sede)
                    <tr>
                        <td>
                            @if($sede->empresa)
                                <a href="{{ route('empresas.show', $sede->empresa_id) }}" class="fw-bold text-primary text-decoration-none">
                                    <i class="fa-solid fa-building me-1"></i> {{ $sede->empresa->nombre_comercial ?? $sede->empresa->razon_social }}
                                </a>
                                <div class="text-muted small font-monospace">RUC: {{ $sede->empresa->ruc }}</div>
                            @else
                                <span class="text-muted small">No asignada</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold text-dark">{{ $sede->nombre }}</span>
                        </td>
                        <td>
                            @if($sede->codigo)
                                <span class="badge bg-light text-dark border font-monospace">{{ $sede->codigo }}</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $sede->ciudad ?: 'Lima' }}</span>
                                @if($sede->direccion)
                                    <div class="text-muted text-truncate mt-1" style="max-width: 250px;" title="{{ $sede->direccion }}">
                                        <i class="fa-solid fa-location-dot text-danger me-1"></i> {{ $sede->direccion }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($sede->telefono)
                                <span class="small text-muted"><i class="fa-solid fa-phone text-success me-1"></i> {{ $sede->telefono }}</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="small">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="Compras">
                                    {{ $sede->comprobantes_compras_count }} CxP
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" title="Ventas">
                                    {{ $sede->comprobantes_ventas_count }} CxC
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('sedes.toggle_status', $sede->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm border-0 p-0" title="Click para alternar estado">
                                    @if($sede->activo)
                                        <span class="badge bg-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> Activa</span>
                                    @else
                                        <span class="badge bg-secondary px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i> Inactiva</span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('sedes.edit', $sede->id) }}" class="btn btn-light border text-dark" title="Editar Sede">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <button type="button" class="btn btn-light border text-danger btn-eliminar-sede" 
                                        data-id="{{ $sede->id }}" 
                                        data-name="{{ $sede->nombre }}" 
                                        data-comprobantes="{{ $sede->comprobantes_compras_count + $sede->comprobantes_ventas_count }}"
                                        title="Eliminar Sede">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                            <form id="delete-sede-table-form-{{ $sede->id }}" action="{{ route('sedes.destroy', $sede->id) }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-location-dot fs-1 d-block mb-3 text-secondary opacity-50"></i>
                            <h6 class="fw-bold">No se encontraron sedes con los filtros seleccionados</h6>
                            <p class="small mb-3">Registre nuevas sedes o sucursales para asociar las operaciones.</p>
                            <a href="{{ route('sedes.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-plus-circle me-1"></i> Registrar Nueva Sede
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-eliminar-sede').forEach(button => {
            button.addEventListener('click', function () {
                const sedeId = this.dataset.id;
                const sedeName = this.dataset.name;
                const comprobantes = parseInt(this.dataset.comprobantes || 0);

                if (comprobantes > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No se puede eliminar',
                        html: `La sede <b>${sedeName}</b> tiene <b>${comprobantes} comprobante(s)</b> contables asociados.<br><br>Para ocultarla de las listas operativas, utilice la opción de <b>Desactivar</b>.`,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#1e3a8a'
                    });
                    return;
                }

                Swal.fire({
                    title: '¿Eliminar Sede?',
                    html: `¿Está seguro de eliminar la sede <b>${sedeName}</b>?<br><span class="text-danger small">Esta acción no se puede deshacer.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fa-solid fa-trash me-1"></i> Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-sede-table-form-${sedeId}`).submit();
                    }
                });
            });
        });
    });
</script>
@endpush
