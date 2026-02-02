<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\EventoNacionalidad;
use App\Models\EventoTipoEntrada;
use App\Models\EventoPrecio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventoPrecioController extends Controller
{
    // ======================================================
    // NACIONALIDADES
    // ======================================================

    public function nacionalidadesIndex(Evento $evento)
    {
        $items = EventoNacionalidad::where('evento_id', $evento->id)
            ->orderBy('orden')
            ->orderBy('id')
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

    // ======================================================
    // TIPOS DE ENTRADA
    // ======================================================

    public function tiposIndex(Evento $evento)
    {
        $items = EventoTipoEntrada::where('evento_id', $evento->id)
            ->orderBy('orden')
            ->orderBy('id')
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

    // ======================================================
    // PRECIOS (MATRIZ)
    // ======================================================

    /**
     * GET /api/eventos/{evento}/precios
     */
    public function preciosIndex(Evento $evento)
    {
        $items = EventoPrecio::where('evento_id', $evento->id)->get();

        return response()->json([
            'items' => $items
        ]);
    }

    /**
     * POST /api/eventos/{evento}/precios/upsert
     *
     * Body:
     * {
     *   "rows": [
     *     {
     *       "nacionalidad_id": 1,
     *       "tipo_entrada_id": 2,
     *       "egp_compra": 0,
     *       "egp_venta": 0,
     *       "eur_compra": 0,
     *       "eur_venta": 0,
     *       "usd_compra": 0,
     *       "usd_venta": 0,
     *       "usdt_compra": 0,
     *       "usdt_venta": 0,
     *       "activo": true
     *     }
     *   ]
     * }
     */
    public function preciosUpsert(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'rows' => 'required|array|min:1',

            'rows.*.nacionalidad_id' => 'required|integer',
            'rows.*.tipo_entrada_id' => 'required|integer',

            'rows.*.egp_compra'  => 'nullable|numeric|min:0',
            'rows.*.egp_venta'   => 'nullable|numeric|min:0',
            'rows.*.eur_compra'  => 'nullable|numeric|min:0',
            'rows.*.eur_venta'   => 'nullable|numeric|min:0',
            'rows.*.usd_compra'  => 'nullable|numeric|min:0',
            'rows.*.usd_venta'   => 'nullable|numeric|min:0',
            'rows.*.usdt_compra' => 'nullable|numeric|min:0',
            'rows.*.usdt_venta'  => 'nullable|numeric|min:0',

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
                    ],
                    [
                        'egp_compra'  => (float)($r['egp_compra']  ?? 0),
                        'egp_venta'   => (float)($r['egp_venta']   ?? 0),
                        'eur_compra'  => (float)($r['eur_compra']  ?? 0),
                        'eur_venta'   => (float)($r['eur_venta']   ?? 0),
                        'usd_compra'  => (float)($r['usd_compra']  ?? 0),
                        'usd_venta'   => (float)($r['usd_venta']   ?? 0),
                        'usdt_compra' => (float)($r['usdt_compra'] ?? 0),
                        'usdt_venta'  => (float)($r['usdt_venta']  ?? 0),
                        'activo'      => array_key_exists('activo', $r) ? (bool)$r['activo'] : true,
                    ]
                );

                $upserted++;
            }

            return response()->json([
                'message'  => 'OK',
                'upserted' => $upserted
            ]);
        });
    }
}
