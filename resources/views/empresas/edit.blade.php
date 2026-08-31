@extends('layouts.app')

@section('title', 'Editar Empresa: ' . $empresa->razon_social)

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('empresas.index') }}" class="text-decoration-none text-muted">Empresas</a></li>
                <li class="breadcrumb-item"><a href="{{ route('empresas.show', $empresa->id) }}" class="text-decoration-none text-muted">{{ $empresa->nombre_comercial ?? $empresa->razon_social }}</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Editar</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Editar Empresa
        </h4>
        <p class="text-muted small mb-0">Actualice la información fiscal, canales de contacto y configuración de cobranza.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('empresas.show', $empresa->id) }}" class="btn btn-outline-primary shadow-sm fw-semibold">
            <i class="fa-solid fa-eye me-1"></i> Ver Ficha y Sedes
        </a>
        <a href="{{ route('empresas.index') }}" class="btn btn-outline-secondary shadow-sm fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver al Listado
        </a>
    </div>
</div>

<form action="{{ route('empresas.update', $empresa->id) }}" method="POST" enctype="multipart/form-data" id="formEditarEmpresa">
    @csrf
    @method('PUT')

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

    <div class="row g-4">
        <!-- Columna Izquierda: Datos Fiscales y Contacto -->
        <div class="col-12 col-lg-7">
            <!-- Bloque 1: Identificación Fiscal SUNAT -->
            <div class="msa-card mb-4">
                <div class="msa-card-header bg-light">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-id-card text-primary me-2"></i> Identificación Fiscal (SUNAT)</span>
                </div>
                <div class="msa-card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">NÚMERO DE RUC <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-hashtag text-primary"></i></span>
                                <input type="text" name="ruc" id="rucInput" class="form-control font-monospace @error('ruc') is-invalid @enderror" 
                                       placeholder="20XXXXXXXXX" maxlength="11" value="{{ old('ruc', $empresa->ruc) }}" required autocomplete="off">
                                <button class="btn btn-outline-primary fw-semibold" type="button" id="btnConsultarSunat">
                                    <i class="fa-solid fa-rotate me-1"></i> SUNAT
                                </button>
                            </div>
                            <div id="sunatSpinner" class="small text-primary mt-1 d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span> Consultando actualización en SUNAT...
                            </div>
                            <div id="sunatMsg" class="small mt-1"></div>
                            @error('ruc')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">NOMBRE COMERCIAL</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-tag text-muted"></i></span>
                                <input type="text" name="nombre_comercial" id="nombreComercialInput" class="form-control text-uppercase @error('nombre_comercial') is-invalid @enderror" 
                                       placeholder="Ej: MSA TALLERES" value="{{ old('nombre_comercial', $empresa->nombre_comercial) }}">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">RAZÓN SOCIAL <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-building text-muted"></i></span>
                                <input type="text" name="razon_social" id="razonSocialInput" class="form-control text-uppercase @error('razon_social') is-invalid @enderror" 
                                       placeholder="Ej: MSA SERVICIOS AUTOMOTRICES S.A.C." value="{{ old('razon_social', $empresa->razon_social) }}" required>
                            </div>
                            @error('razon_social')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">DIRECCIÓN FISCAL</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-location-dot text-danger"></i></span>
                                <input type="text" name="direccion" id="direccionInput" class="form-control @error('direccion') is-invalid @enderror" 
                                       placeholder="Ej: Av. Nicolás Arriola 1420, La Victoria, Lima" value="{{ old('direccion', $empresa->direccion) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bloque 2: Datos de Contacto y Comunicación -->
            <div class="msa-card mb-4">
                <div class="msa-card-header bg-light">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-phone-volume text-success me-2"></i> Contacto y Notificaciones</span>
                </div>
                <div class="msa-card-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">TELÉFONO / WHATSAPP</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-phone text-success"></i></span>
                                <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" 
                                       placeholder="(01) 472-8990 / 987654321" value="{{ old('telefono', $empresa->telefono) }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">CORREO ELECTRÓNICO</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-regular fa-envelope text-info"></i></span>
                                <input type="email" name="correo" class="form-control @error('correo') is-invalid @enderror" 
                                       placeholder="administracion@empresa.pe" value="{{ old('correo', $empresa->correo) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Configuración Bancaria, Alertas y Logo -->
        <div class="col-12 col-lg-5">
            <!-- Bloque 3: Cuentas Bancarias Oficiales -->
            <div class="msa-card mb-4">
                <div class="msa-card-header bg-light d-flex align-items-center justify-content-between">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-money-check-dollar text-success me-2"></i> Cuentas Bancarias</span>
                    <span class="badge bg-success-subtle text-success">Cobros WhatsApp</span>
                </div>
                <div class="msa-card-body p-4">
                    <p class="text-muted small mb-2">
                        Estas cuentas aparecerán automáticamente al enviar recordatorios de cobranza por WhatsApp:
                    </p>
                    <textarea name="cuentas_bancarias" class="form-control font-monospace small @error('cuentas_bancarias') is-invalid @enderror" 
                              rows="6" placeholder="• BCP Soles: 191-2345678-0-12&#10;• BBVA Soles: 0011-0123-0100012345">{{ old('cuentas_bancarias', $empresa->cuentas_bancarias) }}</textarea>
                </div>
            </div>

            <!-- Bloque 4: Parámetros Operativos & Logo -->
            <div class="msa-card mb-4">
                <div class="msa-card-header bg-light">
                    <span class="fw-bold text-dark"><i class="fa-solid fa-sliders text-primary me-2"></i> Parámetros & Logo</span>
                </div>
                <div class="msa-card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">DÍAS DE ALERTA DE VENCIMIENTO <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-regular fa-bell text-warning"></i></span>
                            <input type="number" name="dias_alerta_vencimiento" class="form-control" min="1" max="60" value="{{ old('dias_alerta_vencimiento', $empresa->dias_alerta_vencimiento) }}" required>
                            <span class="input-group-text bg-light text-muted">días de anticipación</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">LOGO DE LA EMPRESA</label>
                        @if($empresa->logo_url && file_exists(public_path($empresa->logo_url)))
                            <div class="mb-2 p-2 border rounded bg-light text-center">
                                <img src="{{ asset($empresa->logo_url) }}" alt="Logo actual" class="img-fluid rounded shadow-sm" style="max-height: 60px; object-fit: contain;">
                                <div class="small text-muted mt-1">Logo actual</div>
                            </div>
                        @endif
                        <input type="file" name="logo" id="logoInput" class="form-control @error('logo') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">Subir nuevo archivo reemplazará el logo actual.</small>
                        
                        <div id="logoPreviewContainer" class="mt-2 d-none text-center p-2 border rounded bg-light">
                            <img id="logoPreview" src="#" alt="Nuevo logo" class="img-fluid rounded" style="max-height: 70px; object-fit: contain;">
                            <div class="small text-success mt-1">Nuevo logo a guardar</div>
                        </div>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="activo" id="checkActivo" value="1" {{ old('activo', $empresa->activo ? '1' : '0') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-dark" for="checkActivo">
                            Empresa Activa en el Sistema
                        </label>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Guardar Cambios
                </button>
                <a href="{{ route('empresas.show', $empresa->id) }}" class="btn btn-light border text-muted">
                    Cancelar
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rucInput = document.getElementById('rucInput');
        const btnConsultarSunat = document.getElementById('btnConsultarSunat');
        const sunatSpinner = document.getElementById('sunatSpinner');
        const sunatMsg = document.getElementById('sunatMsg');
        const razonSocialInput = document.getElementById('razonSocialInput');
        const nombreComercialInput = document.getElementById('nombreComercialInput');
        const direccionInput = document.getElementById('direccionInput');
        const logoInput = document.getElementById('logoInput');
        const logoPreview = document.getElementById('logoPreview');
        const logoPreviewContainer = document.getElementById('logoPreviewContainer');

        // Vista previa de logo
        if (logoInput) {
            logoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        logoPreview.src = e.target.result;
                        logoPreviewContainer.classList.remove('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    logoPreviewContainer.classList.add('d-none');
                }
            });
        }

        // Consulta Asíncrona a la API de SUNAT
        btnConsultarSunat.addEventListener('click', function () {
            const ruc = rucInput.value.trim();

            if (ruc.length !== 11 || isNaN(ruc)) {
                sunatMsg.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i> Ingrese un RUC válido de 11 dígitos numéricos.</span>';
                return;
            }

            sunatSpinner.classList.remove('d-none');
            sunatMsg.innerHTML = '';
            btnConsultarSunat.disabled = true;

            fetch(`/api/sunat/ruc/${ruc}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error al consultar SUNAT');
                    }
                    return response.json();
                })
                .then(data => {
                    sunatSpinner.classList.add('d-none');
                    btnConsultarSunat.disabled = false;

                    if (data && (data.razon_social || data.nombre)) {
                        const razonSocial = data.razon_social || data.nombre || '';
                        const direccion = data.direccion || data.direccion_completa || '';
                        const nombreComercial = data.nombre_comercial || '';

                        razonSocialInput.value = razonSocial;
                        if (direccion) direccionInput.value = direccion;
                        if (nombreComercial) nombreComercialInput.value = nombreComercial;

                        sunatMsg.innerHTML = '<span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> Datos actualizados desde SUNAT.</span>';
                    } else {
                        sunatMsg.innerHTML = '<span class="text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i> No se encontraron datos en SUNAT para este RUC.</span>';
                    }
                })
                .catch(err => {
                    sunatSpinner.classList.add('d-none');
                    btnConsultarSunat.disabled = false;
                    sunatMsg.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> No fue posible conectar con el servicio SUNAT.</span>';
                });
        });
    });
</script>
@endpush
