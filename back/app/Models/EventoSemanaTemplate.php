<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class EventoSemanaTemplate extends Model implements AuditableContract
{
    use AuditableTrait, SoftDeletes;

    protected $table = 'evento_semana_templates';

    protected $fillable = [
        'evento_id','dow','hora_inicio','hora_fin',
        'plan','precio','capacidad','activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'dow' => 'integer',
        'precio' => 'float',
        'capacidad' => 'integer',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }
}
