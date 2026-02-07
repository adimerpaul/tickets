<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class EventoMoneda extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $table = 'evento_monedas';

    protected $fillable = [
        'evento_id','moneda_id','activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function evento() { return $this->belongsTo(Evento::class); }
    public function moneda() { return $this->belongsTo(Moneda::class, 'moneda_id'); }
}
