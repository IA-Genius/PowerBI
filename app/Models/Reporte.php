<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reporte extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'nombre',
        'link_desktop',
        'link_mobile',
        'icon',
        'orden',
        'cartera_id',
    ];

    protected $casts = [
        'orden' => 'integer',
    ];

    public function cartera()
    {
        return $this->belongsTo(Cartera::class);
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_reporte');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'reporte_user');
    }
}
