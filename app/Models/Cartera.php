<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cartera extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'estado',
    ];

    public function reportes()
    {
        return $this->hasMany(Reporte::class);
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_cartera');
    }
}
