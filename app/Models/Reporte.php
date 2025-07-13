<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    protected $fillable = [
        'nombre',
        'link',
        'orden',
        'cartera_id',
    ];

    public function cartera()
    {
        return $this->belongsTo(Cartera::class);
    }
}
