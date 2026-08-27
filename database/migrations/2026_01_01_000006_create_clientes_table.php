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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_documento', ['RUC', 'DNI', 'CE', 'PASAPORTE'])->default('RUC');
            $table->string('numero_documento', 20)->unique();
            $table->string('razon_social_nombre', 255);
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('correo', 150)->nullable();
            $table->string('estado_sunat', 50)->default('ACTIVO')->nullable();
            $table->string('condicion_sunat', 50)->default('HABIDO')->nullable();
            $table->timestamps();

            $table->index('numero_documento');
            $table->index('razon_social_nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
