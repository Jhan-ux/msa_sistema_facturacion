<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'razon_social_nombre',
        'direccion',
        'telefono',
        'correo',
        'estado_sunat',
        'condicion_sunat',
    ];

    public function comprobantesVentas(): HasMany
    {
        return $this->hasMany(ComprobanteVenta::class, 'cliente_id');
    }
}
