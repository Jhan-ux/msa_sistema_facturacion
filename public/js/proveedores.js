/**
 * Lógica de formulario y consulta SUNAT para Proveedores
 */

function toggleSeccionAdelanto() {
    const check = document.getElementById('checkTieneAdelanto');
    const bloque = document.getElementById('bloqueAdelanto');
    if (check && bloque) {
        bloque.style.display = check.checked ? 'block' : 'none';
    }
}

function actualizarMonedaLabel() {
    const select = document.getElementById('selectMoneda');
    const label = document.getElementById('labelMonedaAdelanto');
    if (select && label) {
        label.innerText = select.value === 'USD' ? '$' : 'S/';
    }
}

function filtrarSedesPorEmpresa() {
    const selectEmpresa = document.getElementById('selectEmpresa');
    const selectSede = document.getElementById('selectSede');
    if (!selectEmpresa || !selectSede) return;

    const empresaId = selectEmpresa.value;
    let primeraSede = null;

    for (let i = 0; i < selectSede.options.length; i++) {
        const opt = selectSede.options[i];
        const empresaOpt = opt.getAttribute('data-empresa');
        if (empresaOpt === empresaId) {
            opt.style.display = 'block';
            if (!primeraSede) primeraSede = opt.value;
        } else {
            opt.style.display = 'none';
        }
    }
    if (primeraSede) selectSede.value = primeraSede;
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function buscarProveedorSunat() {
    const inputRuc = document.getElementById('inputRuc');
    const btn = document.getElementById('btnBuscarSunat');
    const mensaje = document.getElementById('sunatMensaje');
    if (!inputRuc) return;

    const ruc = inputRuc.value.trim();

    if (ruc.length !== 11) {
        Swal.fire('Atención', 'El RUC debe tener 11 dígitos numéricos.', 'warning');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Consultando...';
    mensaje.innerHTML = '<span class="text-muted">Consultando padrón SUNAT...</span>';

    fetch(`/api/sunat/ruc/${encodeURIComponent(ruc)}`)
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-magnifying-glass me-1"></i> Buscar SUNAT';

            if (data.success && data.data) {
                const d = data.data;
                document.getElementById('inputRazonSocial').value = d.razon_social;
                if (d.direccion) document.getElementById('inputDireccion').value = d.direccion;

                mensaje.innerHTML = `<span class="text-success"><i class="fa-solid fa-circle-check"></i> Encontrado: ${escapeHtml(d.razon_social)} (Estado: ${escapeHtml(d.estado)} / Condición: ${escapeHtml(d.condicion)})</span>`;
                Swal.fire({
                    icon: 'success',
                    title: 'Proveedor Encontrado',
                    text: d.razon_social,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                mensaje.innerHTML = `<span class="text-warning"><i class="fa-solid fa-triangle-exclamation"></i> ${escapeHtml(data.message || 'No se encontraron datos automáticos. Ingrese los datos manualmente.')}</span>`;
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-magnifying-glass me-1"></i> Buscar SUNAT';
            mensaje.innerHTML = '<span class="text-warning">Servicio no disponible. Ingrese manualmente.</span>';
        });
}

document.addEventListener('DOMContentLoaded', function () {
    filtrarSedesPorEmpresa();
    actualizarMonedaLabel();
});
