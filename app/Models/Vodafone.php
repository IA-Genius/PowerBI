<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Vodafone extends Model
{
    use SoftDeletes;

    protected $table = 'historial_registros_vodafone';

    protected $fillable = [
        'user_id',
        'upload_id',
        'asignado_a_id',
        'estado',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logImportacion()
    {
        return $this->belongsTo(LogImportacionVodafone::class, 'upload_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function asignadoA()
    {
        return $this->belongsTo(User::class, 'asignado_a_id');
    }
}
