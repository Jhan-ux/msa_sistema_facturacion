<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'ruc',
        'razon_social',
        'direccion',
        'telefono',
        'correo',
        'estado_sunat',
        'condicion_sunat',
    ];

    public function comprobantesCompras(): HasMany
    {
        return $this->hasMany(ComprobanteCompra::class, 'proveedor_id');
    }
}
