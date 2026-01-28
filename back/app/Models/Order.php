<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
class Order extends Model implements AuditableContract
{
    use AuditableTrait,SoftDeletes;
    protected $fillable = [
        'codigo_pedido','session_id','email','amount_total','currency','status',
        'metadata','items','dni','nombre_completo','nacionalidad','entrada_tipo',

        // 👇 nuevos / requeridos
        'evento_id','starts_at','horario_adulto_id','horario_nino_id','adults','kids',
    ];

    protected $casts = [
        'metadata' => 'array',
        'items' => 'array',
//        'paid_at' => 'datetime',
    ];
}
