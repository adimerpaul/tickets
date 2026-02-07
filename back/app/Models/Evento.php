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
        'regla_nacionalidad','moneda_id','idioma_id',

        // nuevo
        'slot_interval_min','semana_hora_inicio','semana_hora_fin','generar_semanas',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'slot_interval_min' => 'integer',
        'generar_semanas' => 'integer',
    ];

    public function horarios()
    {
        return $this->hasMany(EventoHorario::class, 'evento_id')->orderByDesc('starts_at');
    }
    // app/Models/Evento.php

    public function nacionalidades()
    {
        return $this->hasMany(\App\Models\EventoNacionalidad::class, 'evento_id')->orderBy('orden');
    }

    public function tiposEntrada()
    {
        return $this->hasMany(\App\Models\EventoTipoEntrada::class, 'evento_id')->orderBy('orden');
    }

    public function precios()
    {
        return $this->hasMany(\App\Models\EventoPrecio::class, 'evento_id');
    }

    public function monedas()
    {
        return $this->hasMany(\App\Models\EventoMoneda::class, 'evento_id');
    }

    public function moneda()
    {
        return $this->belongsTo(\App\Models\Moneda::class, 'moneda_id');
    }

    public function idioma()
    {
        return $this->belongsTo(\App\Models\Idioma::class, 'idioma_id');
    }

}
