<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderEmailHistory extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'subject',
        'to_email',
        'pdf_path',
        'pdf_name',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
