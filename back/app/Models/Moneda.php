<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Moneda extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $table = 'monedas';

    protected $fillable = ['codigo','nombre','simbolo','activo','orden'];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];
}
