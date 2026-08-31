@extends('layouts.app')

@section('title', 'Gestión de Empresas')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Empresas</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-building text-primary me-2"></i> Gestión de Empresas
        </h4>
        <p class="text-muted small mb-0">Administración de razones sociales, sedes, configuración contable y cuentas bancarias.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sedes.index') }}" class="btn btn-outline-secondary shadow-sm fw-semibold">
            <i class="fa-solid fa-location-dot me-1"></i> Ver Todas las Sedes
        </a>
        <a href="{{ route('empresas.create') }}" class="btn btn-primary shadow-sm fw-semibold">
            <i class="fa-solid fa-plus-circle me-1"></i> Nueva Empresa
        </a>
    </div>
</div>

<!-- Resumen Estadístico de Empresas -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card border-primary border-opacity-25 bg-primary bg-opacity-10">
            <div class="text-primary small fw-bold text-uppercase">Total Empresas</div>
            <div class="fs-4 fw-bold text-primary mt-1">{{ $totalEmpresas }}</div>
            <small class="text-muted">Empresas registradas en el sistema</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card border-success border-opacity-25 bg-success bg-opacity-10">
            <div class="text-success small fw-bold text-uppercase">Empresas Activas</div>
            <div class="fs-4 fw-bold text-success mt-1">{{ $totalEmpresasActivas }}</div>
            <small class="text-muted">Disponibles para emisión y recepción</small>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card border-info border-opacity-25 bg-info bg-opacity-10">
            <div class="text-info-emphasis small fw-bold text-uppercase">Sedes Totales</div>
            <div class="fs-4 fw-bold text-info mt-1">{{ $totalSedes }}</div>
            <small class="text-muted">Sucursales y talleres vinculados</small>
        </div>
    </div>
</div>

<!-- Filtros y Búsqueda -->
<div class="msa-card mb-4">
    <div class="msa-card-body p-3">
        <form method="GET" action="{{ route('empresas.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold text-muted">BUSCAR EMPRESA</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="RUC, Razón Social, Nombre Comercial, Teléfono..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted">ESTADO</label>
                <select name="estado" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Todos los estados --</option>
                    <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>✅ Solo Activas</option>
                    <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>⛔ Inactivas</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold">
                    <i class="fa-solid fa-filter me-1"></i> Filtrar
                </button>
                @if(request()->hasAny(['search', 'estado']))
                    <a href="{{ route('empresas.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpiar Filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Empresas -->
