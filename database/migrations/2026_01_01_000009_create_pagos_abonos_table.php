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
        Schema::create('pagos_abonos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_operacion', ['COMPRA_PAGO', 'VENTA_COBRO']);
            $table->foreignId('comprobante_compra_id')->nullable()->constrained('comprobantes_compras')->onDelete('cascade');
            $table->foreignId('comprobante_venta_id')->nullable()->constrained('comprobantes_ventas')->onDelete('cascade');
            $table->date('fecha_pago');
            $table->decimal('monto', 12, 2);
            $table->enum('metodo_pago', ['TRANSFERENCIA', 'EFECTIVO', 'YAPE', 'PLIN', 'DEPOSITO', 'CHEQUE', 'TARJETA', 'OTRO'])->default('TRANSFERENCIA');
            $table->string('nro_operacion', 100)->nullable();
            $table->string('banco', 100)->nullable();
            $table->string('comprobante_voucher', 255)->nullable();
            $table->text('observacion')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index('fecha_pago');
            $table->index('tipo_operacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_abonos');
    }
};
