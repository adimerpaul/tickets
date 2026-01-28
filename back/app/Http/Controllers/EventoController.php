<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\EventoHorario;
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

    public function index(Request $request)
    {

        $q = Evento::query()
            ->with(['horarios', 'tickets'])
            ->orderBy('orden')
            ->orderBy('id', 'desc');

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

        return $q->get();
    }

    // VER POR ID
    public function show(Evento $evento)
    {
        return $evento->load(['horarios', 'tickets']);
    }

    // VER POR SLUG (PARA /evento/:site)
    public function showBySlug($slug)
    {
        $evento = Evento::where('slug', $slug)->with(['horarios', 'tickets'])->first();
        if (!$evento) return response()->json(['message' => 'Evento no encontrado'], 404);
        return $evento;
    }

    // CREAR
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

    // ACTUALIZAR
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
        return $evento->load(['horarios', 'tickets']);
    }

    // ELIMINAR
    public function destroy(Evento $evento)
    {
        $evento->delete();
        return response()->json(['message' => 'Evento eliminado']);
    }

    // ======= HORARIOS =======
    public function horariosStore(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'fecha' => 'nullable|date',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'capacidad' => 'nullable|integer|min:0',
            'reservados' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
            'nota' => 'nullable|string|max:255',
        ]);

        $data['evento_id'] = $evento->id;
        return EventoHorario::create($data);
    }

    public function horariosUpdate(Request $request, EventoHorario $horario)
    {
        $data = $request->validate([
            'fecha' => 'nullable|date',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'capacidad' => 'nullable|integer|min:0',
            'reservados' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
            'nota' => 'nullable|string|max:255',
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
