@extends('layouts.app')

@section('title', 'Registrar Factura de Proveedor')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">
            <i class="fa-solid fa-file-circle-plus text-danger me-2"></i> Nueva Factura / Gasto de Proveedor
        </h4>
        <p class="text-muted small mb-0">Registre la compra, gasto o servicio asignándolo a su respectiva empresa, sede y área.</p>
    </div>
    <div>
        <a href="{{ route('proveedores.index') }}" class="btn btn-sm btn-outline-secondary">
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

<form action="{{ route('proveedores.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- Columna Izquierda: Datos de Empresa, Sede y Proveedor -->
        <div class="col-12 col-lg-6">
            <!-- Asignación Corporativa -->
            <div class="msa-card mb-4">
                <div class="msa-card-header bg-light">
                    <span class="text-primary"><i class="fa-solid fa-building me-2"></i> 1. Asignación de Empresa y Sede</span>
                </div>
                <div class="msa-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">EMPRESA COMPRADORA <span class="text-danger">*</span></label>
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

            <!-- Datos del Proveedor con Consulta SUNAT -->
            <div class="msa-card">
                <div class="msa-card-header bg-light d-flex justify-content-between align-items-center">
                    <span class="text-dark"><i class="fa-solid fa-truck me-2"></i> 2. Datos del Proveedor</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Consulta SUNAT Oficial</span>
                </div>
                <div class="msa-card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">RUC DEL PROVEEDOR (11 DÍGITOS) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="ruc" id="inputRuc" class="form-control font-monospace fw-bold" maxlength="11" value="{{ old('ruc') }}" placeholder="Ej: 20512345678" required>
                            <button type="button" class="btn btn-primary" id="btnBuscarSunat" onclick="buscarProveedorSunat()">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Buscar SUNAT
                            </button>
                        </div>
                        <small id="sunatMensaje" class="form-text"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">RAZÓN SOCIAL <span class="text-danger">*</span></label>
                        <input type="text" name="razon_social" id="inputRazonSocial" class="form-control form-control-sm fw-semibold" value="{{ old('razon_social') }}" placeholder="Nombre fiscal de la empresa proveedora" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">DIRECCIÓN FISCAL</label>
                        <input type="text" name="direccion" id="inputDireccion" class="form-control form-control-sm" value="{{ old('direccion') }}" placeholder="Av. / Jr. / Calle...">
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">TELÉFONO DE CONTACTO</label>
                            <input type="text" name="telefono" id="inputTelefono" class="form-control form-control-sm" value="{{ old('telefono') }}" placeholder="Teléfono o celular">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">CORREO ELECTRÓNICO</label>
                            <input type="email" name="correo" id="inputCorreo" class="form-control form-control-sm" value="{{ old('correo') }}" placeholder="pagos@proveedor.pe">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Datos del Comprobante y Adelanto -->
        <div class="col-12 col-lg-6">
            <div class="msa-card mb-4">
                <div class="msa-card-header bg-light">
                    <span class="text-danger"><i class="fa-solid fa-receipt me-2"></i> 3. Datos de la Factura / Comprobante</span>
                </div>
                <div class="msa-card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">TIPO COMPROBANTE <span class="text-danger">*</span></label>
                            <select name="tipo_comprobante" class="form-select form-select-sm" required>
                                <option value="FACTURA" {{ old('tipo_comprobante') == 'FACTURA' ? 'selected' : '' }}>Factura</option>
                                <option value="BOLETA" {{ old('tipo_comprobante') == 'BOLETA' ? 'selected' : '' }}>Boleta de Venta</option>
                                <option value="RECIBO_HONORARIOS" {{ old('tipo_comprobante') == 'RECIBO_HONORARIOS' ? 'selected' : '' }}>Recibo por Honorarios</option>
                                <option value="NOTA_VENTA" {{ old('tipo_comprobante') == 'NOTA_VENTA' ? 'selected' : '' }}>Nota de Venta / Cotización</option>
                                <option value="OTRO" {{ old('tipo_comprobante') == 'OTRO' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">SERIE Y CORRELATIVO <span class="text-danger">*</span></label>
                            <input type="text" name="serie_correlativo" class="form-control form-control-sm font-monospace text-uppercase" value="{{ old('serie_correlativo') }}" placeholder="Ej: F001-0004523" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">ÁREA / CENTRO DE COSTO <span class="text-danger">*</span></label>
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
                            <label class="form-label small fw-bold text-muted">MONTO TOTAL <span class="text-danger">*</span></label>
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
                            <input type="date" name="fecha_vencimiento" class="form-control form-control-sm fw-semibold text-danger" value="{{ old('fecha_vencimiento', date('Y-m-d', strtotime('+30 days'))) }}" required>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted">DESCRIPCIÓN DEL BIEN O SERVICIO (OPCIONAL)</label>
                        <textarea name="descripcion" rows="2" class="form-control form-control-sm" placeholder="Detalle del repuesto, reparación, insumo o servicio adquirido...">{{ old('descripcion') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Sección de Adelanto Inicial Opcional -->
            <div class="msa-card border-success border-opacity-50">
                <div class="msa-card-header bg-success bg-opacity-10 d-flex justify-content-between align-items-center">
                    <span class="text-success fw-bold"><i class="fa-solid fa-money-bill-transfer me-2"></i> 4. ¿Se realizó un adelanto o pago inicial?</span>
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
                                <option value="CHEQUE">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">BANCO</label>
                            <input type="text" name="banco" class="form-control form-control-sm" value="{{ old('banco') }}" placeholder="Ej: BCP, BBVA, Interbank...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">NRO. OPERACIÓN / VOUCHER</label>
                            <input type="text" name="nro_operacion" class="form-control form-control-sm" value="{{ old('nro_operacion') }}" placeholder="OP-1234567">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('proveedores.index') }}" class="btn btn-outline-secondary px-4">Cancelar</a>
                <button type="submit" class="btn btn-danger px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Factura de Proveedor
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/proveedores.js') }}"></script>
@endpush
