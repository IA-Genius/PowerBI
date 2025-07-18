<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vodafone extends Model
{
    protected $table = 'vodafone';

    protected $fillable = [
        'dni_nif_cif',
        'id_cliente',
        'observacion_smart',
        'oferta_comercial',
        'operador_actual',
        'telefono_contacto',
        'nombre_cliente',
        'direccion_instalacion',
        'fecha_creacion',
        'fecha_cierre',
        'observaciones_back_office',
        'tipificaciones',
        'observaciones_operaciones',
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'fecha_cierre' => 'date',
    ];
}
