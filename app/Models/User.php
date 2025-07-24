<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Cartera;
use App\Models\Reporte;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'active'            => 'boolean',
    ];

    /**
     * Relación pivote user ⇆ reportes
     */
    public function reportes()
    {
        return $this
            ->belongsToMany(Reporte::class, 'reporte_user')
            ->with('cartera');
    }

    /**
     * Relación pivote user ⇆ carteras
     */
    public function carteras()
    {
        return $this
            ->belongsToMany(Cartera::class, 'cartera_user');
    }
    public function getEffectiveCarteras()
    {
        return $this->roles->flatMap->carteras
            ->merge($this->carteras)
            ->unique('id')
            ->filter(fn($cartera) => $cartera->estado)
            ->values();
    }


    public function getEffectiveReportes()
    {
        return $this->roles->flatMap->reportes
            ->merge($this->reportes)
            ->unique('id')
            ->values();
    }

    public function importacionesVodafone()
    {
        return $this->hasMany(LogImportacionVodafone::class);
    }
}
