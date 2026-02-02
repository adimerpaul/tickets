<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
class EventoNacionalidad extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $table = 'evento_nacionalidades';

    protected $fillable = [
        'evento_id','nombre','slug','activo','orden'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }
}
