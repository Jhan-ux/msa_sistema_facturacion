# 🏢📊 MSA - Sistema de Facturación y Control Contable Corporativo
> **Sistema integral de gestión de facturación, cuentas por pagar (CxP), cuentas por cobrar (CxC), amortizaciones y alertas de vencimiento con arquitectura Multi-Empresa y Multi-Sede.**

---

## 🌟 Características Principales

- **🏛️ Arquitectura Multi-Empresa & Multi-Sede:** Permite administrar múltiples razones sociales (empresas) y sucursales (sedes) de forma centralizada o aislada con selector de contexto en tiempo real.
- **📊 Tablero de Control Financiero (Dashboard):** Indicadores en tiempo real de saldos pendientes en Soles (PEN) y Dólares (USD), semáforos de vencimiento y distribución de gastos/ingresos por áreas operativas (*Taller, Repuestos, Ventas, Cigüeñal, Administración*).
- **💸 Cuentas por Pagar (Proveedores - CxP):** Control minucioso de compras, facturas, boletas, fechas de vencimiento, estados de pago (*Pendiente, Con Adelanto, Pagado, Vencido*) y registro de amortizaciones/vouchers.
- **💳 Cuentas por Cobrar (Clientes - CxC):** Registro de ventas, control de cobranzas, historial de abonos y generación de enlaces de cobro con formato para **WhatsApp** con cuentas bancarias configuradas por empresa.
- **🟢🟡🔴 Motor de Semáforos de Vencimiento:** Alertas inteligentes con código de color según días restantes o días de mora para priorizar pagos y cobranzas urgentes.
- **🔍 Integración de Consultas SUNAT & RENIEC:** Búsqueda asíncrona de datos de RUC y DNI (Razón Social, Dirección, Estado y Condición Habido).
- **👤 Módulo de Mi Perfil & Seguridad:** Panel de usuario para consultar datos personales, empresas/sedes asignadas y actualización segura de contraseña.
- **📑 Reportes y Exportación:** Generación y exportación de estados de cuenta y listas en Excel, PDF y vistas optimizadas para impresión.

---

## 🛠️ Stack Tecnológico

