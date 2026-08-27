# 🏗️ 02. Arquitectura y Diseño Técnico (Laravel 11 Multi-Empresa & Multi-Sede)

## 1. Stack Tecnológico

| Capa | Tecnología | Propósito |
| :--- | :--- | :--- |
| **Framework Backend** | **Laravel 11** (PHP 8.2+) | Framework PHP moderno, robusto, con Eloquent ORM, middleware, migraciones y soporte Multi-Tenant nativo. |
| **Base de Datos** | MySQL 8.0+ / MariaDB (XAMPP) | Motor relacional con integridad referencial (InnoDB), transacciones ACID e índices en `empresa_id` y `sede_id`. |
| **Motor de Vistas** | Blade Templates + Componentes | Vistas modulares, layouts reutilizables y selector de contexto. |
| **Diseño UI / CSS** | Bootstrap 5.3 + FontAwesome 6 | Interfaz empresarial limpia, adaptable a pantallas de oficina o talleres. |
| **Tablas y Exportación**| DataTables.js (HTML5 Export) | Paginación rápida, filtros en vivo, exportación a Excel y PDF. |
| **Componentes Asíncronos** | Vanilla JavaScript / Fetch API | Consultas a SUNAT sin recargar página y modales de pago interactivos. |
| **Gestor de Paquetes** | Composer & NPM | Gestión de dependencias backend y frontend. |

---

## 2. Patrón de Arquitectura: Scoped Multi-Tenant (Empresa & Sede)

Para soportar múltiples empresas y sedes en una base de datos unificada de alto rendimiento, se utiliza el patrón **Scoped Multi-Tenant**:

```
                              ┌───────────────────────────────────┐
                              │   USUARIO / CONTADOR / DIRECTIVA  │
                              └─────────────────┬─────────────────┘
                                                │ Selecciona Empresa y Sede
                                                ▼
                              ┌───────────────────────────────────┐
                              │    SetEmpresaSedeMiddleware       │
                              │ (Guarda empresa_activa_id y       │
                              │  sede_activa_id en sesión)        │
                              └─────────────────┬─────────────────┘
                                                │
                                                ▼
                              ┌───────────────────────────────────┐
                              │       CONTROLADOR LARAVEL         │
                              │ (Dashboard, CxP, CxC, Reportes)   │
                              └─────────┬───────────────────┬─────┘
                     Aplica             │                   │ Retorna
                     EmpresaScope       │                   │ Vista Blade
                     & SedeScope        ▼                   ▼
                    ┌─────────────────────────┐      ┌─────────────────────────┐
                    │     ELOQUENT MODELS     │      │       BLADE VIEWS       │
                    │ ComprobanteCompra       │      │ (Header c/ Selectores,  │
                    │ ComprobanteVenta        │      │  Tablas, Modales Pagos) │
                    └────────────┬────────────┘      └─────────────────────────┘
                                 │
                                 ▼
                    ┌─────────────────────────┐
                    │      BASE DE DATOS      │
                    │ `empresa_id`, `sede_id` │
                    └─────────────────────────┘
```

### Funcionamiento de los Scopes:
1. **`EmpresaScope`**: Si el usuario está en el contexto de la Empresa 1, todas las consultas `ComprobanteCompra::all()` ejecutan automáticamente:
   ```sql
   SELECT * FROM comprobantes_compras WHERE empresa_id = 1
   ```
2. **`SedeScope`**: Si además seleccionó la Sede 2, la consulta se refina a:
   ```sql
   SELECT * FROM comprobantes_compras WHERE empresa_id = 1 AND sede_id = 2
   ```
3. **Vista Consolidada (SuperAdmin)**: Si el usuario selecciona `[ 🌐 Todas las Empresas ]` o `[ 🌐 Todas las Sedes ]`, los scopes se desactivan temporalmente (`withoutGlobalScopes()`) para generar reportes globales consolidados.

---

## 3. Estructura de Directorios del Proyecto Laravel

