<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\EventoHorario;
use App\Models\EventoNacionalidad;
use App\Models\EventoTipoEntrada;
use App\Models\EventoPrecio;
use Illuminate\Http\Request;

class EventoCheckoutController extends Controller
{
    /**
     * GET /api/eventos/{evento}/checkout-data
     *
     * Devuelve:
     * - evento
     * - nacionalidades activas
     * - tipos de entrada activos
     * - horarios activos (sin precios)
     * - precios indexados por nacionalidad/tipo/segmento
     */
    public function checkoutData(Evento $evento)
    {
        // =========================
        // EVENTO
        // =========================
        $evento->load([
            'nacionalidades' => fn ($q) => $q->where('activo', true)->orderBy('orden'),
            'tiposEntrada'   => fn ($q) => $q->where('activo', true)->orderBy('orden'),
        ]);

        // =========================
        // HORARIOS (solo disponibilidad)
        // =========================
        $horarios = EventoHorario::where('evento_id', $evento->id)
            ->where('activo', true)
            ->whereNotNull('starts_at')
            ->get()
            ->map(function ($h) {
                return [
                    'id'          => $h->id,
                    'starts_at'   => $h->starts_at,
                    'fecha'       => $h->fecha ?? substr($h->starts_at, 0, 10),
                    'plan'        => strtoupper($h->plan), // ADULTO / NINO
                    'capacidad'   => (int)$h->capacidad,
                    'reservados'  => (int)$h->reservados,
                    'disponibles' => max(0, (int)$h->capacidad - (int)$h->reservados),
                ];
            });

        // =========================
        // PRECIOS (TABLA MAESTRA)
        // =========================
        $precios = EventoPrecio::where('evento_id', $evento->id)
            ->where('activo', true)
            ->get()
            ->map(function ($p) {
                return [
                    'nacionalidad_id' => $p->nacionalidad_id,
                    'tipo_entrada_id'=> $p->tipo_entrada_id,
                    'segmento'        => strtoupper($p->segmento), // ADULTO / NINO
                    'monedas' => [
                        'EGP'  => $p->egp_venta,
                        'EUR'  => $p->eur_venta,
                        'USD'  => $p->usd_venta,
                        'USDT' => $p->usdt_venta,
                    ],
                ];
            });

        return response()->json([
            'evento' => [
                'id'     => $evento->id,
                'nombre' => $evento->nombre,
                'slug'   => $evento->slug,
                'moneda' => strtoupper($evento->moneda),
            ],

            'nacionalidades' => $evento->nacionalidades->map(fn ($n) => [
                'id'     => $n->id,
                'nombre' => $n->nombre,
                'slug'   => $n->slug,
            ]),

            'tipos_entrada' => $evento->tiposEntrada->map(fn ($t) => [
                'id'     => $t->id,
                'nombre' => $t->nombre,
                'slug'   => $t->slug,
                'imagen' => $t->imagen,
            ]),

            'horarios' => $horarios,

            'precios' => $precios,
        ]);
    }
}
