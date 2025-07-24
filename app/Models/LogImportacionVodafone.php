<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LogImportacionVodafone extends Model
{
    use HasFactory;

    protected $table = 'log_importacion_vodafone';

    protected $fillable = [
        'user_id',
        'nombre_archivo',
        'cantidad_registros',
    ];

    public function registros()
    {
        return $this->hasMany(Vodafone::class, 'upload_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