<div class="msa-card">
    <div class="msa-card-header bg-light d-flex align-items-center justify-content-between">
        <span class="fw-bold text-dark"><i class="fa-solid fa-list me-2 text-primary"></i> Directorio de Empresas</span>
        <span class="badge bg-primary rounded-pill">{{ $empresas->count() }} registradas</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tablaEmpresas">
            <thead class="table-light">
                <tr>
                    <th style="min-width: 250px;">Empresa / Razón Social</th>
                    <th>RUC</th>
                    <th>Contacto & Dirección</th>
                    <th class="text-center">Sedes</th>
                    <th class="text-center">Comprobantes</th>
                    <th class="text-center">Alerta Días</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end" style="min-width: 140px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($empresas as $emp)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @if($emp->logo_url && file_exists(public_path($emp->logo_url)))
                                    <img src="{{ asset($emp->logo_url) }}" alt="Logo" class="rounded border shadow-sm" style="width: 44px; height: 44px; object-fit: contain; background: #fff; padding: 2px;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 44px; height: 44px; font-weight: 800; font-size: 1.1rem; background: linear-gradient(135deg, #1e3a8a, #3b82f6) !important;">
                                        {{ strtoupper(substr($emp->razon_social, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('empresas.show', $emp->id) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                        {{ $emp->razon_social }}
                                    </a>
                                    @if($emp->nombre_comercial && $emp->nombre_comercial !== $emp->razon_social)
                                        <div class="text-muted small"><i class="fa-solid fa-tag me-1 text-primary"></i>{{ $emp->nombre_comercial }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace fs-6 px-2 py-1">
                                <i class="fa-regular fa-id-card me-1 text-primary"></i>{{ $emp->ruc }}
                            </span>
                        </td>
                        <td>
                            <div class="small">
                                @if($emp->direccion)
                                    <div class="text-truncate text-muted" style="max-width: 260px;" title="{{ $emp->direccion }}">
                                        <i class="fa-solid fa-location-dot text-danger me-1"></i> {{ $emp->direccion }}
                                    </div>
                                @endif
                                @if($emp->telefono)
                                    <div class="text-muted"><i class="fa-solid fa-phone text-success me-1"></i> {{ $emp->telefono }}</div>
                                @endif
                                @if($emp->correo)
                                    <div class="text-muted"><i class="fa-regular fa-envelope text-info me-1"></i> {{ $emp->correo }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('empresas.show', $emp->id) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none px-2 py-1 fs-7" title="Ver Sedes de esta empresa">
                                <i class="fa-solid fa-location-dot me-1"></i> {{ $emp->sedes_count }} {{ $emp->sedes_count == 1 ? 'Sede' : 'Sedes' }}
                            </a>
                        </td>
                        <td class="text-center">
                            <div class="small">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="Comprobantes de Compra">
                                    <i class="fa-solid fa-truck-field me-1"></i> {{ $emp->comprobantes_compras_count }} CxP
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" title="Comprobantes de Venta">
                                    <i class="fa-solid fa-users me-1"></i> {{ $emp->comprobantes_ventas_count }} CxC
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                <i class="fa-regular fa-bell text-warning me-1"></i> {{ $emp->dias_alerta_vencimiento }} días
                            </span>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('empresas.toggle_status', $emp->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm border-0 p-0" title="Click para cambiar estado">
                                    @if($emp->activo)
                                        <span class="badge bg-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> Activo</span>
                                    @else
                                        <span class="badge bg-secondary px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i> Inactivo</span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('empresas.show', $emp->id) }}" class="btn btn-light border text-primary" title="Ver Detalle y Sedes">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('empresas.edit', $emp->id) }}" class="btn btn-light border text-dark" title="Editar Empresa">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <button type="button" class="btn btn-light border text-danger btn-eliminar-empresa" 
                                        data-id="{{ $emp->id }}" 
                                        data-name="{{ $emp->razon_social }}" 
                                        data-comprobantes="{{ $emp->comprobantes_compras_count + $emp->comprobantes_ventas_count }}"
                                        title="Eliminar Empresa">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                            <form id="delete-empresa-form-{{ $emp->id }}" action="{{ route('empresas.destroy', $emp->id) }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-building-circle-xmark fs-1 d-block mb-3 text-secondary opacity-50"></i>
                            <h6 class="fw-bold">No se encontraron empresas registradas</h6>
                            <p class="small mb-3">Comience registrando su primera empresa para la facturación.</p>
                            <a href="{{ route('empresas.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-plus-circle me-1"></i> Registrar Primera Empresa
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
        // Manejador de confirmación para eliminar empresa
        document.querySelectorAll('.btn-eliminar-empresa').forEach(button => {
            button.addEventListener('click', function () {
                const empresaId = this.dataset.id;
                const empresaName = this.dataset.name;
                const comprobantes = parseInt(this.dataset.comprobantes || 0);

                if (comprobantes > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No se puede eliminar',
                        html: `La empresa <b>${empresaName}</b> tiene <b>${comprobantes} comprobante(s)</b> contables asociados.<br><br>Para no usarla en las operaciones, utilice la opción de <b>Desactivar</b>.`,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#1e3a8a'
                    });
                    return;
                }

                Swal.fire({
                    title: '¿Eliminar Empresa?',
                    html: `¿Está seguro de que desea eliminar la empresa <b>${empresaName}</b>?<br><span class="text-danger small">Se eliminarán también sus sedes asociadas. Esta acción no se puede deshacer.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fa-solid fa-trash me-1"></i> Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-empresa-form-${empresaId}`).submit();
                    }
                });
            });
        });
    });
</script>
@endpush
