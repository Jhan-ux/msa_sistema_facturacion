@extends('layouts.app')

@section('title', 'Editar Sede: ' . $sede->nombre)

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sedes.index') }}" class="text-decoration-none text-muted">Sedes</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Editar</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Editar Sede o Sucursal
        </h4>
        <p class="text-muted small mb-0">Actualice la información de contacto, ubicación y empresa asignada a la sede.</p>
    </div>
    <div>
        <a href="{{ route('sedes.index', ['empresa_id' => $sede->empresa_id]) }}" class="btn btn-outline-secondary shadow-sm fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a Sedes
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="msa-card">
            <div class="msa-card-header bg-light">
                <span class="fw-bold text-dark"><i class="fa-solid fa-store text-primary me-2"></i> Datos de la Sede</span>
            </div>
            <div class="msa-card-body p-4">
                <form action="{{ route('sedes.update', $sede->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if(request()->has('redirect_to_empresa'))
                        <input type="hidden" name="redirect_to_empresa" value="1">
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-circle-exclamation fs-5"></i>
                                <strong>Por favor corrija los siguientes errores:</strong>
                            </div>
                            <ul class="mb-0 small ps-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row g-3">
                        <!-- Empresa Asignada -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">EMPRESA ASOCIADA <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-building text-primary"></i></span>
                                <select name="empresa_id" class="form-select @error('empresa_id') is-invalid @enderror" required>
                                    @foreach($empresas as $emp)
                                        <option value="{{ $emp->id }}" {{ (old('empresa_id', $sede->empresa_id) == $emp->id) ? 'selected' : '' }}>
                                            {{ $emp->razon_social }} @if($emp->nombre_comercial) ({{ $emp->nombre_comercial }}) @endif - RUC: {{ $emp->ruc }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('empresa_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nombre de Sede -->
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-bold text-dark">NOMBRE DE LA SEDE <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-tag text-muted"></i></span>
                                <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                                       placeholder="Ej: Sede Principal - La Victoria" value="{{ old('nombre', $sede->nombre) }}" required>
                            </div>
                            @error('nombre')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Código Interno -->
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-dark">CÓDIGO INTERNO</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-barcode text-muted"></i></span>
                                <input type="text" name="codigo" class="form-control font-monospace text-uppercase @error('codigo') is-invalid @enderror" 
                                       placeholder="Ej: SED-01" value="{{ old('codigo', $sede->codigo) }}">
                            </div>
                        </div>

                        <!-- Ciudad -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">CIUDAD / DISTRITO</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-city text-info"></i></span>
                                <input type="text" name="ciudad" class="form-control @error('ciudad') is-invalid @enderror" 
                                       placeholder="Lima, Arequipa, Trujillo..." value="{{ old('ciudad', $sede->ciudad) }}">
                            </div>
                        </div>

                        <!-- Teléfono -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">TELÉFONO DE CONTACTO</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-phone text-success"></i></span>
                                <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" 
                                       placeholder="987654321 / (01) 472-8990" value="{{ old('telefono', $sede->telefono) }}">
                            </div>
                        </div>

                        <!-- Dirección Física -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">DIRECCIÓN COMPLETA</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-location-dot text-danger"></i></span>
                                <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" 
                                       placeholder="Av. Nicolás Arriola 1420, Urb. Santa Catalina" value="{{ old('direccion', $sede->direccion) }}">
                            </div>
                        </div>

                        <!-- Switch Activo -->
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="activo" id="checkActivo" value="1" {{ old('activo', $sede->activo ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark" for="checkActivo">
                                    Sede Activa para Operaciones Contables
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Botones de Acción -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('sedes.index', ['empresa_id' => $sede->empresa_id]) }}" class="btn btn-light border text-muted">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
