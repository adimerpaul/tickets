<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\EventoHorario;
use App\Models\EventoSemanaTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventoController extends Controller
{
    public function menu(Request $request)
    {
        $soloActivos = $request->boolean('solo_activos', true);

        $eventos = Evento::query()
            ->select(['id','nombre','slug','pais','categoria','activo','orden'])
            ->when($soloActivos, fn($q) => $q->where('activo', true))
            ->orderBy('pais')
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $iconByCategoria = function (?string $cat): array {
            $c = strtolower($cat ?? '');
            if (str_contains($c, 'museum')) return ['icon' => 'museum'];
            if (str_contains($c, 'temple')) return ['icon' => 'account_balance'];
            if (str_contains($c, 'site'))   return ['icon' => 'landscape'];
            if (str_contains($c, 'city'))   return ['icon' => 'location_city'];
            return ['icon' => 'event'];
        };

        $grouped = $eventos->groupBy(fn($ev) => trim($ev->pais ?: 'Sin país'))
            ->map(function ($items, $pais) use ($iconByCategoria) {
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

    public function show(Evento $evento) { return $evento; }

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

            // config horarios
            'slot_interval_min' => 'nullable|integer|min:5|max:240',
            'semana_hora_inicio' => 'nullable|date_format:H:i', // viene "09:00"
            'semana_hora_fin' => 'nullable|date_format:H:i',    // viene "17:00"
            'generar_semanas' => 'nullable|integer|min:1|max:520',
        ]);

        // ✅ Defaults si no viene nada
        $data['slot_interval_min']  = $data['slot_interval_min']  ?? 30;
        $data['generar_semanas']    = $data['generar_semanas']    ?? 52;

        // ✅ Normalizar a TIME con segundos (H:i:s) para evitar error Carbon
        $data['semana_hora_inicio'] = $this->normTime($data['semana_hora_inicio'] ?? '09:00');
        $data['semana_hora_fin']    = $this->normTime($data['semana_hora_fin']    ?? '17:00');

        return DB::transaction(function () use ($data) {
            $evento = Evento::create($data);

            // ✅ Crear plantilla semanal por defecto
            $this->crearPlantillaDefault($evento);

            // ✅ Generar slots reales por fecha para N semanas
            $this->generarSlotsInterno($evento, Carbon::now(), (int)($evento->generar_semanas ?? 52));

            return $evento;
        });
    }
    private function normTime(?string $v): ?string
    {
        if ($v === null || $v === '') return null;

        // "HH:MM"
        if (preg_match('/^\d{2}:\d{2}$/', $v)) {
            return $v . ':00';
        }

        // "HH:MM:SS"
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $v)) {
            return $v;
        }

        // fallback
        return Carbon::parse($v)->format('H:i:s');
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

            // nuevo
            'slot_interval_min' => 'nullable|integer|min:5|max:240',
            'semana_hora_inicio' => 'nullable|date_format:H:i',
            'semana_hora_fin' => 'nullable|date_format:H:i',
            'generar_semanas' => 'nullable|integer|min:1|max:520',
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
    // ✅ PLANTILLA SEMANAL (GRILLA)
    // =======================
    public function semanaIndex(Request $request, Evento $evento)
    {
        $plan = $request->input('plan'); // "Adulto" / "Niño" / null

        $items = EventoSemanaTemplate::query()
            ->where('evento_id', $evento->id)
            ->when($plan !== null && $plan !== '', fn($q) => $q->where('plan', $plan))
            ->orderBy('dow')->orderBy('hora_inicio')
            ->get();

        return response()->json([
            'evento' => [
                'id' => $evento->id,
                'slot_interval_min' => $evento->slot_interval_min,
                'semana_hora_inicio' => $evento->semana_hora_inicio,
                'semana_hora_fin' => $evento->semana_hora_fin,
                'generar_semanas' => $evento->generar_semanas,
            ],
            'plan' => $plan,
            'items' => $items
        ]);
    }

    /**
     * Guarda en lote toda la grilla:
     * payload:
     *  - plan: "Adulto"
     *  - slot_interval_min, semana_hora_inicio, semana_hora_fin, generar_semanas (opcional)
     *  - cells: [{ dow:1, hora_inicio:"09:00", activo:true, capacidad:100, precio:20 }, ...]
     */
    public function semanaUpsert(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'plan' => 'nullable|string|max:40',

            'slot_interval_min' => 'nullable|integer|min:5|max:240',
            'semana_hora_inicio' => 'nullable|date_format:H:i',
            'semana_hora_fin' => 'nullable|date_format:H:i',
            'generar_semanas' => 'nullable|integer|min:1|max:520',

            'cells' => 'required|array|min:1',
            'cells.*.dow' => 'required|integer|min:1|max:7',
            'cells.*.hora_inicio' => 'required|date_format:H:i',
            'cells.*.activo' => 'required|boolean',
            'cells.*.capacidad' => 'nullable|integer|min:0',
            'cells.*.precio' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data, $evento) {

            // actualizar config si viene
            $updateEvento = [];
            foreach (['slot_interval_min','semana_hora_inicio','semana_hora_fin','generar_semanas'] as $k) {
                if (array_key_exists($k, $data)) $updateEvento[$k] = $data[$k];
            }
            if (!empty($updateEvento)) $evento->update($updateEvento);

            $plan = $data['plan'] ?? null;
            $planNorm = ($plan !== null && $plan !== '') ? $plan : null;

            $interval = (int) ($evento->slot_interval_min ?? 30);

            foreach ($data['cells'] as $c) {

                // ✅ normaliza a H:i:s para que coincida con la DB / unique key
                $horaInicio = Carbon::createFromFormat('H:i', $c['hora_inicio'])->format('H:i:s');
                $horaFin    = Carbon::createFromFormat('H:i:s', $horaInicio)->addMinutes($interval)->format('H:i:s');

                // ✅ updateOrCreate usando EXACTAMENTE la llave del UNIQUE
                EventoSemanaTemplate::updateOrCreate(
                    [
                        'evento_id'   => $evento->id,
                        'dow'         => (int) $c['dow'],
                        'hora_inicio' => $horaInicio,
                        'plan'        => $planNorm,
                    ],
                    [
                        'hora_fin'   => $horaFin,
                        'activo'     => (bool) $c['activo'],
                        'capacidad'  => isset($c['capacidad']) ? (int) $c['capacidad'] : 0,
                        'precio'     => isset($c['precio']) ? (float) $c['precio'] : 0,
                    ]
                );
            }

            // ✅ Regenerar slots futuros (sin romper reservados)
            $this->generarSlotsInterno($evento, Carbon::now(), (int) ($evento->generar_semanas ?? 52));

            return response()->json(['message' => 'Plantilla semanal guardada y slots regenerados']);
        });
    }


    // =======================
    // ✅ GENERAR SLOTS MANUAL
    // =======================
    public function generarSlots(Request $request, Evento $evento)
    {
        $weeks = (int) $request->input('weeks', $evento->generar_semanas ?? 52);
        $weeks = max(1, min(520, $weeks));

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))
            : Carbon::now();

        $created = $this->generarSlotsInterno($evento, $from, $weeks);

        return response()->json([
            'message' => 'Slots generados',
            'created' => $created,
        ]);
    }

    // =======================
    // SLOTS REALES (PAGINADO)
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

        if ($request->filled('plan')) {
            $q->where('plan', $request->input('plan'));
        }

        return $q->paginate($perPage);
    }

    public function horariosUpdate(Request $request, EventoHorario $horario)
    {
        $data = $request->validate([
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

    // =======================
    // INTERNOS
    // =======================
    private function crearPlantillaDefault(Evento $evento): void
    {
        $interval = (int)($evento->slot_interval_min ?? 30);
        $start = Carbon::createFromFormat('H:i:s', $evento->semana_hora_inicio ?? '09:00:00');
        $end = Carbon::createFromFormat('H:i:s', $evento->semana_hora_fin ?? '17:00:00');

        // si quieres solo Adulto, deja ['Adulto']
        $planes = ['Adulto', 'Niño'];

        for ($dow = 1; $dow <= 7; $dow++) {
            foreach ($planes as $plan) {
                $t = $start->copy();
                while ($t->lt($end)) {
                    $t2 = $t->copy()->addMinutes($interval);
                    if ($t2->gt($end)) break;

                    EventoSemanaTemplate::updateOrCreate(
                        [
                            'evento_id' => $evento->id,
                            'dow' => $dow,
                            'hora_inicio' => $t->format('H:i:s'),
                            'plan' => $plan,
                        ],
                        [
                            'hora_fin' => $t2->format('H:i:s'),
                            'precio' => 0,
                            'capacidad' => 100,
                            'activo' => true,
                        ]
                    );

                    $t = $t2;
                }
            }
        }
    }

    /**
     * Genera slots reales desde $from por $weeks semanas hacia adelante.
     * - Crea los faltantes
     * - Si una plantilla está inactiva, desactiva slots futuros SOLO si reservados=0
     */
    private function generarSlotsInterno(Evento $evento, Carbon $from, int $weeks): int
    {
        $created = 0;

        $from = $from->copy();
        $to = $from->copy()->addWeeks($weeks)->endOfDay();

        // plantillas activas/inactivas
        $templates = EventoSemanaTemplate::query()
            ->where('evento_id', $evento->id)
            ->get();

        if ($templates->isEmpty()) return 0;

        // índice por dow
        $byDow = $templates->groupBy('dow');

        // recorrer día por día
        for ($day = $from->copy()->startOfDay(); $day->lte($to); $day->addDay()) {
            $dow = (int)$day->dayOfWeekIso; // 1..7

            $tpls = $byDow->get($dow, collect());
            if ($tpls->isEmpty()) continue;

            foreach ($tpls as $tpl) {
                // construir starts/ends
                $starts = Carbon::parse($day->format('Y-m-d') . ' ' . $tpl->hora_inicio);
                $ends = Carbon::parse($day->format('Y-m-d') . ' ' . $tpl->hora_fin);

                $existing = EventoHorario::query()
                    ->where('evento_id', $evento->id)
                    ->where('starts_at', $starts->format('Y-m-d H:i:s'))
                    ->where('plan', $tpl->plan)
                    ->first();

                if (!$existing) {
                    EventoHorario::create([
                        'evento_id' => $evento->id,
                        'template_id' => $tpl->id,
                        'fecha' => $day->format('Y-m-d'),
                        'hora_inicio' => $tpl->hora_inicio,
                        'hora_fin' => $tpl->hora_fin,
                        'starts_at' => $starts->format('Y-m-d H:i:s'),
                        'ends_at' => $ends->format('Y-m-d H:i:s'),
                        'capacidad' => (int)$tpl->capacidad,
                        'reservados' => 0,
                        'activo' => (bool)$tpl->activo,
                        'nota' => null,
                        'plan' => $tpl->plan,
                        'precio' => (float)$tpl->precio,
                    ]);
                    $created++;
                } else {
                    // sincroniza propiedades si es futuro y no rompe reservados
                    if ($starts->gte(Carbon::now())) {
                        // si plantilla está OFF, solo apagamos slot si no hay reservados
                        if (!$tpl->activo && (int)$existing->reservados > 0) {
                            // no tocar
                        } else {
                            $existing->update([
                                'template_id' => $tpl->id,
                                'capacidad' => (int)$tpl->capacidad,
                                'precio' => (float)$tpl->precio,
                                'activo' => (bool)$tpl->activo,
                            ]);
                        }
                    }
                }
            }
        }

        return $created;
    }
}
