<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VodafoneAuditoria extends Model
{
    public $timestamps = false; // Usas 'fecha' como timestamp personalizado

    protected $fillable = [
        'vodafone_id',
        'user_id',
        'accion',
        'campos_editados',
        'fecha',
    ];

    protected $casts = [
        'campos_editados' => 'array',
        'fecha' => 'datetime',
    ];

    public function registro()
    {
        return $this->belongsTo(Vodafone::class, 'vodafone_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
