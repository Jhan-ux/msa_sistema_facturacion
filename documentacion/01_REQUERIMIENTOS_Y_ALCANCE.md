# 📋 01. Requerimientos y Alcance del Sistema (Multi-Empresa & Multi-Sede)

## 1. Visión General
El **Sistema MSA de Facturación y Control Contable** es una plataforma corporativa desarrollada en **Laravel 11**, diseñada para gestionar integralmente múltiples razones sociales (**Multi-Empresa**) y sus respectivas sucursales físicas (**Multi-Sede**).

Permite a los directores, contadores generales y asistentes contables monitorear de manera aislada o consolidada las **Cuentas por Pagar (Proveedores)**, las **Cuentas por Cobrar (Clientes)**, los **Adelantos/Abonos recibidos y entregados**, y las **Alertas de Vencimiento**.

---

## 2. Requerimiento Multi-Empresa & Multi-Sede

### 2.1 Módulo de Empresas (Razones Sociales)
Cada empresa registrada en el sistema cuenta con:
- **RUC propio**: Registro Único de Contribuyentes (11 dígitos).
- **Razón Social** y **Nombre Comercial**.
- **Dirección Fiscal**, Teléfono y Correo corporativo.
- **Logo institucional**: Para membretar reportes en PDF y estados de cuenta.
- **Cuentas Bancarias de la Empresa**: BCP, BBVA, Interbank, Yape/Plin (para adjuntar en mensajes de cobranza).
- **Moneda Base y Configuración Fiscal**: Soles (`PEN`) y Dólares (`USD`).

### 2.2 Jerarquía Empresa ➔ Sedes
- Una **Empresa** puede tener **una o múltiples Sedes/Sucursales** (ej. *Sede Principal*, *Sucursal Taller Norte*, *Almacén Central*).
- Los comprobantes de compra (CxP) y comprobantes de venta (CxC) están vinculados a una **Empresa** y a una **Sede**.

### 2.3 Niveles de Acceso y Roles de Usuario:
1. **Super Administrador / Contador Corporativo**:
   - Acceso a **Todas las Empresas** y **Todas las Sedes**.
   - Puede ver reportes consolidados del grupo empresarial o alternar entre empresas con el selector de la barra superior.
2. **Administrador / Contador de Empresa**:
   - Acceso exclusivo a su(s) empresa(s) asignada(s) y a todas sus sedes.
3. **Asistente Contable / Cajero por Sede**:
   - Acceso restringido a una empresa y sede específica.

---

## 3. Requerimientos Funcionales por Módulo

### 3.1 Módulo de Proveedores (Cuentas por Pagar - CxP)
Gestiona todas las facturas y compras realizadas a proveedores por cada empresa y sede.

#### 📌 Campos Obligatorios y Opcionales:
1. **Empresa & Sede**: Selección de la empresa compradora y la sede donde se recibió el bien/servicio.
2. **RUC**: 11 dígitos con botón de autocompletado en **SUNAT**.
3. **Razón Social**: Nombre fiscal del proveedor.
4. **Tipo y Nro. de Comprobante**: Factura, Boleta de Venta, Recibo por Honorarios, Nota de Venta (Ej: `F001-004523`).
5. **Fecha de Emisión**: Fecha de emisión del comprobante.
6. **Fecha de Vencimiento**: Fecha límite de pago acordada con el proveedor.
7. **Monto Total**: Importe total del comprobante.
8. **Moneda**: Soles (`PEN - S/`) o Dólares (`USD - $`).
9. **Área / Centro de Costo**:
   - Taller
   - Ventas
   - Repuestos
   - Cigüeñal (Cigüeña)
   - Administración / Otros
10. **Descripción**: Detalle opcional del concepto de la compra.
11. **Historial de Adelantos y Pagos**:
    - Permite registrar pagos parciales/adelantos sucesivos.
    - Campos por abono: *Fecha de pago*, *Monto abonado*, *Método de pago* (Transferencia, Efectivo, Yape, Plin, Cheque), *Nro de Operación / Banco* y *Voucher adjunto*.
12. **Estado de Pago**:
    - `PENDIENTE`: Monto pagado = 0 y dentro de fecha.
    - `CON ADELANTO`: Monto pagado parcial ($0 < \text{Pagado} < \text{Total}$).
    - `PAGADO`: Cancelado al 100%.
    - `VENCIDO`: Fecha de vencimiento superada con saldo pendiente.
13. **Alerta de Vencimiento**: Semáforo visual (🟢 Verde, 🟡 Amarillo, 🟠 Naranja, 🔴 Rojo).

---

### 3.2 Módulo de Clientes (Cuentas por Cobrar - CxC)
Controla las ventas a crédito, cotizaciones y servicios facturados a clientes.

#### 📌 Campos Obligatorios y Opcionales:
1. **Empresa & Sede**: Empresa emisora y sede donde se atendió al cliente.
2. **RUC / DNI**: Documento de identidad con botón de consulta oficial en **SUNAT / RENIEC**.
3. **Razón Social / Nombre Completo**: Obtenido desde SUNAT o manual.
4. **Tipo y Nro. de Comprobante**: Factura (`F001-XXXXX`), Boleta (`B001-XXXXX`), Cotización o Nota de Venta.
5. **Fecha de Emisión**: Fecha de la venta o servicio.
6. **Fecha de Vencimiento**: Plazo máximo de crédito al cliente.
7. **Monto Total**: Importe facturado.
8. **Moneda**: Soles (`PEN - S/`) o Dólares (`USD - $`).
9. **Área de Origen**: Taller, Ventas, Repuestos, Cigüeñal (Cigüeña), etc.
10. **Descripción**: Detalle del trabajo de rectificación, mecánica o repuestos.
11. **Contacto**: Teléfono(s) (para WhatsApp) y Correo(s) electrónico(s).
12. **Historial de Adelantos y Cobranzas**:
    - Registro de cada abono/adelanto que entrega el cliente.
    - Cálculo dinámico: **Monto Total**, **Total Adelantado**, **Saldo por Cobrar** y **Última Fecha de Cobro**.
13. **Estado de Cobranza**: `PENDIENTE`, `CON ADELANTO`, `PAGADO / COBRADO` y `VENCIDO`.
14. **Alerta de Cobranza**: Notificación visual y botón directo para enviar recordatorio de cobro por **WhatsApp** con las cuentas bancarias de la empresa emisora.

---

### 3.3 Consulta Oficial de RUC en SUNAT
- Botón `[ 🔍 Buscar SUNAT ]` junto al campo RUC.
- Autocompleta en segundos: Razón Social, Dirección Fiscal, Estado (`ACTIVO`/`BAJA`), Condición (`HABIDO`/`NO HABIDO`) y Ubigeo.

---

### 3.4 Dashboard y Reportes Corporativos
- **Filtro Jerárquico en Navbar**:
  - `[ Empresa: Todas v / Empresa 1 / Empresa 2 ]`
  - `[ Sede: Todas v / Sede Principal / Taller Norte ]`
- **KPIs Consolidados o Individuales**:
  - Cuentas por Cobrar totales (PEN y USD).
  - Cuentas por Pagar totales (PEN y USD).
  - Facturas próximas a vencer (7 días) y vencidas.
  - Distribución de ingresos y egresos por **Área** (Taller, Repuestos, Cigüeñal, Ventas).
- **Exportación**:
  - Reportes a **Excel (.xlsx)** estructurados para contabilidad y libros electrónicos.
  - Estados de cuenta en **PDF** membretados con el logo y RUC de la empresa seleccionada.
