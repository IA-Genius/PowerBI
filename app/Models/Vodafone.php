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
        'trazabilidad',

        // Nuevos campos de SmartClient / filtrado
        'marca_base',
        'origen_motivo_cancelacion',
        'nombre_cliente',
        'dni_cliente',
        'orden_trabajo_anterior',
        'telefono_principal',
        'telefono_adicional',
        'correo_referencia',
        'direccion_historico',
        'observaciones',
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

    public function asignado_a()
    {
        return $this->belongsTo(User::class, 'asignado_a_id');
    }
}
