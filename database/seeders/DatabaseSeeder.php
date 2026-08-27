<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Empresa;
use App\Models\Sede;
use App\Models\Area;
use App\Models\User;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\ComprobanteCompra;
use App\Models\ComprobanteVenta;
use App\Models\PagoAbono;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear Empresas
        $empresa1 = Empresa::create([
            'ruc' => '20601234567',
            'razon_social' => 'MSA SERVICIOS AUTOMOTRICES S.A.C.',
            'nombre_comercial' => 'MSA TALLERES',
            'direccion' => 'Av. Nicolás Arriola 1420, La Victoria, Lima',
            'telefono' => '(01) 472-8990 / 987-654-321',
            'correo' => 'administracion@msatalleres.pe',
            'cuentas_bancarias' => "• BCP Soles: 191-2345678-0-12 (CCI: 00219100234567801234)\n• BBVA Soles: 0011-0123-0100012345\n• Yape / Plin: 987 654 321 (A nombre de MSA Servicios)",
            'dias_alerta_vencimiento' => 5,
            'activo' => true,
        ]);

        $empresa2 = Empresa::create([
            'ruc' => '20609876543',
            'razon_social' => 'MSA IMPORT & REPUESTOS S.A.C.',
            'nombre_comercial' => 'MSA REPUESTOS',
            'direccion' => 'Av. Iquitos 850, La Victoria, Lima',
            'telefono' => '(01) 324-5566 / 998-112-233',
            'correo' => 'ventas@msarepuestos.pe',
            'cuentas_bancarias' => "• Interbank Soles: 200-3001234567 (CCI: 00320000300123456789)\n• BCP Dólares: 191-9876543-1-45\n• Yape: 998 112 233",
            'dias_alerta_vencimiento' => 7,
            'activo' => true,
        ]);

        // 2. Crear Sedes
        $sede1 = Sede::create([
            'empresa_id' => $empresa1->id,
            'nombre' => 'Sede Principal - La Victoria',
            'codigo' => 'SED-01',
            'direccion' => 'Av. Nicolás Arriola 1420',
            'telefono' => '987654321',
            'ciudad' => 'Lima',
            'activo' => true,
        ]);

        $sede2 = Sede::create([
            'empresa_id' => $empresa1->id,
            'nombre' => 'Sede Taller Norte - Los Olivos',
            'codigo' => 'SED-02',
            'direccion' => 'Av. Alfredo Mendiola 3500',
            'telefono' => '987654322',
            'ciudad' => 'Los Olivos, Lima',
            'activo' => true,
        ]);

        $sede3 = Sede::create([
            'empresa_id' => $empresa2->id,
            'nombre' => 'Sede Almacén Central',
            'codigo' => 'SED-03',
            'direccion' => 'Av. Iquitos 850',
            'telefono' => '998112233',
            'ciudad' => 'Lima',
            'activo' => true,
        ]);

        // 3. Crear Áreas / Centros de Costo
        $areaTaller = Area::create(['nombre_area' => 'Taller', 'descripcion' => 'Servicios mecánicos, mantenimiento y mano de obra']);
        $areaVentas = Area::create(['nombre_area' => 'Ventas', 'descripcion' => 'Ventas comerciales y facturación de servicios']);
        $areaRepuestos = Area::create(['nombre_area' => 'Repuestos', 'descripcion' => 'Venta y distribución de repuestos y autopartes']);
        $areaCiguenal = Area::create(['nombre_area' => 'Cigüeñal', 'descripcion' => 'Torno, rectificación de motores y cigüeñales']);
        $areaAdmin = Area::create(['nombre_area' => 'Administración', 'descripcion' => 'Gastos operativos, alquiler y servicios públicos']);

        // 4. Crear Usuarios
        $admin = User::create([
            'name' => 'Super Administrador MSA',
            'username' => 'admin',
            'email' => 'admin@msa.com',
            'password' => Hash::make('admin123'),
            'rol' => 'SUPERADMIN',
            'activo' => true,
        ]);
        $admin->empresas()->attach([$empresa1->id, $empresa2->id]);
        $admin->sedes()->attach([$sede1->id, $sede2->id, $sede3->id]);

        $contador = User::create([
            'name' => 'Contador General',
            'username' => 'contador',
            'email' => 'contabilidad@msa.com',
            'password' => Hash::make('contador123'),
            'rol' => 'CONTADOR',
            'activo' => true,
        ]);
        $contador->empresas()->attach([$empresa1->id, $empresa2->id]);
        $contador->sedes()->attach([$sede1->id, $sede2->id, $sede3->id]);

        // 5. Crear Proveedores
        $prov1 = Proveedor::create([
            'ruc' => '20512345678',
            'razon_social' => 'DISTRIBUIDORA DE REPUESTOS MOTRIZ S.A.C.',
            'direccion' => 'Av. México 1120, La Victoria',
            'telefono' => '945678123',
            'correo' => 'ventas@motrizrepuestos.pe',
            'estado_sunat' => 'ACTIVO',
            'condicion_sunat' => 'HABIDO',
        ]);

        $prov2 = Proveedor::create([
            'ruc' => '20491234567',
            'razon_social' => 'RECTIFICACIONES Y METALES DEL PERU S.R.L.',
            'direccion' => 'Av. Canadá 1840, San Luis',
            'telefono' => '981234567',
            'correo' => 'facturacion@rectimetales.pe',
            'estado_sunat' => 'ACTIVO',
            'condicion_sunat' => 'HABIDO',
        ]);

        $prov3 = Proveedor::create([
            'ruc' => '20603344556',
            'razon_social' => 'LUBRICANTES Y FILTROS TOTAL S.A.',
            'direccion' => 'Av. Argentina 2200, Callao',
            'telefono' => '976543210',
            'correo' => 'cobranzas@lubritotal.pe',
            'estado_sunat' => 'ACTIVO',
            'condicion_sunat' => 'HABIDO',
        ]);

        // 6. Crear Clientes
        $cli1 = Cliente::create([
            'tipo_documento' => 'RUC',
            'numero_documento' => '20498765432',
            'razon_social_nombre' => 'TRANSPORTES DEL SUR EXPRESS S.A.C.',
            'direccion' => 'Av. Circunvalación 450, Ate',
            'telefono' => '987654321',
            'correo' => 'administracion@transportessur.pe',
            'estado_sunat' => 'ACTIVO',
            'condicion_sunat' => 'HABIDO',
        ]);

        $cli2 = Cliente::create([
            'tipo_documento' => 'RUC',
            'numero_documento' => '20556677889',
            'razon_social_nombre' => 'CONSTRUCTORA E INMOBILIARIA LOS ANDES S.A.C.',
            'direccion' => 'Av. Javier Prado Este 2400, San Borja',
            'telefono' => '998877665',
            'correo' => 'pagos@losandesconstructora.pe',
            'estado_sunat' => 'ACTIVO',
            'condicion_sunat' => 'HABIDO',
        ]);

        $cli3 = Cliente::create([
            'tipo_documento' => 'DNI',
            'numero_documento' => '45678912',
            'razon_social_nombre' => 'JUAN CARLOS RAMOS PEREZ',
            'direccion' => 'Jr. Huánuco 560, La Victoria',
            'telefono' => '912345678',
            'correo' => 'jramos_mecanica@gmail.com',
            'estado_sunat' => 'ACTIVO',
            'condicion_sunat' => 'HABIDO',
        ]);

        // 7. Crear Facturas de Compras (Proveedores - CxP)
        // Compra 1: Con Adelanto
        $compra1 = ComprobanteCompra::create([
            'empresa_id' => $empresa1->id,
            'sede_id' => $sede1->id,
            'proveedor_id' => $prov1->id,
            'area_id' => $areaRepuestos->id,
            'tipo_comprobante' => 'FACTURA',
            'serie_correlativo' => 'F001-0004523',
            'fecha_emision' => Carbon::now()->subDays(10)->toDateString(),
            'fecha_vencimiento' => Carbon::now()->addDays(3)->toDateString(), // Próximo a vencer (Amarillo)
            'moneda' => 'PEN',
            'monto_total' => 5000.00,
            'monto_pagado' => 2000.00,
            'saldo_pendiente' => 3000.00,
            'estado_pago' => 'CON_ADELANTO',
            'descripcion' => 'Compra de pistones, válvulas y kit de distribución',
            'user_id' => $admin->id,
        ]);

        PagoAbono::create([
            'tipo_operacion' => 'COMPRA_PAGO',
            'comprobante_compra_id' => $compra1->id,
            'fecha_pago' => Carbon::now()->subDays(8)->toDateString(),
            'monto' => 2000.00,
            'metodo_pago' => 'TRANSFERENCIA',
            'nro_operacion' => 'OP-984210',
            'banco' => 'BCP',
            'observacion' => 'Adelanto del 40% para despacho de repuestos',
            'user_id' => $admin->id,
        ]);

        // Compra 2: Vencida
        ComprobanteCompra::create([
            'empresa_id' => $empresa1->id,
            'sede_id' => $sede2->id,
            'proveedor_id' => $prov2->id,
            'area_id' => $areaCiguenal->id,
            'tipo_comprobante' => 'FACTURA',
            'serie_correlativo' => 'F002-0001890',
            'fecha_emision' => Carbon::now()->subDays(25)->toDateString(),
            'fecha_vencimiento' => Carbon::now()->subDays(5)->toDateString(), // Vencido hace 5 días (Rojo)
            'moneda' => 'PEN',
            'monto_total' => 3200.00,
            'monto_pagado' => 0.00,
            'saldo_pendiente' => 3200.00,
            'estado_pago' => 'PENDIENTE',
            'descripcion' => 'Servicio de rectificado de cigüeñal y bancada de motor Scania',
            'user_id' => $admin->id,
        ]);

        // Compra 3: Pagada al 100%
        $compra3 = ComprobanteCompra::create([
            'empresa_id' => $empresa2->id,
            'sede_id' => $sede3->id,
            'proveedor_id' => $prov3->id,
            'area_id' => $areaTaller->id,
            'tipo_comprobante' => 'FACTURA',
            'serie_correlativo' => 'F001-0009921',
            'fecha_emision' => Carbon::now()->subDays(15)->toDateString(),
            'fecha_vencimiento' => Carbon::now()->subDays(2)->toDateString(),
            'moneda' => 'USD',
            'monto_total' => 850.00,
            'monto_pagado' => 850.00,
            'saldo_pendiente' => 0.00,
            'estado_pago' => 'PAGADO',
            'descripcion' => 'Lote de aceites sintéticos 15W40 y filtros de petróleo',
            'user_id' => $admin->id,
        ]);

        PagoAbono::create([
            'tipo_operacion' => 'COMPRA_PAGO',
            'comprobante_compra_id' => $compra3->id,
            'fecha_pago' => Carbon::now()->subDays(3)->toDateString(),
            'monto' => 850.00,
            'metodo_pago' => 'TRANSFERENCIA',
            'nro_operacion' => 'OP-778219',
            'banco' => 'BCP Dólares',
            'observacion' => 'Cancelación total de factura',
            'user_id' => $admin->id,
        ]);

        // 8. Crear Facturas de Ventas (Clientes - CxC)
        // Venta 1: Con Adelanto (Taller)
        $venta1 = ComprobanteVenta::create([
            'empresa_id' => $empresa1->id,
            'sede_id' => $sede1->id,
            'cliente_id' => $cli1->id,
            'area_id' => $areaTaller->id,
            'tipo_comprobante' => 'FACTURA',
            'serie_correlativo' => 'F001-0000102',
            'fecha_emision' => Carbon::now()->subDays(7)->toDateString(),
            'fecha_vencimiento' => Carbon::now()->addDays(2)->toDateString(), // Por vencer (Amarillo)
            'moneda' => 'PEN',
            'monto_total' => 8500.00,
            'monto_cobrado' => 4000.00,
            'saldo_pendiente' => 4500.00,
            'estado_pago' => 'CON_ADELANTO',
            'descripcion' => 'Reparación integral de caja de cambios y mantenimiento de frenos de flota',
            'user_id' => $admin->id,
        ]);

        PagoAbono::create([
            'tipo_operacion' => 'VENTA_COBRO',
            'comprobante_venta_id' => $venta1->id,
            'fecha_pago' => Carbon::now()->subDays(6)->toDateString(),
            'monto' => 4000.00,
            'metodo_pago' => 'TRANSFERENCIA',
            'nro_operacion' => 'TR-1192834',
            'banco' => 'BCP',
            'observacion' => 'Adelanto del 50% al ingresar vehículo a taller',
            'user_id' => $admin->id,
        ]);

        // Venta 2: Vencida (Cigüeñal)
        ComprobanteVenta::create([
            'empresa_id' => $empresa1->id,
            'sede_id' => $sede1->id,
            'cliente_id' => $cli2->id,
            'area_id' => $areaCiguenal->id,
            'tipo_comprobante' => 'FACTURA',
            'serie_correlativo' => 'F001-0000103',
            'fecha_emision' => Carbon::now()->subDays(20)->toDateString(),
            'fecha_vencimiento' => Carbon::now()->subDays(6)->toDateString(), // Vencida hace 6 días (Rojo)
            'moneda' => 'PEN',
            'monto_total' => 4800.00,
            'monto_cobrado' => 1000.00,
            'saldo_pendiente' => 3800.00,
            'estado_pago' => 'CON_ADELANTO',
            'descripcion' => 'Rectificación de cigüeñal y encamisado de bloque para maquinaria pesada',
            'user_id' => $admin->id,
        ]);

        // Venta 3: Pagada (Repuestos)
        $venta3 = ComprobanteVenta::create([
            'empresa_id' => $empresa2->id,
            'sede_id' => $sede3->id,
            'cliente_id' => $cli3->id,
            'area_id' => $areaRepuestos->id,
            'tipo_comprobante' => 'BOLETA',
            'serie_correlativo' => 'B001-0000450',
            'fecha_emision' => Carbon::now()->subDays(5)->toDateString(),
            'fecha_vencimiento' => Carbon::now()->toDateString(),
            'moneda' => 'PEN',
            'monto_total' => 1200.00,
            'monto_cobrado' => 1200.00,
            'saldo_pendiente' => 0.00,
            'estado_pago' => 'PAGADO',
            'descripcion' => 'Venta de kit de embrague y fajas de alternador',
            'user_id' => $admin->id,
        ]);

        PagoAbono::create([
            'tipo_operacion' => 'VENTA_COBRO',
            'comprobante_venta_id' => $venta3->id,
            'fecha_pago' => Carbon::now()->subDays(5)->toDateString(),
            'monto' => 1200.00,
            'metodo_pago' => 'YAPE',
            'nro_operacion' => 'YAPE-99231',
            'observacion' => 'Pago al contado por Yape',
            'user_id' => $admin->id,
        ]);
    }
}
