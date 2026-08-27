<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Sede extends Model
{
    use HasFactory;

    protected $table = 'sedes';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'ciudad',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function comprobantesCompras(): HasMany
    {
        return $this->hasMany(ComprobanteCompra::class, 'sede_id');
    }

    public function comprobantesVentas(): HasMany
    {
        return $this->hasMany(ComprobanteVenta::class, 'sede_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'sede_user');
    }
}
