<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'rol',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * Empresas a las que tiene acceso el usuario
     */
    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresa_user');
    }

    /**
     * Sedes a las que tiene acceso el usuario
     */
    public function sedes(): BelongsToMany
    {
        return $this->belongsToMany(Sede::class, 'sede_user');
    }

    public function isSuperAdmin(): bool
    {
        return $this->rol === 'SUPERADMIN';
    }

    public function isAdminEmpresa(): bool
    {
        return $this->rol === 'ADMIN_EMPRESA' || $this->isSuperAdmin();
    }

    public function isContador(): bool
    {
        return in_array($this->rol, ['CONTADOR', 'ADMIN_EMPRESA', 'SUPERADMIN']);
    }
}
