<?php


namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use function Laravel\Prompts\error;

class OrderController extends Controller
{
    function sendEmailWithEntradasPdf(Order $order)
    {
        // generar el pdf de entradas
        $items = $order->items ?? [];

        foreach ($items as $i => $it) {
            $it = is_array($it) ? (object)$it : $it;

            $payload = $it->name . '|' . $order->localizador;

            $svg = \QrCode::format('svg')->size(180)->generate($payload);

            $it->qr_src = 'data:image/svg+xml;base64,' . base64_encode($svg);

            $items[$i] = $it;
        }

        $order->items = $items;

        $pdf = \PDF::loadView('pdf.order_entries', ['order' => $order])
            ->setPaper('a4', 'portrait');

        // enviar email con el pdf adjunto
        \Mail::send('emails.order_entries', ['order' => $order], function ($message) use ($order, $pdf) {
            $message->to($order->email)
                ->subject('Tus entradas')
                ->attachData($pdf->output(), "order_{$order->id}_entries.pdf");
        });

        return response()->json(['message' => 'Email sent']);
    }
    function update(Request $request, Order $order)
    {
        $data = $request->only([
            'localizador',
        ]);

        $order->update($data);

        return response()->json($order);
    }
    function pdfEntradas(Order $order)
    {
        $items = $order->items ?? [];

        foreach ($items as $i => $it) {
            $it = is_array($it) ? (object)$it : $it;

            $payload = $it->name . '|' . $order->localizador;

            $svg = \QrCode::format('svg')->size(180)->generate($payload);

            $it->qr_src = 'data:image/svg+xml;base64,' . base64_encode($svg);

            $items[$i] = $it;
        }

        $order->items = $items;

        $pdf = \PDF::loadView('pdf.order_entries', ['order' => $order])
            ->setPaper('a4', 'portrait');

        return $pdf->stream("order_{$order->id}_entries.pdf");
    }

    public function index(Request $request)
    {
        $site = $request->input('site');

        $evento = Evento::where('slug', $site)->firstOrFail();
        if (!$evento) {
            return response()->json(['message' => 'Evento no encontrado'], 404);
        }
        // filtros
        $q = Order::query();

        $q->where('evento_id', $evento->id);

        // buscar por session_id, email, payment_intent_id
        if ($request->filled('search')) {
            $s = trim($request->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('session_id', 'like', "%$s%")
                    ->orWhere('payment_intent_id', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%");
            });
        }

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        // rango fechas (por created_at)
        if ($request->filled('from')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $q->where('created_at', '>=', $from);
        }
        if ($request->filled('to')) {
            $to = Carbon::parse($request->to)->endOfDay();
            $q->where('created_at', '<=', $to);
        }

        // orden
        $sortBy = $request->get('sortBy', 'id');
        $sortDir = $request->get('sortDir', 'desc');
        if (!in_array($sortBy, ['id', 'created_at', 'paid_at', 'amount_total', 'status'])) $sortBy = 'id';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        $q->orderBy($sortBy, $sortDir);

        // paginación
        $perPage = (int)$request->get('perPage', 15);
        $perPage = max(5, min(100, $perPage));

        $data = $q->paginate($perPage);

        return response()->json($data);
    }

    public function stats(Request $request)
    {
        // aplicar los mismos filtros que index (para que las cards reflejen lo filtrado)
        $q = Order::query();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('session_id', 'like', "%$s%")
                    ->orWhere('payment_intent_id', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%");
            });
        }

        if ($request->filled('from')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $q->where('created_at', '>=', $from);
        }
        if ($request->filled('to')) {
            $to = Carbon::parse($request->to)->endOfDay();
            $q->where('created_at', '<=', $to);
        }

        $rows = $q->selectRaw("status, COUNT(*) as c")->groupBy('status')->get();

        $map = [
            'PENDING' => 0,
            'PAID' => 0,
            'EXPIRED' => 0,
            'FAILED' => 0,
            'TOTAL' => 0,
        ];

        foreach ($rows as $r) {
            $st = strtoupper($r->status);
            if (!isset($map[$st])) $map[$st] = 0;
            $map[$st] += (int)$r->c;
            $map['TOTAL'] += (int)$r->c;
        }

        return response()->json($map);
    }

    public function show(Order $order)
    {
        return response()->json($order);
    }

    // ===== PDF opcional BACKEND (DomPDF) =====
    // composer require barryvdh/laravel-dompdf
    public function pdf(Order $order)
    {
        $pdf = \PDF::loadView('pdf.order', ['order' => $order])
            ->setPaper('a4', 'portrait');

        return $pdf->stream("order_{$order->id}.pdf");
    }

    public function pdfList(Request $request)
    {
        // reutiliza index pero sin paginate
        $q = Order::query();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('session_id', 'like', "%$s%")
                    ->orWhere('payment_intent_id', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%");
            });
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $q->where('created_at', '>=', $from);
        }
        if ($request->filled('to')) {
            $to = Carbon::parse($request->to)->endOfDay();
            $q->where('created_at', '<=', $to);
        }

        $orders = $q->orderBy('id', 'desc')->limit(500)->get(); // evita pdf gigante

        $pdf = \PDF::loadView('pdf.orders_list', [
            'orders' => $orders,
            'filters' => $request->all()
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("orders_list.pdf");
    }
}
