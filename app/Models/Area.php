<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';

    protected $fillable = [
        'nombre_area',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function comprobantesCompras(): HasMany
    {
        return $this->hasMany(ComprobanteCompra::class, 'area_id');
    }

    public function comprobantesVentas(): HasMany
    {
        return $this->hasMany(ComprobanteVenta::class, 'area_id');
    }
}