```
msa_sistema_facturacion/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthenticatedSessionController.php
│   │   │   ├── DashboardController.php         # KPIs corporativos y por empresa/sede
│   │   │   ├── EmpresaController.php           # CRUD de Empresas y datos fiscales
│   │   │   ├── SedeController.php              # CRUD de Sedes por empresa
│   │   │   ├── ProveedorController.php         # Gestión de proveedores
│   │   │   ├── ComprobanteCompraController.php # Facturas de compras (CxP)
│   │   │   ├── ClienteController.php           # Gestión de clientes
│   │   │   ├── ComprobanteVentaController.php  # Facturas de ventas (CxC)
│   │   │   ├── PagoAbonoController.php         # Adelantos y pagos parciales
│   │   │   ├── SunatController.php             # API de búsqueda RUC/DNI
│   │   │   └── ReporteController.php           # Exportación Excel/PDF
│   │   ├── Middleware/
│   │   │   ├── SetActiveEmpresaSede.php        # Controla la empresa y sede activa
│   │   │   └── CheckRole.php                   # Permisos de usuario (SuperAdmin, Admin, etc.)
│   │   └── Requests/
│   │       ├── StoreEmpresaRequest.php
│   │       ├── StoreComprobanteCompraRequest.php
│   │       ├── StoreComprobanteVentaRequest.php
│   │       └── StorePagoAbonoRequest.php
│   ├── Models/
│   │   ├── Empresa.php                         # Modelo de Empresas (Razones Sociales)
│   │   ├── Sede.php                            # Modelo de Sedes/Sucursales
│   │   ├── Area.php                            # Áreas (Taller, Ventas, Repuestos, Cigüeñal)
│   │   ├── Proveedor.php                       # Proveedores
│   │   ├── ComprobanteCompra.php               # Comprobantes de compras
│   │   ├── Cliente.php                         # Clientes
│   │   ├── ComprobanteVenta.php                # Comprobantes de ventas
│   │   ├── PagoAbono.php                       # Historial de adelantos y pagos
│   │   └── User.php                            # Usuarios del sistema
│   ├── Scopes/
│   │   ├── EmpresaScope.php                    # Filtro global por empresa activa
│   │   └── SedeScope.php                       # Filtro global por sede activa
│   └── Services/
│       ├── SunatService.php                    # Consumo de API SUNAT con Http Client
│       ├── SemaforoVencimientoService.php      # Lógica de semáforos y alertas
│       └── WhatsAppService.php                 # Enlaces automáticos con cuentas de la empresa
├── config/                                     # Configuraciones de Laravel
├── database/
│   ├── migrations/                             # Migraciones de base de datos
│   │   ├── 2026_01_01_000001_create_empresas_table.php
│   │   ├── 2026_01_01_000002_create_sedes_table.php
│   │   ├── 2026_01_01_000003_create_areas_table.php
│   │   ├── 2026_01_01_000004_create_proveedores_table.php
│   │   ├── 2026_01_01_000005_create_clientes_table.php
│   │   ├── 2026_01_01_000006_create_comprobantes_compras_table.php
│   │   ├── 2026_01_01_000007_create_comprobantes_ventas_table.php
│   │   ├── 2026_01_01_000008_create_pagos_abonos_table.php
│   │   ├── 2026_01_01_000009_create_empresa_user_table.php
│   │   └── 2026_01_01_000010_create_sede_user_table.php
│   └── seeders/                                # Datos iniciales
├── public/                                     # Assets públicos
├── resources/
│   └── views/                                  # Vistas Blade
│       ├── layouts/
│       │   ├── app.blade.php                   # Layout principal
│       │   ├── navbar.blade.php                # Doble Selector: Empresa y Sede
│       │   └── sidebar.blade.php               # Menú lateral
│       ├── dashboard/                          # Dashboards por empresa y corporativo
│       ├── empresas/                           # Gestión de razones sociales y logos
│       ├── sedes/                              # Gestión de sucursales
│       ├── proveedores/                        # Cuentas por Pagar
│       ├── clientes/                           # Cuentas por Cobrar
│       ├── pagos/                              # Historial de adelantos
│       └── reportes/                           # Reportes Excel/PDF
├── routes/
│   └── web.php                                 # Rutas del sistema
└── documentacion/                              # Documentación oficial
```
