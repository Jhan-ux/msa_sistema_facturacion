@extends('layouts.app')

@section('title', 'Registrar Factura / Venta a Cliente')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-file-circle-plus text-primary me-2"></i> Nueva Factura / Venta a Cliente
        </h4>
        <p class="text-muted small mb-0">Emisión o registro de comprobante de venta, cobranza a crédito y registro de anticipos.</p>
    </div>
    <div>
        <a href="{{ route('clientes.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver al Listado
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Por favor verifique los siguientes errores:</div>
        <ul class="mb-0 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('clientes.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- Columna Izquierda: Empresa, Sede y Cliente con SUNAT/RENIEC -->
        <div class="col-12 col-lg-6">
            <!-- Asignación de Empresa y Sede -->
            <div class="msa-card mb-4">
                <div class="msa-card-header bg-light">
                    <span class="text-primary"><i class="fa-solid fa-building me-2"></i> 1. Asignación de Empresa Emisora</span>
                </div>
                <div class="msa-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">EMPRESA EMISORA <span class="text-danger">*</span></label>
                            <select name="empresa_id" id="selectEmpresa" class="form-select form-select-sm" required onchange="filtrarSedesPorEmpresa()">
                                @foreach($empresas as $emp)
                                    <option value="{{ $emp->id }}" {{ old('empresa_id', $empresaId) == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->nombre_comercial ?? $emp->razon_social }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">SEDE / SUCURSAL <span class="text-danger">*</span></label>
                            <select name="sede_id" id="selectSede" class="form-select form-select-sm" required>
                                @foreach($sedes as $sed)
                                    <option value="{{ $sed->id }}" data-empresa="{{ $sed->empresa_id }}" {{ old('sede_id', $sedeId) == $sed->id ? 'selected' : '' }}>
                                        {{ $sed->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos del Cliente con Consulta SUNAT / RENIEC -->
            <div class="msa-card">
                <div class="msa-card-header bg-light d-flex justify-content-between align-items-center">
                    <span class="text-dark"><i class="fa-solid fa-user-check me-2"></i> 2. Datos del Cliente</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Consulta Oficial SUNAT / RENIEC</span>
                </div>
                <div class="msa-card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">TIPO DOC. <span class="text-danger">*</span></label>
                            <select name="tipo_documento" id="selectTipoDoc" class="form-select form-select-sm fw-semibold" onchange="cambiarPlaceholderDoc()">
                                <option value="RUC" {{ old('tipo_documento') == 'RUC' ? 'selected' : '' }}>RUC (Empresa)</option>
                                <option value="DNI" {{ old('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI (Persona)</option>
                                <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carnet Ext.</option>
                                <option value="PASAPORTE" {{ old('tipo_documento') == 'PASAPORTE' ? 'selected' : '' }}>Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-muted">NRO. DOCUMENTO <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="numero_documento" id="inputNumDoc" class="form-control font-monospace fw-bold" maxlength="20" value="{{ old('numero_documento') }}" placeholder="Ej: 20498765432" required>
                                <button type="button" class="btn btn-primary" id="btnBuscarDoc" onclick="buscarClienteSunatReniec()">
                                    <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar
                                </button>
                            </div>
                            <small id="sunatMensaje" class="form-text"></small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">RAZÓN SOCIAL / NOMBRE COMPLETO <span class="text-danger">*</span></label>
                        <input type="text" name="razon_social_nombre" id="inputRazonSocial" class="form-control form-control-sm fw-semibold" value="{{ old('razon_social_nombre') }}" placeholder="Nombre fiscal o particular del cliente" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">DIRECCIÓN</label>
                        <input type="text" name="direccion" id="inputDireccion" class="form-control form-control-sm" value="{{ old('direccion') }}" placeholder="Av. / Jr. / Distrito...">
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">TELÉFONO / CELULAR (WHATSAPP)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="fa-brands fa-whatsapp text-success"></i></span>
                                <input type="text" name="telefono" id="inputTelefono" class="form-control" value="{{ old('telefono') }}" placeholder="987654321">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">CORREO ELECTRÓNICO</label>
                            <input type="email" name="correo" id="inputCorreo" class="form-control form-control-sm" value="{{ old('correo') }}" placeholder="cliente@correo.com">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Comprobante y Anticipo -->
        <div class="col-12 col-lg-6">
            <div class="msa-card mb-4">
                <div class="msa-card-header bg-light">
                    <span class="text-primary"><i class="fa-solid fa-receipt me-2"></i> 3. Datos del Comprobante de Venta</span>
                </div>
                <div class="msa-card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">TIPO COMPROBANTE <span class="text-danger">*</span></label>
                            <select name="tipo_comprobante" class="form-select form-select-sm" required>
                                <option value="FACTURA" {{ old('tipo_comprobante') == 'FACTURA' ? 'selected' : '' }}>Factura</option>
                                <option value="BOLETA" {{ old('tipo_comprobante') == 'BOLETA' ? 'selected' : '' }}>Boleta de Venta</option>
                                <option value="COTIZACION" {{ old('tipo_comprobante') == 'COTIZACION' ? 'selected' : '' }}>Cotización / Proforma</option>
                                <option value="NOTA_VENTA" {{ old('tipo_comprobante') == 'NOTA_VENTA' ? 'selected' : '' }}>Nota de Venta</option>
                                <option value="OTRO" {{ old('tipo_comprobante') == 'OTRO' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">SERIE Y CORRELATIVO <span class="text-danger">*</span></label>
                            <input type="text" name="serie_correlativo" class="form-control form-control-sm font-monospace text-uppercase" value="{{ old('serie_correlativo') }}" placeholder="Ej: F001-0000120" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">ÁREA DE ORIGEN <span class="text-danger">*</span></label>
                            <select name="area_id" class="form-select form-select-sm fw-semibold" required>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                        {{ $area->nombre_area }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">MONEDA <span class="text-danger">*</span></label>
                            <select name="moneda" id="selectMoneda" class="form-select form-select-sm fw-bold" required onchange="actualizarMonedaLabel()">
                                <option value="PEN" {{ old('moneda', 'PEN') == 'PEN' ? 'selected' : '' }}>Soles (S/)</option>
                                <option value="USD" {{ old('moneda') == 'USD' ? 'selected' : '' }}>Dólares ($)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">TOTAL A COBRAR <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="monto_total" id="inputMontoTotal" class="form-control form-control-sm fw-bold" value="{{ old('monto_total') }}" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">FECHA DE EMISIÓN <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_emision" class="form-control form-control-sm" value="{{ old('fecha_emision', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">FECHA DE VENCIMIENTO <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_vencimiento" class="form-control form-control-sm fw-semibold text-danger" value="{{ old('fecha_vencimiento', date('Y-m-d', strtotime('+15 days'))) }}" required>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted">DESCRIPCIÓN DEL TRABAJO / REPUESTO</label>
                        <textarea name="descripcion" rows="2" class="form-control form-control-sm" placeholder="Ej: Rectificación de cigüeñal motor Scania, cambio de fajas y juego de pistones...">{{ old('descripcion') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Sección de Anticipo / Adelanto Inicial del Cliente -->
            <div class="msa-card border-success border-opacity-50">
                <div class="msa-card-header bg-success bg-opacity-10 d-flex justify-content-between align-items-center">
                    <span class="text-success fw-bold"><i class="fa-solid fa-hand-holding-dollar me-2"></i> 4. ¿El cliente entregó un adelanto o pago inicial?</span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="tiene_adelanto" id="checkTieneAdelanto" value="1" {{ old('tiene_adelanto') ? 'checked' : '' }} onchange="toggleSeccionAdelanto()">
                    </div>
                </div>
                <div class="msa-card-body" id="bloqueAdelanto" style="display: {{ old('tiene_adelanto') ? 'block' : 'none' }};">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">MONTO DEL ADELANTO (<span id="labelMonedaAdelanto">S/</span>)</label>
                            <input type="number" step="0.01" min="0.01" name="monto_adelanto" id="inputMontoAdelanto" class="form-control form-control-sm fw-bold text-success" value="{{ old('monto_adelanto') }}" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">MÉTODO DE PAGO</label>
                            <select name="metodo_pago" class="form-select form-select-sm">
                                <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                                <option value="YAPE">Yape</option>
                                <option value="PLIN">Plin</option>
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="DEPOSITO">Depósito</option>
                                <option value="TARJETA">Tarjeta</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">BANCO / CUENTA</label>
                            <input type="text" name="banco" class="form-control form-control-sm" value="{{ old('banco') }}" placeholder="Ej: BCP, BBVA, Interbank...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">NRO. OPERACIÓN</label>
                            <input type="text" name="nro_operacion" class="form-control form-control-sm" value="{{ old('nro_operacion') }}" placeholder="OP-987654">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Factura de Cliente
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/clientes.js') }}"></script>
@endpush
