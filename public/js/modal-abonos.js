/**
 * Gestión interactiva del Modal de Adelantos y Pagos
 */

function abrirModalAbonosCompra(id) {
    fetch(`/proveedores/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const c = data.compra;
                const simbolo = c.moneda === 'USD' ? '$' : 'S/';

                document.getElementById('modalSubtitulo').innerText = `${c.tipo_comprobante} ${c.serie_correlativo} - ${c.proveedor.razon_social} (${c.empresa.nombre_comercial ?? c.empresa.razon_social})`;
                document.getElementById('modalMontoTotal').innerText = `${simbolo} ${parseFloat(c.monto_total).toFixed(2)}`;
                document.getElementById('modalMontoAbonado').innerText = `${simbolo} ${parseFloat(c.monto_pagado).toFixed(2)}`;
                document.getElementById('modalSaldoPendiente').innerText = `${simbolo} ${parseFloat(c.saldo_pendiente).toFixed(2)}`;
                document.getElementById('modalMonedaSimbolo').innerText = simbolo;

                document.getElementById('formCompraId').value = c.id;
                document.getElementById('formVentaId').value = '';
                document.getElementById('formRegistrarAbono').action = "/pagos/compra";

                const inputMonto = document.getElementById('pagoMonto');
                inputMonto.max = c.saldo_pendiente;
                inputMonto.value = c.saldo_pendiente > 0 ? parseFloat(c.saldo_pendiente).toFixed(2) : '';

                if (parseFloat(c.saldo_pendiente) <= 0.001) {
                    document.getElementById('seccionNuevoAbono').style.display = 'none';
                } else {
                    document.getElementById('seccionNuevoAbono').style.display = 'block';
                }

                renderizarListaPagos(data.pagos, simbolo);
                new bootstrap.Modal(document.getElementById('modalAbonos')).show();
            }
        })
        .catch(err => {
            console.error("Error al cargar abonos de compra:", err);
            Swal.fire('Error', 'No se pudieron cargar los datos del comprobante.', 'error');
        });
}

function abrirModalAbonosVenta(id) {
    fetch(`/clientes/${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const v = data.venta;
                const simbolo = v.moneda === 'USD' ? '$' : 'S/';

                document.getElementById('modalSubtitulo').innerText = `${v.tipo_comprobante} ${v.serie_correlativo} - ${v.cliente.razon_social_nombre} (${v.empresa.nombre_comercial ?? v.empresa.razon_social})`;
                document.getElementById('modalMontoTotal').innerText = `${simbolo} ${parseFloat(v.monto_total).toFixed(2)}`;
                document.getElementById('modalMontoAbonado').innerText = `${simbolo} ${parseFloat(v.monto_cobrado).toFixed(2)}`;
                document.getElementById('modalSaldoPendiente').innerText = `${simbolo} ${parseFloat(v.saldo_pendiente).toFixed(2)}`;
                document.getElementById('modalMonedaSimbolo').innerText = simbolo;

                document.getElementById('formVentaId').value = v.id;
                document.getElementById('formCompraId').value = '';
                document.getElementById('formRegistrarAbono').action = "/pagos/venta";

                const inputMonto = document.getElementById('pagoMonto');
                inputMonto.max = v.saldo_pendiente;
                inputMonto.value = v.saldo_pendiente > 0 ? parseFloat(v.saldo_pendiente).toFixed(2) : '';

                if (parseFloat(v.saldo_pendiente) <= 0.001) {
                    document.getElementById('seccionNuevoAbono').style.display = 'none';
                } else {
                    document.getElementById('seccionNuevoAbono').style.display = 'block';
                }

                renderizarListaPagos(data.pagos, simbolo);
                new bootstrap.Modal(document.getElementById('modalAbonos')).show();
            }
        })
        .catch(err => {
            console.error("Error al cargar abonos de venta:", err);
            Swal.fire('Error', 'No se pudieron cargar los datos del comprobante.', 'error');
        });
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderizarListaPagos(pagos, simbolo) {
    const tbody = document.getElementById('listaHistorialAbonos');
    const badge = document.getElementById('modalTotalAbonosBadge');
    if (badge) badge.innerText = `${pagos.length} abonos`;
    tbody.innerHTML = '';

    if (pagos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">No hay abonos registrados aún.</td></tr>`;
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

    pagos.forEach(p => {
        const fechaSafe = escapeHtml(p.fecha_pago);
        const metodoSafe = escapeHtml(p.metodo_pago);
        const opSafe = p.nro_operacion ? escapeHtml(p.nro_operacion) : '-';
        const bancoSafe = p.banco ? ` (${escapeHtml(p.banco)})` : '';
        const obsSafe = p.observacion ? escapeHtml(p.observacion) : '-';
        const pagoId = parseInt(p.id, 10);

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="small fw-semibold">${fechaSafe}</td>
            <td class="fw-bold text-success">${escapeHtml(simbolo)} ${parseFloat(p.monto).toFixed(2)}</td>
            <td><span class="badge bg-light text-dark border">${metodoSafe}</span></td>
            <td class="small">${opSafe}${bancoSafe}</td>
            <td class="small text-muted">${obsSafe}</td>
            <td class="text-center">
                <form action="/pagos/${pagoId}" method="POST" onsubmit="return confirm('¿Seguro que deseas anular este abono? El saldo del comprobante se recalculará automáticamente.')">
                    <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Eliminar abono">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
            </td>
        `;
        tbody.appendChild(tr);
    });
}
