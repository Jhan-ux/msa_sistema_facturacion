<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comprobantes_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('sede_id')->constrained('sedes')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('area_id')->constrained('areas')->onDelete('restrict')->onUpdate('cascade');
            $table->enum('tipo_comprobante', ['FACTURA', 'BOLETA', 'COTIZACION', 'NOTA_VENTA', 'OTRO'])->default('FACTURA');
            $table->string('serie_correlativo', 50);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->enum('moneda', ['PEN', 'USD'])->default('PEN');
            $table->decimal('monto_total', 12, 2)->default(0.00);
            $table->decimal('monto_cobrado', 12, 2)->default(0.00);
            $table->decimal('saldo_pendiente', 12, 2)->default(0.00);
            $table->enum('estado_pago', ['PENDIENTE', 'CON_ADELANTO', 'PAGADO', 'ANULADO'])->default('PENDIENTE');
            $table->text('descripcion')->nullable();
            $table->string('archivo_adjunto', 255)->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index('empresa_id');
            $table->index('sede_id');
            $table->index('fecha_vencimiento');
            $table->index('estado_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobantes_ventas');
    }
};