- **Backend:** [Laravel 11](https://laravel.com/) (PHP 8.2+)
- **Base de Datos:** MySQL / MariaDB con Eloquent ORM y migraciones estructuradas
- **Frontend & UI:** Blade Templates, Bootstrap 5.3, FontAwesome 6, SweetAlert2
- **DataTables:** DataTables.js con filtros avanzados, búsqueda instantánea y botones de exportación
- **Bundler:** Vite
- **Integraciones:** API REST para consultas SUNAT / RENIEC y servicio de WhatsApp

---

## 🚀 Guía de Instalación y Puesta en Marcha

### 1. Clonar el Repositorio
```bash
git clone https://github.com/Jhan-ux/msa_sistema_facturacion.git
cd msa_sistema_facturacion
```

### 2. Instalar Dependencias de PHP y JavaScript
```bash
composer install
npm install
```

### 3. Configurar el Entorno (.env)
Copiar el archivo de ejemplo y configurar las credenciales de base de datos:
```bash
cp .env.example .env
php artisan key:generate
```

En tu archivo `.env`, verifica los datos de conexión a MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=msa_sistema_facturacion
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Ejecutar Migraciones y Seeders (Datos de Prueba)
```bash
php artisan migrate --seed
```

### 5. Iniciar los Servidores de Desarrollo
En terminales separadas ejecuta:
```bash
# Servidor Web Laravel
php artisan serve

# Compilador de Assets Frontend (Vite)
npm run dev
```

Acceder al sistema en tu navegador: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🔑 Credenciales de Acceso por Defecto

El seeder inicial crea usuarios con roles preconfigurados:

| Usuario | Contraseña | Rol | Acceso |
| :--- | :--- | :--- | :--- |
| **`admin`** | `admin123` | **SUPERADMIN** | Acceso global irrestricto a todas las Empresas y Sedes |
| **`contador`** | `contador123` | **CONTADOR** | Acceso a operaciones contables, CxP, CxC y Reportes |

---

## 📁 Estructura del Proyecto

```text
msa_sistema_facturacion/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Controladores (Auth, Clientes, Proveedores, Pagos, Reportes, Dashboard, Sunat, Profile)
│   │   └── Middleware/           # Middlewares (ej. SetActiveEmpresaSede)
│   ├── Models/                   # Modelos Eloquent (Empresa, Sede, Cliente, Proveedor, Comprobantes, Pagos, User, Area)
│   ├── Providers/                # Service Providers de Laravel
│   └── Services/                 # Capa de servicios (DashboardService, PagoService, SunatService, WhatsAppService)
├── bootstrap/                    # Configuración de arranque de la aplicación
├── config/                       # Archivos de configuración (app, auth, database, services, etc.)
├── database/
│   ├── factories/                # Fábricas de modelos para testing
│   ├── migrations/               # Migraciones (Arquitectura Multi-empresa y Multi-sede)
│   └── seeders/                  # Seeders con datos iniciales corporativos
├── documentacion/                # Documentación exhaustiva sobre arquitectura, requerimientos y modelo SQL
│   ├── 01_REQUERIMIENTOS_Y_ALCANCE.md
│   ├── 02_ARQUITECTURA_Y_DISENO_TECNICO.md
│   ├── 03_MODELO_DE_DATOS_Y_SQL.md
│   ├── 04_MODULOS_Y_PANTALLAS.md
│   ├── 05_INTEGRACIONES_Y_ALERTAS.md
│   └── README.md
├── public/                       # Assets públicos compilados (CSS, JS, favicon)
│   ├── css/                      # Estilos personalizados (app.css, auth.css)
│   └── js/                       # Scripts del frontend (app.js, auth.js, clientes.js, modal-abonos.js, proveedores.js)
├── resources/
│   ├── css/                      # Estilos fuentes
│   ├── js/                       # JavaScript fuente
│   └── views/                    # Vistas Blade modulares
│       ├── auth/                 # Login y autenticación
│       ├── clientes/             # Vistas de Cuentas por Cobrar (CxC)
│       ├── components/           # Componentes Blade reutilizables (modal_abonos)
│       ├── dashboard/            # Panel principal con KPIs y alertas
│       ├── layouts/              # Plantillas base (app, navbar, sidebar)
│       ├── perfil/               # Panel de Mi Perfil & Seguridad de contraseña
│       ├── proveedores/          # Vistas de Cuentas por Pagar (CxP)
│       └── reportes/             # Reportes analíticos de CxP y CxC
├── routes/
│   ├── console.php               # Comandos de consola Artisan
│   └── web.php                   # Rutas web del sistema y endpoints
├── storage/                      # Almacenamiento local, logs y caché
└── tests/                        # Pruebas automatizadas (Unit y Feature)
```

---

## 📚 Documentación Técnica Detallada

Para más información sobre la arquitectura y diseño técnico, consulta la carpeta [`documentacion/`](file:///c:/xampp/htdocs/msa_sistema_facturacion/documentacion):
- 📘 [01. Requerimientos y Alcance Funcional](file:///c:/xampp/htdocs/msa_sistema_facturacion/documentacion/01_REQUERIMIENTOS_Y_ALCANCE.md)
- 🏗️ [02. Arquitectura y Diseño Técnico](file:///c:/xampp/htdocs/msa_sistema_facturacion/documentacion/02_ARQUITECTURA_Y_DISENO_TECNICO.md)
- 🗄️ [03. Modelo de Datos y Script SQL DDL](file:///c:/xampp/htdocs/msa_sistema_facturacion/documentacion/03_MODELO_DE_DATOS_Y_SQL.md)
- 🖥️ [04. Módulos y Pantallas del Sistema](file:///c:/xampp/htdocs/msa_sistema_facturacion/documentacion/04_MODULOS_Y_PANTALLAS.md)
- 🔔 [05. Integraciones SUNAT, WhatsApp y Motor de Alertas](file:///c:/xampp/htdocs/msa_sistema_facturacion/documentacion/05_INTEGRACIONES_Y_ALERTAS.md)

---

## 📄 Licencia

Este proyecto es software de código abierto licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).

## Resumen de funciones del software:

El software es una herramienta integral de gestiòn de cobranzas y pagos, que permite llevar un control detallado de las cuentas por cobrar y cuentas por pagar de una empresa, controlando de manera precisa el estado de cada cuenta, generando reportes, estados de cuenta y consultas en linea a la base de datos de la SUNAT y RENIEC, ademas de contar con un motor de alertas para notificar los pagos recibidos y los pagos por vencer.


## Funciones especificas del Software:

1. **Módulo de Gestión de Comprobantes de Compra (CxP)**:
   1.1. Registro de Comprobantes de Compra: El usuario puede registrar comprobantes de compra, tales como facturas, boletas de venta, notas de crédito, notas de débito y tickets.
   1.2. Gestión de Pagos a Proveedores: El usuario puede registrar pagos a proveedores, tales como pagos por adelantado, pagos parciales y pagos completos.
   1.3. Historial de Pagos a Proveedores: El usuario puede ver el historial de pagos realizados a proveedores.
   1.4. Reportes de Cuentas por Pagar: El usuario puede generar reportes de cuentas por pagar.
   1.5. Consultas a la SUNAT: El usuario puede consultar a la SUNAT para obtener información sobre los comprobantes de compra.
   
2. **Módulo de Gestión de Comprobantes de Venta (CxC)**:
   2.1. Registro de Comprobantes de Venta: El usuario puede registrar comprobantes de venta, tales como facturas, boletas de venta, notas de crédito, notas de débito y tickets.
   2.2. Gestión de Pagos a Clientes: El usuario puede registrar pagos a clientes, tales como pagos por adelantado, pagos parciales y pagos completos.
   2.3. Historial de Pagos a Clientes: El usuario puede ver el historial de pagos realizados a clientes.
   2.4. Reportes de Cuentas por Cobrar: El usuario puede generar reportes de cuentas por cobrar.
   2.5. Consultas a la SUNAT: El usuario puede consultar a la SUNAT para obtener información sobre los comprobantes de venta.

3. **Módulo de Gestión de Clientes**:
   3.1. Registro de Clientes: El usuario puede registrar clientes.
   3.2. Actualización de Datos de Clientes: El usuario puede actualizar los datos de los clientes.
   3.3. Eliminación de Clientes: El usuario puede eliminar clientes.
   3.4. Historial de Clientes: El usuario puede ver el historial de clientes.
   3.5. Consultas a RENIEC: El usuario puede consultar a RENIEC para obtener información sobre los clientes.

4. **Módulo de Gestión de Proveedores**:
   4.1. Registro de Proveedores: El usuario puede registrar proveedores.
   4.2. Actualización de Datos de Proveedores: El usuario puede actualizar los datos de los proveedores.
   4.3. Eliminación de Proveedores: El usuario puede eliminar proveedores.
   4.4. Historial de Proveedores: El usuario puede ver el historial de proveedores.
   4.5. Consultas a RENIEC: El usuario puede consultar a RENIEC para obtener información sobre los proveedores.
