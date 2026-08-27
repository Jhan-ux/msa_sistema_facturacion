# 🏢📊 MSA - Sistema de Gestión Contable y Facturación
## Arquitectura Multi-Empresa & Multi-Sede | Laravel 11 Framework

Bienvenido a la documentación oficial del sistema **MSA - Facturación y Control Contable Corporativo**. El sistema está diseñado con una arquitectura **Multi-Empresa y Multi-Sede**, permitiendo a un grupo empresarial o estudio contable administrar múltiples razones sociales/empresas de manera centralizada o independiente.

---

## 🏛️ Jerarquía de la Arquitectura Corporativa

```mermaid
graph TD
    SuperAdmin([Super Administrador / Contador Corporativo]) --> Switcher[Selector Global: Empresa y Sede]
    
    Switcher --> Emp1[🏢 Empresa 1: MSA Servicios Automotrices S.A.C. (RUC: 20601111111)]
    Switcher --> Emp2[🏢 Empresa 2: MSA Rectificaciones y Cigüeñal S.A.C. (RUC: 20602222222)]
    Switcher --> EmpN[🏢 Empresa N: Distribuidora de Repuestos S.A.C.]

    Emp1 --> Sede1A[📍 Sede 1: Principal - La Victoria]
    Emp1 --> Sede1B[📍 Sede 2: Taller Norte - Los Olivos]

    Emp2 --> Sede2A[📍 Sede Única: Planta de Rectificaciones]

    Sede1A --> Ops1[CxP Proveedores | CxC Clientes | Adelantos | Áreas]
    Sede1B --> Ops2[CxP Proveedores | CxC Clientes | Adelantos | Áreas]
    Sede2A --> Ops3[CxP Proveedores | CxC Clientes | Adelantos | Áreas]
```

---

## 📑 Índice de la Documentación

1. **[01_REQUERIMIENTOS_Y_ALCANCE.md](file:///c:/xampp/htdocs/msa_sistema_facturacion/documentacion/01_REQUERIMIENTOS_Y_ALCANCE.md)**
   - Especificación del modelo **Multi-Empresa y Multi-Sede**.
   - Aislamiento de datos fiscales por Empresa (RUC, Razón Social, Logo, Cuentas Bancarias).
   - Gestión de **Proveedores (CxP)** y **Clientes (CxC)** por empresa y sede.
   - Control de estados: *Pagados*, *Con Adelanto / Parcial*, *Pendientes* y *Vencidos*.
   - Áreas operativas: **Taller**, **Ventas**, **Repuestos**, **Cigüeñal (Cigüeña)**, etc.

2. **[02_ARQUITECTURA_Y_DISENO_TECNICO.md](file:///c:/xampp/htdocs/msa_sistema_facturacion/documentacion/02_ARQUITECTURA_Y_DISENO_TECNICO.md)**
   - Stack: **Laravel 11**, PHP 8.2+, MySQL/MariaDB, Eloquent ORM, Blade, Bootstrap 5 + DataTables.
   - Patrón **Multi-Tenant con Scopes de Eloquent** (`EmpresaScope` y `SedeScope`).
   - Middleware de contexto activo (`SetEmpresaSedeMiddleware`).
   - Estructura de carpetas modular en Laravel.

3. **[03_MODELO_DE_DATOS_Y_SQL.md](file:///c:/xampp/htdocs/msa_sistema_facturacion/documentacion/03_MODELO_DE_DATOS_Y_SQL.md)**
   - Diagrama Entidad-Relación (DER) Multi-Empresa / Multi-Sede.
   - Tabla `empresas`, `sedes`, `empresa_user`, `sede_user`.
   - Script SQL DDL completo listo para ejecutar en MySQL/phpMyAdmin.

4. **[04_MODULOS_Y_PANTALLAS.md](file:///c:/xampp/htdocs/msa_sistema_facturacion/documentacion/04_MODULOS_Y_PANTALLAS.md)**
   - Selector doble en Navbar: `[ Empresa: MSA Repuestos ▾ ]` y `[ Sede: Principal ▾ ]`.
   - Dashboard financiero Corporativo (Consolidado de todas las empresas) y Dashboard por Empresa/Sede.
   - Modales de registro con consulta SUNAT, historial de abonos y semáforos de alerta.
   - Módulo de **Mi Perfil & Seguridad** (Gestión de datos de usuario y cambio de contraseña).

5. **[05_INTEGRACIONES_Y_ALERTAS.md](file:///c:/xampp/htdocs/msa_sistema_facturacion/documentacion/05_INTEGRACIONES_Y_ALERTAS.md)**
   - Servicio `SunatService.php` para consulta oficial de RUC y DNI.
   - Motor de Semáforos de Vencimiento (🟢 Verde, 🟡 Amarillo, 🟠 Naranja, 🔴 Rojo).
   - Generación de mensajes y enlaces de cobro por WhatsApp personalizados por empresa.
