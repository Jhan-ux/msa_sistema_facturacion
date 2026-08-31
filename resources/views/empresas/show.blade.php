@extends('layouts.app')

@section('title', $empresa->nombre_comercial ?? $empresa->razon_social)

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('empresas.index') }}" class="text-decoration-none text-muted">Empresas</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">{{ $empresa->nombre_comercial ?? $empresa->razon_social }}</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-building text-primary me-2"></i> {{ $empresa->razon_social }}
        </h4>
        @if($empresa->nombre_comercial && $empresa->nombre_comercial !== $empresa->razon_social)
            <p class="text-muted small mb-0"><i class="fa-solid fa-tag me-1 text-primary"></i> Nombre Comercial: <b>{{ $empresa->nombre_comercial }}</b></p>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('empresas.edit', $empresa->id) }}" class="btn btn-warning shadow-sm fw-semibold">
            <i class="fa-solid fa-pen-to-square me-1"></i> Editar Empresa
        </a>
        <a href="{{ route('sedes.create', ['empresa_id' => $empresa->id]) }}" class="btn btn-primary shadow-sm fw-semibold">
            <i class="fa-solid fa-plus-circle me-1"></i> Añadir Sede
        </a>
        <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary shadow-sm fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<!-- Tarjetas Resumen de Métricas -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
        <div class="stat-card border-primary border-opacity-25 bg-primary bg-opacity-10">
            <div class="text-primary small fw-bold text-uppercase">Sedes Operativas</div>
            <div class="fs-4 fw-bold text-primary mt-1">{{ $empresa->sedes->count() }}</div>
            <small class="text-muted">{{ $empresa->sedes->where('activo', true)->count() }} activas para facturación</small>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="stat-card border-danger border-opacity-25 bg-danger bg-opacity-10">
            <div class="text-danger small fw-bold text-uppercase">Compras Registradas</div>
            <div class="fs-4 fw-bold text-danger mt-1">{{ $empresa->comprobantes_compras_count }}</div>
            <small class="text-muted">Total facturado: S/ {{ number_format($totalComprasMonto, 2) }}</small>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="stat-card border-success border-opacity-25 bg-success bg-opacity-10">
            <div class="text-success small fw-bold text-uppercase">Ventas Registradas</div>
            <div class="fs-4 fw-bold text-success mt-1">{{ $empresa->comprobantes_ventas_count }}</div>
            <small class="text-muted">Total facturado: S/ {{ number_format($totalVentasMonto, 2) }}</small>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="stat-card border-warning border-opacity-25 bg-warning bg-opacity-10">
            <div class="text-dark small fw-bold text-uppercase">Alerta Vencimiento</div>
            <div class="fs-4 fw-bold text-dark mt-1">{{ $empresa->dias_alerta_vencimiento }} días</div>
            <small class="text-muted">Ventana previa de aviso</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Ficha Técnica de la Empresa -->
    <div class="col-12 col-lg-5">
        <div class="msa-card mb-4">
            <div class="msa-card-header bg-light d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark"><i class="fa-solid fa-id-card text-primary me-2"></i> Ficha de Empresa</span>
                <form action="{{ route('empresas.toggle_status', $empresa->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm border-0 p-0" title="Click para alternar estado">
                        @if($empresa->activo)
                            <span class="badge bg-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> Empresa Activa</span>
                        @else
                            <span class="badge bg-secondary px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i> Empresa Inactiva</span>
                        @endif
                    </button>
                </form>
            </div>
            <div class="msa-card-body p-4">
                @if($empresa->logo_url && file_exists(public_path($empresa->logo_url)))
                    <div class="text-center mb-4 p-3 bg-light border rounded">
                        <img src="{{ asset($empresa->logo_url) }}" alt="Logo {{ $empresa->razon_social }}" class="img-fluid rounded" style="max-height: 90px; object-fit: contain;">
                    </div>
                @endif

                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-regular fa-id-badge text-primary me-2"></i> RUC:</span>
                        <span class="font-monospace fw-bold fs-6 text-dark">{{ $empresa->ruc }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-building text-primary me-2"></i> Razón Social:</span>
                        <span class="fw-semibold text-dark text-end ms-2">{{ $empresa->razon_social }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-location-dot text-danger me-2"></i> Dirección Fiscal:</span>
                        <span class="text-dark text-end ms-2">{{ $empresa->direccion ?: 'No registrada' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-solid fa-phone text-success me-2"></i> Teléfono:</span>
                        <span class="text-dark fw-semibold">{{ $empresa->telefono ?: 'No registrado' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fa-regular fa-envelope text-info me-2"></i> Correo Electrónico:</span>
                        <span class="text-dark">{{ $empresa->correo ?: 'No registrado' }}</span>
                    </li>
                </ul>

                <hr class="my-3">

                <!-- Cuentas Bancarias Registradas -->
                <div class="mb-2">
                    <label class="form-label small fw-bold text-dark text-uppercase mb-2">
                        <i class="fa-solid fa-money-check-dollar text-success me-1"></i> Cuentas Bancarias Oficiales:
                    </label>
                    @if($empresa->cuentas_bancarias)
                        <div class="p-3 bg-light border rounded font-monospace small text-dark" style="white-space: pre-line; line-height: 1.6;">{{ $empresa->cuentas_bancarias }}</div>
                    @else
                        <div class="p-3 bg-light border rounded small text-muted text-center">
                            No se han registrado cuentas bancarias para esta empresa.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sedes de la Empresa -->
    <div class="col-12 col-lg-7">
        <div class="msa-card">
            <div class="msa-card-header bg-light d-flex align-items-center justify-content-between">
                <span class="fw-bold text-dark">
                    <i class="fa-solid fa-location-dot text-primary me-2"></i> Sedes & Sucursales Vinculadas
                </span>
                <a href="{{ route('sedes.create', ['empresa_id' => $empresa->id]) }}" class="btn btn-sm btn-primary shadow-sm fw-semibold">
                    <i class="fa-solid fa-plus-circle me-1"></i> Nueva Sede
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th>Sede / Nombre</th>
                            <th>Código</th>
                            <th>Ciudad / Dirección</th>
                            <th class="text-center">Comprobantes</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($empresa->sedes as $sede)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $sede->nombre }}</div>
                                    @if($sede->telefono)
                                        <div class="small text-muted"><i class="fa-solid fa-phone me-1 text-success"></i>{{ $sede->telefono }}</div>
                                    @endif
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
                                        <div class="fw-semibold text-dark">{{ $sede->ciudad ?: 'Lima' }}</div>
                                        <div class="text-muted text-truncate" style="max-width: 200px;" title="{{ $sede->direccion }}">
                                            {{ $sede->direccion ?: 'Sin dirección' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border small" title="Comprobantes asociados">
                                        {{ $sede->comprobantes_compras_count + $sede->comprobantes_ventas_count }} regs
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('sedes.toggle_status', $sede->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm border-0 p-0" title="Click para alternar estado">
                                            @if($sede->activo)
                                                <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Activa</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="fa-solid fa-circle-xmark me-1"></i> Inactiva</span>
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
                                    <form id="delete-sede-form-{{ $sede->id }}" action="{{ route('sedes.destroy', $sede->id) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-location-dot fs-3 d-block mb-2 text-secondary opacity-50"></i>
                                    <span class="small">Esta empresa no tiene sedes asignadas.</span><br>
                                    <a href="{{ route('sedes.create', ['empresa_id' => $empresa->id]) }}" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fa-solid fa-plus-circle me-1"></i> Registrar Primera Sede
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Manejador de eliminación de sede
        document.querySelectorAll('.btn-eliminar-sede').forEach(button => {
            button.addEventListener('click', function () {
                const sedeId = this.dataset.id;
                const sedeName = this.dataset.name;
                const comprobantes = parseInt(this.dataset.comprobantes || 0);

                if (comprobantes > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No se puede eliminar',
                        html: `La sede <b>${sedeName}</b> tiene <b>${comprobantes} comprobante(s)</b> vinculados.<br><br>Para ocultarla de las listas operativas, utilice la opción de <b>Desactivar</b>.`,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#1e3a8a'
                    });
                    return;
                }

                Swal.fire({
                    title: '¿Eliminar Sede?',
                    html: `¿Está seguro de eliminar la sede <b>${sedeName}</b>? Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fa-solid fa-trash me-1"></i> Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-sede-form-${sedeId}`).submit();
                    }
                });
            });
        });
    });
</script>
@endpush
