<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoAbono extends Model
{
    use HasFactory;

    protected $table = 'pagos_abonos';

    protected $fillable = [
        'tipo_operacion',
        'comprobante_compra_id',
        'comprobante_venta_id',
        'fecha_pago',
        'monto',
        'metodo_pago',
        'nro_operacion',
        'banco',
        'comprobante_voucher',
        'observacion',
        'user_id',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto' => 'decimal:2',
    ];

    public function comprobanteCompra(): BelongsTo
    {
        return $this->belongsTo(ComprobanteCompra::class, 'comprobante_compra_id');
    }

    public function comprobanteVenta(): BelongsTo
    {
        return $this->belongsTo(ComprobanteVenta::class, 'comprobante_venta_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
