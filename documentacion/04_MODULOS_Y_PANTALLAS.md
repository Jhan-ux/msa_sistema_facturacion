# 🖥️ 04. Módulos y Diseño de Pantallas (Multi-Empresa & Multi-Sede)

## 1. Barra de Navegación Global (Doble Selector Corporativo)

En la parte superior de la interfaz, el usuario con permisos corporativos cuenta con dos selectores vinculados para cambiar de **Empresa** y **Sede** de forma instantánea:

```
+-------------------------------------------------------------------------------------------------------------------+
| [🏢 MSA CORPORATIVO] | 🏢 Empresa: [ MSA Talleres S.A.C. ▾ ] | 📍 Sede: [ Principal - La Victoria ▾ ] | 🔔 (4) | 👤 Admin ▾|
|                      |   • MSA Servicios Automotrices S.A.C. |   • Principal - La Victoria            |        |           |
|                      |   • MSA Import & Repuestos S.A.C.     |   • Taller Norte - Los Olivos          |        |           |
|                      |   • 🌐 Todas las Empresas (Global)    |   • 🌐 Todas las Sedes (Consolidado)   |        |           |
+-------------------------------------------------------------------------------------------------------------------+
```

---

## 2. Dashboard Principal (Tablero Financiero Corporativo)

El Dashboard se adapta según el contexto seleccionado (Consolidado de todas las empresas, una sola empresa o una sede en particular):

```
+-------------------------------------------------------------------------------------------------------------------+
| 📊 DASHBOARD FINANCIERO [🏢 Empresa: MSA SERVICIOS AUTOMOTRICES] [📍 Sede: Principal]             [📅 26/08/2026] |
+-------------------------------------------------------------------------------------------------------------------+
| [ 💳 CxC POR COBRAR ]    | [ 💸 CxP POR PAGAR ]    | [ 🟡 POR VENCER (7d) ]     | [ 🔴 VENCIDAS HOY ]             |
|   S/ 64,500.00           |   S/ 38,200.00          |   6 Comprobantes           |   2 Comprobantes                |
|   $ 3,400.00 USD         |   $ 1,150.00 USD        |   (Programar cobranza/pago)|   (Acción Urgente)              |
+-------------------------------------------------------------------------------------------------------------------+
| 🚨 BANDEJA CENTRAL DE ALERTAS DE VENCIMIENTO (Próximos 5 días y Vencidas)                                         |
| Empresa        | Sede           | Tipo  | RUC/DNI     | Razón Social         | Nro Comp. | Vence      | Saldo     |Est|
| MSA Talleres   | Principal      | [CxP] | 20512345678 | DISTRIBUIDORA MOTRIZ | F001-4920 | 28/08 (2d) | S/ 1,450  | 🟡 |
| MSA Repuestos  | Almacén Central| [CxC] | 20498765432 | TRANSPORTES DEL SUR  | F001-0192 | 20/08 (-6d)| S/ 3,800  | 🔴 |
+-------------------------------------------------------------------------------------------------------------------+
| 📊 DISTRIBUCIÓN POR ÁREA / CENTRO DE COSTOS                                                                       |
| • Taller: S/ 28,400.00 (44%)      • Cigüeñal: S/ 18,100.00 (28%)                                                  |
| • Repuestos: S/ 12,500.00 (19%)   • Ventas: S/ 5,500.00 (9%)                                                      |
+-------------------------------------------------------------------------------------------------------------------+
```

---

## 3. Módulo de Proveedores (Cuentas por Pagar - CxP)

### 3.1 Listado de Facturas de Proveedores
Permite auditar las cuentas por pagar filtradas por Empresa y Sede.

#### 🔍 Filtros Avanzados:
- **Empresa**: *(Si está en modo Global, permite elegir la empresa o ver todas)*.
- **Sede**: *(Filtra por sucursal de la empresa activa)*.
- **Estado de Pago**: `[Todos]` `[Pagados]` `[Con Adelanto]` `[Pendientes]` `[Vencidos]`
- **Área**: `[Todas]` `[Taller]` `[Ventas]` `[Repuestos]` `[Cigüeñal]` `[Administración]`
- **Moneda**: `[Todas]` `[Soles (S/)]` `[Dólares ($)]`

