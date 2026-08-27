<!-- Modal Global de Historial y Registro de Adelantos / Pagos -->
<div class="modal fade" id="modalAbonos" tabindex="-1" aria-labelledby="modalAbonosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <div>
                    <h5 class="modal-title fw-bold" id="modalAbonosLabel">
                        <i class="fa-solid fa-money-bill-wave text-success me-2"></i> Gestión de Adelantos y Pagos
                    </h5>
                    <div class="small text-white-50" id="modalSubtitulo">Comprobante #</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Resumen Financiero del Comprobante -->
                <div class="row g-2 mb-4 p-3 bg-light rounded-3 border">
                    <div class="col-4 text-center border-end">
                        <div class="text-muted small text-uppercase fw-bold">Monto Total</div>
                        <div class="fs-5 fw-bold text-dark" id="modalMontoTotal">S/ 0.00</div>
                    </div>
                    <div class="col-4 text-center border-end">
                        <div class="text-muted small text-uppercase fw-bold">Total Abonado</div>
                        <div class="fs-5 fw-bold text-success" id="modalMontoAbonado">S/ 0.00</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="text-muted small text-uppercase fw-bold">Saldo Restante</div>
                        <div class="fs-5 fw-bold text-danger" id="modalSaldoPendiente">S/ 0.00</div>
                    </div>
                </div>

                <!-- Historial de Abonos -->
                <h6 class="fw-bold mb-2 text-dark d-flex align-items-center justify-content-between">
                    <span><i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i> Historial de Abonos / Adelantos</span>
                    <span class="badge bg-secondary" id="modalTotalAbonosBadge">0 pagos</span>
                </h6>
                <div class="table-responsive mb-4" style="max-height: 200px; overflow-y: auto;">
                    <table class="table table-sm table-bordered align-middle mb-0" id="tablaHistorialAbonos">
                        <thead class="table-light small text-muted">
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Operación / Banco</th>
                                <th>Observación</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="listaHistorialAbonos">
                            <!-- Inyectado dinámicamente con JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Formulario para Registrar Nuevo Abono -->
                <div class="card border-primary border-opacity-25 bg-primary bg-opacity-10" id="seccionNuevoAbono">
                    <div class="card-body p-3">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fa-solid fa-plus-circle me-1"></i> Registrar Nuevo Abono / Adelanto
                        </h6>
                        <form id="formRegistrarAbono" method="POST">
                            @csrf
                            <input type="hidden" name="comprobante_compra_id" id="formCompraId">
                            <input type="hidden" name="comprobante_venta_id" id="formVentaId">

                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">FECHA DE PAGO</label>
                                    <input type="date" name="fecha_pago" id="pagoFecha" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">MONTO A ABONAR (<span id="modalMonedaSimbolo">S/</span>)</label>
                                    <input type="number" step="0.01" min="0.01" name="monto" id="pagoMonto" class="form-control form-control-sm fw-bold" placeholder="0.00" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">MÉTODO DE PAGO</label>
                                    <select name="metodo_pago" id="pagoMetodo" class="form-select form-select-sm" required>
                                        <option value="TRANSFERENCIA">Transferencia Bancaria</option>
                                        <option value="YAPE">Yape</option>
                                        <option value="PLIN">Plin</option>
                                        <option value="EFECTIVO">Efectivo</option>
                                        <option value="DEPOSITO">Depósito en Cuenta</option>
                                        <option value="CHEQUE">Cheque</option>
                                        <option value="TARJETA">Tarjeta de Débito/Crédito</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">BANCO / ORIGEN</label>
                                    <input type="text" name="banco" id="pagoBanco" class="form-control form-control-sm" placeholder="Ej: BCP, BBVA, Interbank...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">NRO. OPERACIÓN / VOUCHER</label>
                                    <input type="text" name="nro_operacion" id="pagoOperacion" class="form-control form-control-sm" placeholder="Código o número de voucher">
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">OBSERVACIÓN / DETALLE</label>
                                    <input type="text" name="observacion" id="pagoObservacion" class="form-control form-control-sm" placeholder="Ej: Segundo adelanto del 50%, pago parcial, etc.">
                                </div>
                            </div>

                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-sm btn-primary px-4 fw-bold shadow-sm">
                                    <i class="fa-solid fa-check me-1"></i> Guardar Adelanto / Pago
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
