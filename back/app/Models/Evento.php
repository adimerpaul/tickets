<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Evento extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'nombre','slug','descripcion',
        'pais','ciudad','ubicacion','lat','lng',
        'activo','imagen','categoria','orden',
        'regla_nacionalidad','moneda',

        // nuevo
        'slot_interval_min','semana_hora_inicio','semana_hora_fin','generar_semanas',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'slot_interval_min' => 'integer',
        'generar_semanas' => 'integer',
    ];

    public function semanaTemplates()
    {
        return $this->hasMany(EventoSemanaTemplate::class, 'evento_id')->orderBy('dow')->orderBy('hora_inicio');
    }

    public function horarios()
    {
        return $this->hasMany(EventoHorario::class, 'evento_id')->orderByDesc('starts_at');
    }

    public function tickets()
    {
        return $this->hasMany(\App\Models\Order::class, 'evento_id')
            ->orderByDesc('paid_at')
            ->orderByDesc('id');
    }
}
