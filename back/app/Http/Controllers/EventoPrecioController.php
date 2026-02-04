<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\EventoNacionalidad;
use App\Models\EventoTipoEntrada;
use App\Models\EventoSegmento;
use App\Models\EventoPrecio;
use App\Models\Moneda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventoPrecioController extends Controller
{
    // ==========================
    // MONEDAS (global)
    // ==========================
    public function monedasIndex(Request $request)
    {
        $soloActivos = $request->boolean('solo_activos', true);

        $items = Moneda::query()
            ->when($soloActivos, fn($q) => $q->where('activo', true))
            ->orderBy('orden')->orderBy('id')
            ->get();

        return response()->json(['items' => $items]);
    }

    // ==========================
    // NACIONALIDADES
    // ==========================
    public function nacionalidadesIndex(Evento $evento)
    {
        $items = EventoNacionalidad::where('evento_id', $evento->id)
            ->orderBy('orden')->orderBy('id')
            ->get();

        return response()->json(['items' => $items]);
    }

    public function nacionalidadesStore(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:80',
            'slug'   => 'required|string|max:120',
            'orden'  => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $data['evento_id'] = $evento->id;
        $data['orden']  = $data['orden'] ?? 0;
        $data['activo'] = array_key_exists('activo', $data) ? (bool)$data['activo'] : true;

        return EventoNacionalidad::create($data);
    }

    public function nacionalidadesUpdate(Request $request, EventoNacionalidad $nac)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:80',
            'slug'   => 'sometimes|required|string|max:120',
            'orden'  => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $nac->update($data);
        return $nac;
    }

    public function nacionalidadesDestroy(EventoNacionalidad $nac)
    {
        $nac->delete();
        return response()->json(['message' => 'OK']);
    }

    // ==========================
    // TIPOS ENTRADA
    // ==========================
    public function tiposIndex(Evento $evento)
    {
        $items = EventoTipoEntrada::where('evento_id', $evento->id)
            ->orderBy('orden')->orderBy('id')
            ->get();

        return response()->json(['items' => $items]);
    }

    public function tiposStore(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'slug'   => 'nullable|string|max:160',
            'orden'  => 'nullable|integer',
            'activo' => 'nullable|boolean',
            'imagen' => 'nullable|string|max:255',
        ]);

        $data['evento_id'] = $evento->id;
        $data['orden']  = $data['orden'] ?? 0;
        $data['activo'] = array_key_exists('activo', $data) ? (bool)$data['activo'] : true;

        return EventoTipoEntrada::create($data);
    }

    public function tiposUpdate(Request $request, EventoTipoEntrada $tipo)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:120',
            'slug'   => 'sometimes|required|string|max:160',
            'orden'  => 'nullable|integer',
            'activo' => 'nullable|boolean',
            'imagen' => 'nullable|string|max:255',
        ]);

        $tipo->update($data);
        return $tipo;
    }

    public function tiposDestroy(EventoTipoEntrada $tipo)
    {
        $tipo->delete();
        return response()->json(['message' => 'OK']);
    }

    // ==========================
    // SEGMENTOS (General/Adulto/Estudiante/Niño)
    // ==========================
    public function segmentosIndex(Evento $evento)
    {
        $items = EventoSegmento::where('evento_id', $evento->id)
            ->orderBy('orden')->orderBy('id')
            ->get();

        return response()->json(['items' => $items]);
    }

    public function segmentosStore(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:80',
            'slug'   => 'required|string|max:120',
            'orden'  => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $data['evento_id'] = $evento->id;
        $data['orden']  = $data['orden'] ?? 0;
        $data['activo'] = array_key_exists('activo', $data) ? (bool)$data['activo'] : true;

        return EventoSegmento::create($data);
    }

    public function segmentosUpdate(Request $request, EventoSegmento $seg)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:80',
            'slug'   => 'sometimes|required|string|max:120',
            'orden'  => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $seg->update($data);
        return $seg;
    }

    public function segmentosDestroy(EventoSegmento $seg)
    {
        $seg->delete();
        return response()->json(['message' => 'OK']);
    }

    // ==========================
    // PRECIOS (Nac x Tipo x Segmento x Moneda)
    // ==========================
    public function preciosIndex(Evento $evento)
    {
        $items = EventoPrecio::where('evento_id', $evento->id)->get();
        return response()->json(['items' => $items]);
    }

    /**
     * POST /api/eventos/{evento}/precios/upsert
     * Body:
     * { "rows": [
     *   { nacionalidad_id, tipo_entrada_id, segmento_id, moneda_id, compra, venta, activo }
     * ]}
     */
    public function preciosUpsert(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'rows' => 'required|array|min:1',

            'rows.*.nacionalidad_id' => 'required|integer',
            'rows.*.tipo_entrada_id' => 'required|integer',
            'rows.*.segmento_id'     => 'required|integer',
            'rows.*.moneda_id'       => 'required|integer',

            'rows.*.compra' => 'nullable|numeric|min:0',
            'rows.*.venta'  => 'nullable|numeric|min:0',

            'rows.*.activo' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($evento, $data) {

            $upserted = 0;

            foreach ($data['rows'] as $r) {
                EventoPrecio::updateOrCreate(
                    [
                        'evento_id'        => $evento->id,
                        'nacionalidad_id'  => (int)$r['nacionalidad_id'],
                        'tipo_entrada_id'  => (int)$r['tipo_entrada_id'],
                        'segmento_id'      => (int)$r['segmento_id'],
                        'moneda_id'        => (int)$r['moneda_id'],
                    ],
                    [
                        'compra' => (float)($r['compra'] ?? 0),
                        'venta'  => (float)($r['venta']  ?? 0),
                        'activo' => array_key_exists('activo', $r) ? (bool)$r['activo'] : true,
                    ]
                );
                $upserted++;
            }

            return response()->json(['message' => 'OK', 'upserted' => $upserted]);
        });
    }
}
