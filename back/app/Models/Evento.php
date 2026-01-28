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
        'regla_nacionalidad','moneda'
    ];

    protected $casts = [
        'activo' => 'boolean',
//        'lat' => 'float',
//        'lng' => 'float',
    ];

    public function horarios()
    {
        return $this->hasMany(EventoHorario::class, 'evento_id')->orderBy('fecha')->orderBy('hora_inicio');
    }

    public function tickets()
    {
        return $this->hasMany(\App\Models\Order::class, 'evento_id')
            ->orderByDesc('paid_at')
            ->orderByDesc('id');
    }
}
