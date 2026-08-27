<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'ruc',
        'razon_social',
        'nombre_comercial',
        'direccion',
        'telefono',
        'correo',
        'logo_url',
        'cuentas_bancarias',
        'dias_alerta_vencimiento',
        'activo',
    ];

    protected $casts = [
        'dias_alerta_vencimiento' => 'integer',
        'activo' => 'boolean',
    ];

    public function sedes(): HasMany
    {
        return $this->hasMany(Sede::class, 'empresa_id');
    }

    public function comprobantesCompras(): HasMany
    {
        return $this->hasMany(ComprobanteCompra::class, 'empresa_id');
    }

    public function comprobantesVentas(): HasMany
    {
        return $this->hasMany(ComprobanteVenta::class, 'empresa_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'empresa_user');
    }
}
