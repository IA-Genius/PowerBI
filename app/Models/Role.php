<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Cartera;   // ← Importa
use App\Models\Reporte;   // ← Importa

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = ['name'];

    public function carteras()
    {
        return $this->belongsToMany(Cartera::class, 'role_cartera');
    }

    public function reportes()
    {
        return $this->belongsToMany(Reporte::class, 'role_reporte');
    }
}
