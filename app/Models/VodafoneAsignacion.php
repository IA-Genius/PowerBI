<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



class VodafoneAsignacion extends Model
{
    public $timestamps = false;
    protected $table = 'vodafone_asignaciones';

    protected $fillable = [
        'vodafone_id',
        'asignado_de_id',
        'asignado_a_id',
        'user_id',
        'motivo',
        'fecha',
    ];

    public function registroVodafone(): BelongsTo
    {
        return $this->belongsTo(Vodafone::class, 'vodafone_id');
    }

    public function asignadoDe(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_de_id');
    }

    public function asignadoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a_id');
    }

    public function usuarioCambio(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación: una asignación tiene muchos logs de auditoría
    public function auditorias()
    {
        return $this->hasMany(VodafoneAuditoria::class, 'asignacion_id');
    }
}
