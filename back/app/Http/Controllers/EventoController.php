<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\EventoHorario;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function menu(Request $request)
    {
        // Si quieres SOLO activos en el menú, pon true.
        // Si quieres todos (activos/inactivos), pon false o manda ?solo_activos=0
        $soloActivos = $request->boolean('solo_activos', true);

        $eventos = Evento::query()
            ->select(['id','nombre','slug','pais','categoria','activo','orden'])
            ->when($soloActivos, fn($q) => $q->where('activo', true))
            ->orderBy('pais')      // primero por país
            ->orderBy('orden')     // luego por orden
            ->orderBy('id')        // luego por id
            ->get();

        $iconByCategoria = function (?string $cat): array {
            $c = strtolower($cat ?? '');
            if (str_contains($c, 'museum')) return ['icon' => 'museum', 'color' => 'cyan-4'];
            if (str_contains($c, 'temple')) return ['icon' => 'account_balance', 'color' => 'orange-4'];
            if (str_contains($c, 'site'))   return ['icon' => 'landscape', 'color' => 'amber-4'];
            if (str_contains($c, 'city'))   return ['icon' => 'location_city', 'color' => 'light-blue-4'];
            return ['icon' => 'event', 'color' => 'grey-4'];
        };

        // Agrupar por país
        $grouped = $eventos->groupBy(function ($ev) {
            return trim($ev->pais ?: 'Sin país');
        })->map(function ($items, $pais) use ($iconByCategoria) {
            return [
                'pais' => $pais,
                'eventos' => $items->map(function ($ev) use ($iconByCategoria) {
                    $meta = $iconByCategoria($ev->categoria);
                    return [
                        'id' => $ev->id,
                        'nombre' => $ev->nombre,
                        'slug' => $ev->slug,
                        'categoria' => $ev->categoria,
                        'activo' => (bool) $ev->activo,
                        'orden' => (int) ($ev->orden ?? 0),
                        'icon' => $meta['icon'],
//                        'color' => $meta['color'],
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'solo_activos' => $soloActivos,
            'items' => $grouped
        ]);
    }
    // =======================
    // EVENTOS (PAGINADO)
    // =======================
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 50);
        $perPage = $perPage > 0 ? min($perPage, 200) : 50;

        $q = Evento::query()
            ->orderBy('orden')
            ->orderByDesc('id');

        if ($request->has('activo')) {
            $q->where('activo', $request->boolean('activo'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $q->where(function ($qq) use ($s) {
                $qq->where('nombre', 'like', "%$s%")
                    ->orWhere('slug', 'like', "%$s%")
                    ->orWhere('ciudad', 'like', "%$s%");
            });
        }

        return $q->paginate($perPage);
    }

    public function show(Evento $evento)
    {
        return $evento;
    }

    public function showBySlug($slug)
    {
        $evento = Evento::where('slug', $slug)->first();
        if (!$evento) return response()->json(['message' => 'Evento no encontrado'], 404);
        return $evento;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:180',
            'slug' => 'required|string|max:200|unique:eventos,slug',
            'descripcion' => 'nullable|string',
            'pais' => 'nullable|string|max:120',
            'ciudad' => 'nullable|string|max:120',
            'ubicacion' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'imagen' => 'nullable|string|max:255',
            'categoria' => 'nullable|string|max:80',
            'orden' => 'nullable|integer',
            'activo' => 'nullable|boolean',
            'regla_nacionalidad' => 'nullable|in:ALL,EGYPTIAN_ONLY,FOREIGNERS_ONLY',
            'moneda' => 'nullable|string|max:10',
        ]);

        return Evento::create($data);
    }

    public function update(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:180',
            'slug' => 'sometimes|required|string|max:200|unique:eventos,slug,' . $evento->id,
            'descripcion' => 'nullable|string',
            'pais' => 'nullable|string|max:120',
            'ciudad' => 'nullable|string|max:120',
            'ubicacion' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'imagen' => 'nullable|string|max:255',
            'categoria' => 'nullable|string|max:80',
            'orden' => 'nullable|integer',
            'activo' => 'nullable|boolean',
            'regla_nacionalidad' => 'nullable|in:ALL,EGYPTIAN_ONLY,FOREIGNERS_ONLY',
            'moneda' => 'nullable|string|max:10',
        ]);

        $evento->update($data);
        return $evento;
    }

    public function destroy(Evento $evento)
    {
        $evento->delete();
        return response()->json(['message' => 'Evento eliminado']);
    }

    // =======================
    // HORARIOS (PAGINADO)
    // =======================
    public function horariosIndex(Request $request, Evento $evento)
    {
        $perPage = (int) $request->input('perPage', 50);
        $perPage = $perPage > 0 ? min($perPage, 200) : 50;

        $q = EventoHorario::query()
            ->where('evento_id', $evento->id)
            ->orderByDesc('starts_at')
            ->orderByDesc('id');

        if ($request->has('activo')) {
            $q->where('activo', $request->boolean('activo'));
        }

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $to   = Carbon::parse($request->input('to'))->endOfDay();
            $q->whereBetween('starts_at', [$from, $to]);
        }

        return $q->paginate($perPage);
    }

    // =======================
    // HORARIOS (CREAR EN LOTE)
    // =======================
    public function horariosStoreLote(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',

            'hora_inicio'  => 'required|date_format:H:i',
            'hora_fin'     => 'required|date_format:H:i',

            'intervalo_min' => 'required|integer|min:5|max:240',

            'capacidad' => 'required|integer|min:0',
            'activo'    => 'nullable|boolean',
            'nota'      => 'nullable|string|max:255',

            // plan (opcional, recomendado que sea enum en DB)
            'plan'      => 'nullable|string|max:40',
//            precio
            'precio'   => 'nullable|numeric|min:0',
        ]);

        $fechaInicio = Carbon::parse($data['fecha_inicio'])->startOfDay();
        $fechaFin    = Carbon::parse($data['fecha_fin'])->startOfDay();

        $intervalo = (int) $data['intervalo_min'];

        $activo = array_key_exists('activo', $data) ? (bool)$data['activo'] : true;

        $created = 0;

        for ($day = $fechaInicio->copy(); $day->lte($fechaFin); $day->addDay()) {
            $start = Carbon::parse($day->format('Y-m-d') . ' ' . $data['hora_inicio']);
            $end   = Carbon::parse($day->format('Y-m-d') . ' ' . $data['hora_fin']);

            if ($end->lte($start)) {
                // si el fin es menor/igual al inicio, saltamos ese día
                continue;
            }

            while ($start->lt($end)) {
                $slotEnd = $start->copy()->addMinutes($intervalo);
                if ($slotEnd->gt($end)) break;

                EventoHorario::create([
                    'evento_id' => $evento->id,
                    'fecha' => $day->format('Y-m-d'),
                    'hora_inicio' => $start->format('H:i'),
                    'hora_fin' => $slotEnd->format('H:i'),
                    'starts_at' => $start->format('Y-m-d H:i:s'),
                    'ends_at' => $slotEnd->format('Y-m-d H:i:s'),
                    'capacidad' => (int) $data['capacidad'],
                    'reservados' => 0,
                    'activo' => $activo,
                    'nota' => $data['nota'] ?? null,
                    'plan' => $data['plan'] ?? null,
                    'precio' => $data['precio'] ?? 0,
                ]);

                $created++;
                $start = $slotEnd;
            }
        }

        return response()->json([
            'message' => 'Horarios creados',
            'created' => $created
        ]);
    }

    public function horariosUpdate(Request $request, EventoHorario $horario)
    {
        $data = $request->validate([
            'fecha' => 'nullable|date',
//            'hora_inicio' => 'nullable|date_format:H:i',
//            'hora_fin' => 'nullable|date_format:H:i',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'capacidad' => 'nullable|integer|min:0',
            'reservados' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
            'nota' => 'nullable|string|max:255',
            'plan' => 'nullable|string|max:40',
            'precio' => 'nullable|numeric|min:0',
        ]);

        $horario->update($data);
        return $horario;
    }

    public function horariosDestroy(EventoHorario $horario)
    {
        $horario->delete();
        return response()->json(['message' => 'Horario eliminado']);
    }
}
