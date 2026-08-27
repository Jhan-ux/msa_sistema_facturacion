/**
 * MSA Facturación y Control Contable - JavaScript Principal
 */

document.addEventListener('DOMContentLoaded', function () {
    // Configuración global en español para DataTables si jQuery está disponible
    if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            pageLength: 25,
            responsive: true,
            dom: "<'row mb-3'<'col-md-6 d-flex align-items-center'B><'col-md-6 d-flex justify-content-end'f>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row mt-3'<'col-md-5'i><'col-md-7 d-flex justify-content-end'p>>",
            buttons: [
                { extend: 'excelHtml5', className: 'btn btn-sm btn-outline-success me-1', text: '<i class="fa-solid fa-file-excel me-1"></i> Excel' },
                { extend: 'pdfHtml5', className: 'btn btn-sm btn-outline-danger me-1', text: '<i class="fa-solid fa-file-pdf me-1"></i> PDF' },
                { extend: 'print', className: 'btn btn-sm btn-outline-secondary', text: '<i class="fa-solid fa-print me-1"></i> Imprimir' }
            ]
        });

        // Inicializar tablas automáticas si existen en el DOM
        if ($('#tablaProveedores').length) {
            $('#tablaProveedores').DataTable();
        }
        if ($('#tablaClientes').length) {
            $('#tablaClientes').DataTable();
        }
        if ($('#tablaReporteCxP').length) {
            $('#tablaReporteCxP').DataTable({ pageLength: 50 });
        }
        if ($('#tablaReporteCxC').length) {
            $('#tablaReporteCxC').DataTable({ pageLength: 50 });
        }
    }

    // Toggle para sidebar en dispositivos móviles
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');

    function toggleSidebar() {
        if (sidebar) {
            sidebar.classList.toggle('active');
        }
        if (backdrop) {
            backdrop.classList.toggle('show');
        }
    }

    function closeSidebar() {
        if (sidebar) {
            sidebar.classList.remove('active');
        }
        if (backdrop) {
            backdrop.classList.remove('show');
        }
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }
});
