<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'session_id',
        'payment_intent_id',
        'email',
        'amount_total',
        'currency',
        'status',
        'paid_at',
        'metadata',
        'items',
    ];

    protected $casts = [
        'metadata' => 'array',
        'items' => 'array',
        'paid_at' => 'datetime',
    ];
}
