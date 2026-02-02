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
        'evento_id','nacionalidad_id','tipo_entrada_id','segmento',
        'egp_compra','egp_venta',
        'eur_compra','eur_venta',
        'usd_compra','usd_venta',
        'usdt_compra','usdt_venta',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',

        'egp_compra' => 'float', 'egp_venta' => 'float',
        'eur_compra' => 'float', 'eur_venta' => 'float',
        'usd_compra' => 'float', 'usd_venta' => 'float',
        'usdt_compra' => 'float', 'usdt_venta' => 'float',
    ];

    public function evento() { return $this->belongsTo(Evento::class); }
    public function nacionalidad() { return $this->belongsTo(EventoNacionalidad::class, 'nacionalidad_id'); }
    public function tipoEntrada() { return $this->belongsTo(EventoTipoEntrada::class, 'tipo_entrada_id'); }
}
