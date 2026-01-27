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
        'session_id',
        'payment_intent_id',
        'email',
        'localizador',
        'amount_total',
        'currency',
        'status',
        'paid_at',
        'metadata',
        'items',
        'dni',
        'nombre_completo',
        'nacionalidad',
        'entrada_tipo',
        'codigo_pedido',
    ];

    protected $casts = [
        'metadata' => 'array',
        'items' => 'array',
        'paid_at' => 'datetime',
    ];
}
