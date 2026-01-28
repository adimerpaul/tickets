<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
class EventoHorario extends Model implements AuditableContract
{
    use AuditableTrait, SoftDeletes;
    protected $fillable = [
        'evento_id',
        'fecha','hora_inicio','hora_fin',
        'starts_at','ends_at',
        'capacidad','reservados',
        'activo','nota'
    ];

//    protected $casts = [
//        'activo' => 'boolean',
//        'fecha' => 'date',
//        'starts_at' => 'datetime',
//        'ends_at' => 'datetime',
//    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }
}
