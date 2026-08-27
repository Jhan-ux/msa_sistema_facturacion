<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ComprobanteCompra extends Model
{
    use HasFactory;

    protected $table = 'comprobantes_compras';

    protected $fillable = [
        'empresa_id',
        'sede_id',
        'proveedor_id',
        'area_id',
        'tipo_comprobante',
        'serie_correlativo',
        'fecha_emision',
        'fecha_vencimiento',
        'moneda',
        'monto_total',
        'monto_pagado',
        'saldo_pendiente',
        'estado_pago',
        'descripcion',
        'archivo_adjunto',
        'user_id',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoAbono::class, 'comprobante_compra_id')->orderBy('fecha_pago', 'desc');
    }

    /**
     * Días de diferencia hasta el vencimiento (positivo = faltan días, negativo = vencido)
     */
    public function getDiasRestantesAttribute(): int
    {
        $hoy = Carbon::today();
        $vencimiento = Carbon::parse($this->fecha_vencimiento);
        return $hoy->diffInDays($vencimiento, false);
    }

    /**
     * Color del semáforo: green, yellow, orange, red, secondary
     */
    public function getSemaforoColorAttribute(): string
    {
        if ($this->estado_pago === 'PAGADO') {
            return 'success';
        }

        $dias = $this->dias_restantes;
        $diasAlerta = $this->empresa->dias_alerta_vencimiento ?? 5;

        if ($dias < 0) {
            return 'danger'; // Vencido (Rojo)
        } elseif ($dias === 0) {
            return 'warning'; // Vence hoy (Naranja/Amarillo fuerte)
        } elseif ($dias <= $diasAlerta) {
            return 'warning'; // Por vencer (Amarillo)
        }

        return 'success'; // En plazo (Verde)
    }

    /**
     * Etiqueta amigable del semáforo
     */
    public function getSemaforoTextoAttribute(): string
    {
        if ($this->estado_pago === 'PAGADO') {
            return 'Pagado';
        }

        $dias = $this->dias_restantes;

        if ($dias < 0) {
            return 'Vencido hace ' . abs($dias) . ' d';
        } elseif ($dias === 0) {
            return 'Vence Hoy';
        }

        return 'Vence en ' . $dias . ' d';
    }
}
