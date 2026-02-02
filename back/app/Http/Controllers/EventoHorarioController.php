<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\EventoHorario;
use App\Models\EventoSemanaTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventoHorarioController extends Controller
{
    /**
     * GET /api/eventos/{evento}/horarios/month?start=YYYY-MM-DD&end=YYYY-MM-DD&plan=Adulto
     * Devuelve resumen por día para pintar el calendario (conteo de slots activos)
     */
    public function month(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'start' => 'required|date_format:Y-m-d',
            'end'   => 'required|date_format:Y-m-d',
            'plan'  => 'nullable|string|max:60',
        ]);

        $start = Carbon::createFromFormat('Y-m-d', $data['start'])->startOfDay();
        $end   = Carbon::createFromFormat('Y-m-d', $data['end'])->endOfDay();
        $plan  = $data['plan'] ?? null;

        $q = EventoHorario::query()
            ->where('evento_id', $evento->id)
            ->whereNotNull('starts_at')
            ->whereBetween('starts_at', [$start, $end]);

        if ($plan !== null && $plan !== '') {
            $q->where('plan', $plan);
        }

        // resumen por fecha (día)
        $rows = $q->selectRaw('DATE(starts_at) as fecha, SUM(CASE WHEN activo=1 THEN 1 ELSE 0 END) as activos, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(starts_at)'))
            ->orderBy('fecha')
            ->get();

        // formato para FullCalendar (allDay events como badges)
        $items = $rows->map(function ($r) {
            $activos = (int)$r->activos;
            $total   = (int)$r->total;

            return [
                'title' => $activos > 0 ? "{$activos} disp." : "0 disp.",
                'start' => $r->fecha,
                'allDay' => true,
                'extendedProps' => [
                    'activos' => $activos,
                    'total' => $total,
                ],
            ];
        });

        return response()->json(['items' => $items]);
    }

    /**
     * GET /api/eventos/{evento}/horarios/day?date=YYYY-MM-DD&plan=Adulto
     * Lista todos los slots del día (para panel lateral)
     */
    public function day(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'plan' => 'nullable|string|max:60',
        ]);

        $date = Carbon::createFromFormat('Y-m-d', $data['date'])->startOfDay();
        $plan = $data['plan'] ?? null;

        $q = EventoHorario::query()
            ->where('evento_id', $evento->id)
            ->whereDate('starts_at', $date);

        if ($plan !== null && $plan !== '') {
            $q->where('plan', $plan);
        }

        $items = $q->orderBy('starts_at')->get()->map(function (EventoHorario $h) {
            return [
                'id' => $h->id,
                'starts_at' => optional($h->starts_at)->format('Y-m-d H:i:s'),
                'ends_at'   => optional($h->ends_at)->format('Y-m-d H:i:s'),
                'hora_inicio' => $h->hora_inicio,
                'hora_fin' => $h->hora_fin,
                'plan' => $h->plan,
                'precio' => (float)$h->precio,
                'capacidad' => (int)$h->capacidad,
                'reservados' => (int)$h->reservados,
                'activo' => (bool)$h->activo,
                'nota' => $h->nota,
            ];
        });

        return response()->json(['items' => $items]);
    }

    /**
     * POST /api/eventos/{evento}/horarios/generate
     * Body:
     * {
     *   "date_from":"2026-02-01",
     *   "date_to":"2026-02-28",
     *   "plan":"Adulto",
     *   "mode":"keep"|"replace"
     * }
     */
    public function generate(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'date_from' => 'required|date_format:Y-m-d',
            'date_to'   => 'required|date_format:Y-m-d',
            'plan'      => 'nullable|string|max:60',
            'mode'      => 'nullable|in:keep,replace',
        ]);

        $from = Carbon::createFromFormat('Y-m-d', $data['date_from'])->startOfDay();
        $to   = Carbon::createFromFormat('Y-m-d', $data['date_to'])->endOfDay();
        $plan = $data['plan'] ?? null;
        $mode = $data['mode'] ?? 'keep';

        if ($to->lt($from)) {
            return response()->json(['message' => 'date_to debe ser mayor o igual a date_from'], 422);
        }

        // Si replace: borramos (hard delete) para evitar choque con unique + softDeletes
        if ($mode === 'replace') {
            $del = EventoHorario::withTrashed()
                ->where('evento_id', $evento->id)
                ->whereNotNull('starts_at')
                ->whereBetween('starts_at', [$from, $to]);

            if ($plan !== null && $plan !== '') {
                $del->where('plan', $plan);
            }

            $del->forceDelete();
        }

        $created = 0;
        $updated = 0;

        DB::beginTransaction();
        try {
            $cursor = $from->copy()->startOfDay();
            while ($cursor->lte($to)) {
                $dow = (int)$cursor->isoWeekday(); // 1..7
                $daySlots = $this->slotsForDay($evento, $cursor, $dow, $plan);

                foreach ($daySlots as $slot) {
                    // starts_at unique por evento + starts_at + plan
                    $row = EventoHorario::updateOrCreate(
                        [
                            'evento_id' => $evento->id,
                            'starts_at' => $slot['starts_at'],
                            'plan' => $slot['plan'],
                        ],
                        $slot
                    );

                    if ($row->wasRecentlyCreated) $created++;
                    else $updated++;
                }

                $cursor->addDay();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error generando slots', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'OK',
            'created' => $created,
            'updated' => $updated,
            'mode' => $mode,
        ]);
    }

    /**
     * PUT /api/evento-horarios/{horario}
     */
    public function update(Request $request, EventoHorario $horario)
    {
        $data = $request->validate([
            'activo'    => 'nullable|boolean',
            'precio'    => 'nullable|numeric|min:0',
            'capacidad' => 'nullable|integer|min:0',
            'nota'      => 'nullable|string|max:255',

            // opcional: permitir mover horario (si quieres)
            'starts_at' => 'nullable|date_format:Y-m-d H:i:s',
            'ends_at'   => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        // si se mueve, recalcular fecha/hora_inicio/fin también
        if (!empty($data['starts_at'])) {
            $s = Carbon::createFromFormat('Y-m-d H:i:s', $data['starts_at']);
            $horario->starts_at = $s;
            $horario->fecha = $s->format('Y-m-d');
            $horario->hora_inicio = $s->format('H:i:s');
        }

        if (!empty($data['ends_at'])) {
            $e = Carbon::createFromFormat('Y-m-d H:i:s', $data['ends_at']);
            $horario->ends_at = $e;
            $horario->hora_fin = $e->format('H:i:s');
        }

        if (array_key_exists('activo', $data)) $horario->activo = (bool)$data['activo'];
        if (array_key_exists('precio', $data)) $horario->precio = (float)$data['precio'];
        if (array_key_exists('capacidad', $data)) $horario->capacidad = (int)$data['capacidad'];
        if (array_key_exists('nota', $data)) $horario->nota = $data['nota'];

        $horario->save();

        return response()->json($horario);
    }

    /**
     * DELETE /api/evento-horarios/{horario}
     */
    public function destroy(EventoHorario $horario)
    {
        // aquí puedes decidir soft delete o force delete
        $horario->delete();
        return response()->json(['message' => 'Eliminado']);
    }

    /**
     * Genera slots para un día:
     * - Si hay templates activos para ese DOW y plan → usa templates
     * - Si NO hay templates → usa configuración del evento (intervalo + inicio/fin)
     */
    private function slotsForDay(Evento $evento, Carbon $date, int $dow, ?string $plan): array
    {
        $templates = EventoSemanaTemplate::query()
            ->where('evento_id', $evento->id)
            ->where('dow', $dow)
            ->where('activo', true)
            ->when($plan !== null && $plan !== '', fn($q) => $q->where('plan', $plan))
            ->orderBy('hora_inicio')
            ->get();

        $slots = [];

        // CASE 1: con templates
        if ($templates->count() > 0) {
            foreach ($templates as $t) {
                $start = Carbon::parse($date->format('Y-m-d') . ' ' . substr((string)$t->hora_inicio, 0, 8));
                $end   = Carbon::parse($date->format('Y-m-d') . ' ' . substr((string)$t->hora_fin, 0, 8));

                // seguridad
                if ($end->lte($start)) continue;

                $slots[] = [
                    'evento_id' => $evento->id,
                    'template_id' => $t->id,
                    'fecha' => $date->format('Y-m-d'),
                    'hora_inicio' => $start->format('H:i:s'),
                    'hora_fin' => $end->format('H:i:s'),
                    'starts_at' => $start->format('Y-m-d H:i:s'),
                    'ends_at' => $end->format('Y-m-d H:i:s'),
                    'plan' => $t->plan,
                    'precio' => (float)$t->precio,
                    'capacidad' => (int)$t->capacidad,
                    'reservados' => 0,
                    'activo' => true,
                    'nota' => null,
                ];
            }
            return $slots;
        }

        // CASE 2: sin templates -> grilla por configuración del evento
        $step = (int)($evento->slot_interval_min ?: 30);
        $startStr = substr((string)$evento->semana_hora_inicio, 0, 8);
        $endStr   = substr((string)$evento->semana_hora_fin, 0, 8);

        $start = Carbon::parse($date->format('Y-m-d') . ' ' . $startStr);
        $end   = Carbon::parse($date->format('Y-m-d') . ' ' . $endStr);

        if ($end->lte($start)) return [];

        $cursor = $start->copy();
        for ($i = 0; $i < 2000; $i++) {
            $next = $cursor->copy()->addMinutes($step);
            if ($next->gt($end)) break;

            $slots[] = [
                'evento_id' => $evento->id,
                'template_id' => null,
                'fecha' => $date->format('Y-m-d'),
                'hora_inicio' => $cursor->format('H:i:s'),
                'hora_fin' => $next->format('H:i:s'),
                'starts_at' => $cursor->format('Y-m-d H:i:s'),
                'ends_at' => $next->format('Y-m-d H:i:s'),
                'plan' => $plan,        // el plan que estás gestionando
                'precio' => 0,
                'capacidad' => 100,
                'reservados' => 0,
                'activo' => true,
                'nota' => null,
            ];

            $cursor = $next;
            if ($cursor->gte($end)) break;
        }

        return $slots;
    }
}