#### 📊 Columnas de la Tabla:
1. **Semáforo**: 🟢 Verde (En plazo/Pagado), 🟡 Amarillo (Vence en <= 5 días), 🔴 Rojo (Vencido).
2. **Empresa & Sede**: Badges identificadores.
3. **RUC & Proveedor**: Razón social y número de RUC.
4. **Comprobante**: Factura (`F001-1234`) / Boleta (`B001-456`).
5. **Área**: Taller, Repuestos, Ventas, Cigüeñal.
6. **F. Emisión & F. Vencimiento**: Con cálculo de días de mora o restantes.
7. **Monto Total**: `S/ 5,000.00` o `$ 1,500.00`.
8. **Monto Pagado / Adelantos**: `S/ 2,000.00` (Con enlace al historial de abonos).
9. **Saldo Pendiente**: `S/ 3,000.00`.
10. **Estado**: `PENDIENTE` / `CON ADELANTO` / `PAGADO` / `VENCIDO`.
11. **Acciones**:
    - 💰 **Registrar / Ver Adelantos** (Abre modal de pagos).
    - 📄 **Ver PDF / Voucher adjunto**.
    - ✏️ **Editar**.
    - 🗑️ **Anular**.

---

### 3.2 Modal de Control de Adelantos y Pagos a Proveedor
```
+---------------------------------------------------------------------------------+
| 💳 HISTORIAL DE PAGOS - FACTURA F001-4920 (DISTRIBUIDORA MOTRIZ)                |
| [Empresa: MSA SERVICIOS AUTOMOTRICES S.A.C.]  [Sede: Principal - La Victoria]   |
| Monto Total: S/ 5,000.00  |  Total Pagado: S/ 2,000.00  |  Saldo: S/ 3,000.00   |
+---------------------------------------------------------------------------------+
| HISTORIAL DE ABONOS:                                                            |
| # | Fecha      | Monto      | Método        | Nro Operación | Banco | Acciones  |
| 1 | 15/08/2026 | S/ 1,000.00| Transferencia | OP-9823412    | BCP   | 🗑️ Eliminar|
| 2 | 22/08/2026 | S/ 1,000.00| Transferencia | OP-9912093    | BBVA  | 🗑️ Eliminar|
+---------------------------------------------------------------------------------+
| ➕ REGISTRAR NUEVO ABONO / ADELANTO:                                             |
| Fecha Pago: [26/08/2026]     Monto a Pagar: [S/ 3,000.00] (Max Saldo)           |
| Método: [Transferencia v]   Banco: [BCP v]   Nro Operación: [12345678]          |
| Voucher: [Examinar archivo...]                                                  |
| Observación: [Adelanto del 50% para compra de cigüeñal y pistones]              |
|                                     [ Cancelar ]  [ 💾 Guardar Pago ]           |
+---------------------------------------------------------------------------------+
```

---

## 4. Módulo de Clientes (Cuentas por Cobrar - CxC)

### 4.1 Listado de Cobranzas a Clientes
- Visualiza el estado de las cuentas por cobrar segregadas por empresa emisora y sede.
- **Botón WhatsApp de Cobranza**: Genera mensaje automático incluyendo:
  - Nombre de la empresa emisora (**MSA**).
  - Nro. de comprobante y concepto del trabajo/repuesto.
  - Adelanto recibido y saldo pendiente de cobro.
  - **Cuentas bancarias oficiales de la empresa** para que el cliente deposite.

---

## 5. Módulo de Empresas y Sedes (Administración)

1. **Gestión de Empresas**:
   - Registro de RUC, Razón Social, Nombre Comercial, Dirección, Teléfono, Correo, Logo y Cuentas Bancarias.
2. **Gestión de Sedes / Sucursales**:
   - Creación de sedes asociadas a cada empresa con dirección y responsable.
3. **Asignación de Usuarios**:
   - Matriz de permisos para asignar a qué empresas y sedes tiene acceso cada usuario.
