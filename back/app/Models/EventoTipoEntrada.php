<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
class EventoTipoEntrada extends Model implements AuditableContract
{
    use SoftDeletes , AuditableTrait;

    protected $table = 'evento_tipos_entrada';

    protected $fillable = [
        'evento_id', 'nombre', 'slug', 'activo', 'orden', 'imagen'
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
