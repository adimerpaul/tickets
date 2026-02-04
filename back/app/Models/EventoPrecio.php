<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class EventoPrecio extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $table = 'evento_precios';

    protected $fillable = [
        'evento_id','nacionalidad_id','tipo_entrada_id','segmento_id','moneda_id',
        'compra','venta','activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'compra' => 'float',
        'venta'  => 'float',
    ];

    public function evento() { return $this->belongsTo(Evento::class); }
    public function nacionalidad() { return $this->belongsTo(EventoNacionalidad::class, 'nacionalidad_id'); }
    public function tipoEntrada() { return $this->belongsTo(EventoTipoEntrada::class, 'tipo_entrada_id'); }
    public function segmento() { return $this->belongsTo(EventoSegmento::class, 'segmento_id'); }
    public function moneda() { return $this->belongsTo(Moneda::class, 'moneda_id'); }
}
