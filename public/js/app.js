/**
 * MSA Facturación y Control Contable - JavaScript Principal
 * Configuración de Tablas, Reportes Ejecutivos e Impresión
 */

document.addEventListener('DOMContentLoaded', function () {
    const config = window.MSA_CONFIG || {
        empresa: 'GRUPO EMPRESARIAL MSA',
        ruc: '20601234567',
        sede: 'Consolidado General',
        user: 'Usuario Contable',
        rol: 'CONTADOR',
        fechaHora: new Date().toLocaleDateString('es-PE') + ' ' + new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
    };

    // Helper para extraer filtros activos del formulario
    function getActiveFiltersSummary() {
        const filters = [];
        const form = document.querySelector('form[method="GET"]');
        if (form) {
            const estadoSelect = form.querySelector('select[name="estado"]');
            if (estadoSelect && estadoSelect.value) {
                filters.push(`<strong>Estado:</strong> ${estadoSelect.options[estadoSelect.selectedIndex].text.trim()}`);
            }
            const areaSelect = form.querySelector('select[name="area_id"]');
            if (areaSelect && areaSelect.value) {
                filters.push(`<strong>Área:</strong> ${areaSelect.options[areaSelect.selectedIndex].text.trim()}`);
            }
            const monedaSelect = form.querySelector('select[name="moneda"]');
            if (monedaSelect && monedaSelect.value) {
                filters.push(`<strong>Moneda:</strong> ${monedaSelect.options[monedaSelect.selectedIndex].text.trim()}`);
            }
            const fechaDesde = form.querySelector('input[name="fecha_desde"]');
            const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
            if (fechaDesde && fechaDesde.value) {
                filters.push(`<strong>Desde:</strong> ${fechaDesde.value}`);
            }
            if (fechaHasta && fechaHasta.value) {
                filters.push(`<strong>Hasta:</strong> ${fechaHasta.value}`);
            }
        }
        return filters.length > 0 ? filters.join(' &bull; ') : 'Todos los registros (Sin filtros específicos)';
    }

    // Helper para extraer métricas KPI de las tarjetas
    function getKpiSummaryHtml() {
        const statCards = document.querySelectorAll('.stat-card');
        if (!statCards || statCards.length === 0) return '';

        let kpiHtml = '<div class="print-kpi-strip">';
        statCards.forEach(card => {
            const labelEl = card.querySelector('.text-uppercase, .text-muted.small.fw-bold') || card.querySelector('.small.fw-bold');
            const valEl = card.querySelector('.fs-4, .fs-5');
            if (labelEl && valEl) {
                const label = labelEl.innerText.trim();
                const val = valEl.innerText.trim();
                let colorClass = 'text-dark';
                if (labelEl.classList.contains('text-danger') || valEl.classList.contains('text-danger')) colorClass = 'danger';
                else if (labelEl.classList.contains('text-success') || valEl.classList.contains('text-success')) colorClass = 'success';
                else if (labelEl.classList.contains('text-primary') || valEl.classList.contains('text-primary')) colorClass = 'primary';
                else if (labelEl.classList.contains('text-info') || valEl.classList.contains('text-info') || valEl.classList.contains('text-info-emphasis')) colorClass = 'info';

                kpiHtml += `
                    <div class="print-kpi-card">
                        <div class="print-kpi-label">${label}</div>
                        <div class="print-kpi-val ${colorClass}">${val}</div>
                    </div>
                `;
            }
        });
        kpiHtml += '</div>';
        return kpiHtml;
    }

    // Configuración global de DataTables
    if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            pageLength: 25,
            responsive: true,
            dom: "<'row mb-3'<'col-md-6 d-flex align-items-center gap-1'B><'col-md-6 d-flex justify-content-end'f>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row mt-3'<'col-md-5'i><'col-md-7 d-flex justify-content-end'p>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-sm btn-outline-success me-1 shadow-sm',
                    text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                    exportOptions: {
                        columns: ':visible:not(.no-export)'
                    },
                    title: function () {
                        return (document.title || 'Reporte_Contable_MSA').replace(/[^a-zA-Z0-9_-]/g, '_');
                    }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'btn btn-sm btn-outline-danger me-1 shadow-sm',
                    text: '<i class="fa-solid fa-file-pdf me-1"></i> PDF',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible:not(.no-export)'
                    },
                    customize: function (doc) {
                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader.fontSize = 8.5;
                        doc.styles.tableHeader.fillColor = '#0f172a';
                        doc.styles.tableHeader.color = '#ffffff';
                        doc.styles.tableHeader.alignment = 'left';
                        doc.pageMargins = [20, 30, 20, 30];

                        // Header personalizado
                        doc.content.splice(0, 0, {
                            margin: [0, 0, 0, 12],
                            columns: [
                                {
                                    width: '*',
                                    text: [
                                        { text: config.empresa + '\n', fontSize: 12, bold: true, color: '#0f172a' },
                                        { text: 'RUC: ' + config.ruc + ' | Sede: ' + config.sede + '\n', fontSize: 8, color: '#64748b' },
                                        { text: document.title.split('|')[0].trim(), fontSize: 10, bold: true, color: '#1e3a8a' }
                                    ]
                                },
                                {
                                    width: 160,
                                    alignment: 'right',
                                    text: [
                                        { text: 'Fecha: ' + config.fechaHora + '\n', fontSize: 8, color: '#475569' },
                                        { text: 'Usuario: ' + config.user + ' (' + config.rol + ')', fontSize: 8, color: '#475569' }
                                    ]
                                }
                            ]
                        });

                        // Pie de página con numeración
                        doc['footer'] = function (currentPage, pageCount) {
                            return {
                                margin: [20, 10, 20, 0],
                                columns: [
                                    { text: 'MSA Facturación y Control Contable - Reporte Oficial', fontSize: 7, color: '#94a3b8' },
                                    { text: 'Página ' + currentPage.toString() + ' de ' + pageCount, alignment: 'right', fontSize: 7, color: '#94a3b8' }
                                ]
                            };
                        };
                    }
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-outline-primary shadow-sm',
                    text: '<i class="fa-solid fa-print me-1"></i> Imprimir Reporte',
                    autoPrint: true,
                    exportOptions: {
                        columns: ':visible:not(.no-export)'
                    },
                    customize: function (win) {
                        const pageTitle = (document.title || 'Reporte de Gestión Contable').split('|')[0].trim();
                        const filterSummary = getActiveFiltersSummary();
                        const kpiSummary = getKpiSummaryHtml();

                        // Agregar estilos avanzados a la ventana de impresión
                        $(win.document.head).find('title').text(pageTitle + ' - MSA Facturación');
                        $(win.document.head).append(`
                            <link rel="preconnect" href="https://fonts.googleapis.com">
                            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                            <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
                            <style>
                                @page {
                                    size: landscape;
                                    margin: 8mm 8mm 10mm 8mm;
                                }
                                * {
                                    box-sizing: border-box;
                                    -webkit-print-color-adjust: exact !important;
                                    print-color-adjust: exact !important;
                                }
                                body {
                                    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
                                    color: #0f172a !important;
                                    background: #ffffff !important;
                                    font-size: 10.5px !important;
                                    line-height: 1.3 !important;
                                    margin: 0 !important;
                                    padding: 6px !important;
                                }
                                h1, .dt-print-title { display: none !important; }

                                /* Membrete Corporativo Ejecutivo */
                                .print-executive-header {
                                    border-bottom: 2px solid #0f172a;
                                    padding-bottom: 10px;
                                    margin-bottom: 12px;
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: flex-start;
                                }
                                .print-brand-left {
                                    display: flex;
                                    align-items: center;
                                    gap: 12px;
                                }
                                .print-brand-icon {
                                    width: 40px;
                                    height: 40px;
                                    background: #0f172a;
                                    color: #38bdf8;
                                    border-radius: 8px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    font-size: 18px;
                                    font-weight: 800;
                                    border: 1px solid #1e293b;
                                    text-align: center;
                                    line-height: 40px;
                                }
                                .print-company-name {
                                    font-size: 15px;
                                    font-weight: 800;
                                    color: #0f172a;
                                    text-transform: uppercase;
                                    letter-spacing: -0.01em;
                                    margin: 0;
                                }
                                .print-company-sub {
                                    font-size: 10px;
                                    color: #475569;
                                    margin-top: 2px;
                                }
                                .print-report-meta {
                                    text-align: right;
                                }
                                .print-report-title {
                                    font-size: 14px;
                                    font-weight: 800;
                                    color: #1e3a8a;
                                    text-transform: uppercase;
                                    margin: 0 0 4px 0;
                                    letter-spacing: 0.02em;
                                }
                                .print-meta-text {
                                    font-size: 9.5px;
                                    color: #64748b;
                                    margin-bottom: 2px;
                                }
                                .print-filters-bar {
                                    background: #f8fafc;
                                    border: 1px solid #e2e8f0;
                                    border-radius: 6px;
                                    padding: 5px 10px;
                                    margin-bottom: 12px;
                                    font-size: 9.5px;
                                    color: #334155;
                                }

                                /* Franja Resumen KPI */
                                .print-kpi-strip {
                                    display: grid;
                                    grid-template-columns: repeat(3, 1fr);
                                    gap: 10px;
                                    margin-bottom: 12px;
                                }
                                .print-kpi-card {
                                    background: #f8fafc;
                                    border: 1px solid #cbd5e1;
                                    border-radius: 6px;
                                    padding: 6px 10px;
                                }
                                .print-kpi-label {
                                    font-size: 9px;
                                    font-weight: 700;
                                    text-transform: uppercase;
                                    color: #64748b;
                                    letter-spacing: 0.02em;
                                }
                                .print-kpi-val {
                                    font-size: 13px;
                                    font-weight: 800;
                                    margin-top: 2px;
                                }
                                .print-kpi-val.danger { color: #b91c1c; }
                                .print-kpi-val.success { color: #15803d; }
                                .print-kpi-val.primary { color: #1d4ed8; }
                                .print-kpi-val.info { color: #0369a1; }

                                /* Diseño de la Tabla Impresa */
                                table.dataTable {
                                    width: 100% !important;
                                    border-collapse: collapse !important;
                                    margin: 0 !important;
                                    font-size: 9.5px !important;
                                }
                                table.dataTable thead th {
                                    background-color: #0f172a !important;
                                    color: #ffffff !important;
                                    font-weight: 700 !important;
                                    font-size: 9px !important;
                                    text-transform: uppercase !important;
                                    letter-spacing: 0.03em !important;
                                    padding: 6px 5px !important;
                                    border: 1px solid #334155 !important;
                                    vertical-align: middle !important;
                                    white-space: nowrap !important;
                                }
                                table.dataTable tbody td {
                                    padding: 5px 6px !important;
                                    border: 1px solid #e2e8f0 !important;
                                    vertical-align: middle !important;
                                    color: #1e293b !important;
                                    font-size: 9.5px !important;
                                }
                                table.dataTable tbody tr:nth-child(even) td {
                                    background-color: #f8fafc !important;
                                }

                                /* Badges y Semáforos en Impresión */
                                .badge {
                                    display: inline-block !important;
                                    padding: 2px 5px !important;
                                    font-size: 8px !important;
                                    font-weight: 700 !important;
                                    border-radius: 4px !important;
                                    text-transform: uppercase !important;
                                    white-space: nowrap !important;
                                    line-height: 1.2 !important;
                                }
                                .badge-semaforo-rojo, .badge.bg-danger {
                                    background-color: #fee2e2 !important;
                                    color: #991b1b !important;
                                    border: 1px solid #f87171 !important;
                                }
                                .badge-semaforo-amarillo, .badge.bg-warning {
                                    background-color: #fef9c3 !important;
                                    color: #854d0e !important;
                                    border: 1px solid #facc15 !important;
                                }
                                .badge-semaforo-verde, .badge.bg-success {
                                    background-color: #dcfce7 !important;
                                    color: #166534 !important;
                                    border: 1px solid #4ade80 !important;
                                }
                                .badge.bg-info {
                                    background-color: #e0f2fe !important;
                                    color: #0369a1 !important;
                                    border: 1px solid #7dd3fc !important;
                                }
                                .badge.bg-light, .badge.bg-secondary-subtle {
                                    background-color: #f1f5f9 !important;
                                    color: #334155 !important;
                                    border: 1px solid #cbd5e1 !important;
                                }
                                .badge.bg-primary, .badge.bg-primary-subtle {
                                    background-color: #dbeafe !important;
                                    color: #1d4ed8 !important;
                                    border: 1px solid #93c5fd !important;
                                }

                                /* Alineación y Tipografías */
                                .font-monospace {
                                    font-family: 'JetBrains Mono', monospace !important;
                                    font-size: 8.5px !important;
                                    letter-spacing: -0.02em !important;
                                }
                                .text-end { text-align: right !important; }
                                .text-center { text-align: center !important; }
                                .text-dark { color: #0f172a !important; }
                                .text-muted { color: #64748b !important; }
                                .text-success { color: #15803d !important; }
                                .text-danger { color: #b91c1c !important; }
                                .text-primary { color: #1d4ed8 !important; }
                                .fw-bold { font-weight: 700 !important; }
                                .fw-semibold { font-weight: 600 !important; }
                                .small { font-size: 8.5px !important; }

                                /* Pie de Tabla con Totales */
                                table.dataTable tfoot td {
                                    background-color: #f1f5f9 !important;
                                    border-top: 2px solid #0f172a !important;
                                    border-bottom: 2px solid #0f172a !important;
                                    font-weight: 700 !important;
                                    font-size: 9.5px !important;
                                    padding: 6px !important;
                                }

                                /* Saltos de Página y Control */
                                tr { page-break-inside: avoid !important; }
                                thead { display: table-header-group !important; }
                                tfoot { display: table-footer-group !important; }

                                /* Bloque de Firmas y Pie */
                                .print-signatures-box {
                                    margin-top: 28px;
                                    display: grid;
                                    grid-template-columns: repeat(2, 1fr);
                                    gap: 80px;
                                    padding: 0 50px;
                                    page-break-inside: avoid;
                                }
                                .print-signature-line {
                                    border-top: 1px dashed #475569;
                                    padding-top: 5px;
                                    text-align: center;
                                    font-size: 9px;
                                    font-weight: 600;
                                    color: #334155;
                                }
                                .print-footer-notice {
                                    margin-top: 18px;
                                    padding-top: 6px;
                                    border-top: 1px solid #cbd5e1;
                                    display: flex;
                                    justify-content: space-between;
                                    font-size: 8px;
                                    color: #94a3b8;
                                    page-break-inside: avoid;
                                }
                            </style>
                        `);

                        // Construcción del Membrete e Inyección en el Body
                        const headerHtml = `
                            <div class="print-executive-header">
                                <div class="print-brand-left">
                                    <div class="print-brand-icon">MSA</div>
                                    <div>
                                        <div class="print-company-name">${config.empresa}</div>
                                        <div class="print-company-sub">
                                            <strong>RUC:</strong> ${config.ruc} &bull; <strong>Sede:</strong> ${config.sede}
                                        </div>
                                    </div>
                                </div>
                                <div class="print-report-meta">
                                    <div class="print-report-title">${pageTitle}</div>
                                    <div class="print-meta-text"><strong>Emisión:</strong> ${config.fechaHora}</div>
                                    <div class="print-meta-text"><strong>Generado por:</strong> ${config.user} (${config.rol})</div>
                                </div>
                            </div>
                            <div class="print-filters-bar">
                                <strong>Filtros aplicados:</strong> ${filterSummary}
                            </div>
                            ${kpiSummary}
                        `;

                        const footerHtml = `
                            <div class="print-signatures-box">
                                <div>
                                    <div class="print-signature-line">Elaborado por: ${config.user}<br><span style="font-size:8px; color:#64748b;">Responsable de Área / Contabilidad</span></div>
                                </div>
                                <div>
                                    <div class="print-signature-line">Revisado y Aprobado<br><span style="font-size:8px; color:#64748b;">Gerencia / Tesorería</span></div>
                                </div>
                            </div>
                            <div class="print-footer-notice">
                                <span>MSA Facturación & Control Contable &bull; Documento de auditoría y control interno</span>
                                <span>Página generada el ${config.fechaHora}</span>
                            </div>
                        `;

                        // Reemplazar / Inyectar en el contenido
                        const body = $(win.document.body);
                        body.find('h1').remove();
                        body.prepend(headerHtml);
                        body.append(footerHtml);
                    }
                }
            ]
        });

        // Inicializar tablas automáticas
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
        if (sidebar) sidebar.classList.toggle('active');
        if (backdrop) backdrop.classList.toggle('show');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('active');
        if (backdrop) backdrop.classList.remove('show');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }
});

