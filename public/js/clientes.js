/**
 * Lógica de formulario y consulta SUNAT/RENIEC para Clientes
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

function cambiarPlaceholderDoc() {
    const tipo = document.getElementById('selectTipoDoc');
    const input = document.getElementById('inputNumDoc');
    if (!tipo || !input) return;

    if (tipo.value === 'RUC') {
        input.placeholder = 'Ej: 20498765432 (11 dígitos)';
        input.maxLength = 11;
    } else if (tipo.value === 'DNI') {
        input.placeholder = 'Ej: 45678912 (8 dígitos)';
        input.maxLength = 8;
    } else {
        input.placeholder = 'Número de documento';
        input.maxLength = 20;
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

function buscarClienteSunatReniec() {
    const tipo = document.getElementById('selectTipoDoc').value;
    const doc = document.getElementById('inputNumDoc').value.trim();
    const btn = document.getElementById('btnBuscarDoc');
    const mensaje = document.getElementById('sunatMensaje');

    if (tipo === 'RUC' && doc.length !== 11) {
        Swal.fire('Atención', 'El RUC debe tener 11 dígitos.', 'warning');
        return;
    }
    if (tipo === 'DNI' && doc.length !== 8) {
        Swal.fire('Atención', 'El DNI debe tener 8 dígitos.', 'warning');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Consultando...';
    mensaje.innerHTML = '<span class="text-muted">Consultando padrón oficial...</span>';

    const endpoint = tipo === 'RUC' ? `/api/sunat/ruc/${doc}` : `/api/sunat/dni/${doc}`;

    fetch(endpoint)
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-magnifying-glass me-1"></i> Buscar';

            if (data.success && data.data) {
                const d = data.data;
                const nombre = d.razon_social || d.nombre_completo;
                document.getElementById('inputRazonSocial').value = nombre;
                if (d.direccion) document.getElementById('inputDireccion').value = d.direccion;

                mensaje.innerHTML = `<span class="text-success"><i class="fa-solid fa-circle-check"></i> Encontrado: ${nombre}</span>`;
                Swal.fire({
                    icon: 'success',
                    title: 'Cliente Encontrado',
                    text: nombre,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                mensaje.innerHTML = `<span class="text-warning"><i class="fa-solid fa-triangle-exclamation"></i> ${data.message || 'No se encontraron datos automáticos.'}</span>`;
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-magnifying-glass me-1"></i> Buscar';
            mensaje.innerHTML = '<span class="text-warning">Servicio no disponible. Ingrese manualmente.</span>';
        });
}

document.addEventListener('DOMContentLoaded', function () {
    filtrarSedesPorEmpresa();
    actualizarMonedaLabel();
    cambiarPlaceholderDoc();
});
