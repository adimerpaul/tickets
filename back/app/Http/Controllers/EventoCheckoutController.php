<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\EventoHorario;
use App\Models\EventoNacionalidad;
use App\Models\EventoTipoEntrada;
use App\Models\EventoPrecio;
use App\Models\EventoSegmento;
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
            'moneda:id,codigo',
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
            ->with(['segmento:id,slug,activo', 'moneda:id,codigo'])
            ->get();

        $precios = $precios->reduce(function ($acc, $p) {
            if ($p->segmento && !$p->segmento->activo) return $acc;
            $seg = strtoupper($p->segmento?->slug ?? '');
            if ($seg === '') return $acc;

            $key = $p->nacionalidad_id . '|' . $p->tipo_entrada_id . '|' . $seg;
            if (!isset($acc[$key])) {
                $acc[$key] = [
                    'nacionalidad_id' => $p->nacionalidad_id,
                    'tipo_entrada_id' => $p->tipo_entrada_id,
                    'segmento' => $seg,
                    'monedas' => [],
                ];
            }

            $code = strtoupper($p->moneda?->codigo ?? '');
            if ($code !== '') {
                $acc[$key]['monedas'][$code] = (float) $p->venta;
            }

            return $acc;
        }, []);

        $precios = array_values($precios);

        $segmentos = EventoSegmento::where('evento_id', $evento->id)
            ->where('activo', true)
            ->orderBy('orden')->orderBy('id')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'nombre' => $s->nombre,
                'slug' => $s->slug,
            ]);

        return response()->json([
            'evento' => [
                'id'     => $evento->id,
                'nombre' => $evento->nombre,
                'slug'   => $evento->slug,
                'moneda' => strtoupper($evento->moneda?->codigo ?? ''),
                'moneda_id' => $evento->moneda_id,
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

            'segmentos' => $segmentos,

            'precios' => $precios,
        ]);
    }
}
