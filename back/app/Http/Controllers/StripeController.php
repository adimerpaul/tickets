<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\EventoHorario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_amount' => 'required|integer|min:1', // centavos
            'customer_email' => 'nullable|email',
            'metadata' => 'nullable|array',
        ]);

        $meta = $validated['metadata'] ?? [];

        // ====== VALIDACIÓN FUERTE de metadata para guardar orden + cupos
        $request->validate([
            'metadata.evento_id' => 'required',
            'metadata.starts_at' => 'required',
            // ids horarios (puede existir solo uno si vendes solo adulto o solo niño)
            'metadata.horario_adulto_id' => 'nullable|integer',
            'metadata.horario_nino_id' => 'nullable|integer',
            'metadata.adults' => 'nullable|integer|min:0',
            'metadata.kids' => 'nullable|integer|min:0',
        ]);

        $adultQty = (int)($meta['adults'] ?? 0);
        $kidQty   = (int)($meta['kids'] ?? 0);

        if ($adultQty + $kidQty <= 0) {
            return response()->json(['message' => 'Cantidad inválida'], 422);
        }

        // ====== CHECK CUPO ANTES DE CREAR SESIÓN
        // Nota: Esto NO "reserva"; solo valida que haya cupos al momento de iniciar checkout.
        // El ajuste definitivo lo hacemos al PAID (webhook).
        $horarioAdultoId = isset($meta['horario_adulto_id']) ? (int)$meta['horario_adulto_id'] : null;
        $horarioNinoId   = isset($meta['horario_nino_id']) ? (int)$meta['horario_nino_id'] : null;

        if ($adultQty > 0 && !$horarioAdultoId) {
            return response()->json(['message' => 'Falta horario_adulto_id'], 422);
        }
        if ($kidQty > 0 && !$horarioNinoId) {
            return response()->json(['message' => 'Falta horario_nino_id'], 422);
        }

        // validación de cupos (sin bloquear)
        if ($adultQty > 0) {
            $hA = EventoHorario::find($horarioAdultoId);
            if (!$hA || !$hA->activo) return response()->json(['message' => 'Horario Adulto inválido'], 422);
            $disp = max(0, (int)$hA->capacidad - (int)$hA->reservados);
            if ($adultQty > $disp) return response()->json(['message' => 'No hay cupos suficientes (Adulto)'], 422);
        }

        if ($kidQty > 0) {
            $hN = EventoHorario::find($horarioNinoId);
            if (!$hN || !$hN->activo) return response()->json(['message' => 'Horario Niño inválido'], 422);
            $disp = max(0, (int)$hN->capacidad - (int)$hN->reservados);
            if ($kidQty > $disp) return response()->json(['message' => 'No hay cupos suficientes (Niño)'], 422);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        // 1) Line items para Stripe (centavos, int)
        $lineItems = array_map(function ($it) {
            return [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => $it['name']],
                    'unit_amount' => (int) $it['unit_amount'],
                ],
                'quantity' => (int) $it['qty'],
            ];
        }, $validated['items']);

        // 2) Crear sesión de Stripe
        $session = \Stripe\Checkout\Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'success_url' => rtrim(env('FRONTEND_URL'), '/') . '/pedido?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => rtrim(env('FRONTEND_URL'), '/') . '/pago-cancelado',
            'customer_email' => $validated['customer_email'] ?? null,
            'metadata' => $meta,
        ]);

        // 3) Preparar items para guardar en BD (EUR con 2 decimales)
        $itemsForDb = array_map(function ($it) {
            $it['unit_amount'] = round(((float) $it['unit_amount']) / 100, 2);
            $it['qty'] = (int) $it['qty'];
            return $it;
        }, $validated['items']);

        // 4) Total
        $amountTotal = 0.00;
        foreach ($itemsForDb as $it) {
            $amountTotal = round($amountTotal + ((float)$it['unit_amount'] * (int)$it['qty']), 2);
        }

        // 5) Guardar PENDING
        Order::create([
            'codigo_pedido' => $this->generateOrderCode(),
            'session_id' => $session->id,
            'email' => $validated['customer_email'] ?? null,
            'amount_total' => $amountTotal,
            'currency' => 'eur',
            'status' => 'PENDING',
            'metadata' => $meta,
            'items' => $itemsForDb,

            // 👇👇👇 FIX: columnas reales
            'evento_id' => (int)($meta['evento_id'] ?? 0),
            'starts_at' => $meta['starts_at'] ?? null,
            'horario_adulto_id' => isset($meta['horario_adulto_id']) ? (int)$meta['horario_adulto_id'] : null,
            'horario_nino_id' => isset($meta['horario_nino_id']) ? (int)$meta['horario_nino_id'] : null,
            'adults' => (int)($meta['adults'] ?? 0),
            'kids' => (int)($meta['kids'] ?? 0),

            // extra
            'dni' => $meta['dni'] ?? null,
            'nombre_completo' => $meta['nombre_completo'] ?? null,
            'nacionalidad' => $meta['nacionalidad'] ?? null,
            'entrada_tipo' => $meta['entrada_tipo'] ?? null,
        ]);

        return response()->json([
            'checkout_url' => $session->url,
            'session_id' => $session->id,
        ]);
    }

    function generateOrderCode()
    {
        $lastOrder = Order::orderBy('id', 'desc')->first();
        if (!$lastOrder) return 'GEM000001';
        $lastCode = $lastOrder->codigo_pedido;
        $number = (int)substr($lastCode, 3) + 1;
        return 'GEM' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function webhook(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Throwable $e) {
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $order = Order::where('session_id', $session->id)->first();
            if (!$order) {
                Log::warning('Order not found for session', ['session_id' => $session->id]);
                return response('ok', 200);
            }
            if ($order->status === 'PAID') return response('ok', 200);

            // email fallback
            $email = $order->email
                ?? ($session->customer_details->email ?? null)
                ?? ($session->customer_email ?? null);

            // ====== MARCAR PAID + SUMAR RESERVADOS (ATÓMICO)
            DB::transaction(function () use ($order, $session, $email) {
                // 1) marcar orden
                $order->email = $email;
                $order->status = 'PAID';
                $order->paid_at = now();
                $order->payment_intent_id = $session->payment_intent ?? null;
                $order->save();

                // 2) sumar reservados según metadata
                $meta = $order->metadata ?: [];
                $adultQty = (int)($meta['adults'] ?? 0);
                $kidQty   = (int)($meta['kids'] ?? 0);

                $horarioAdultoId = isset($meta['horario_adulto_id']) ? (int)$meta['horario_adulto_id'] : null;
                $horarioNinoId   = isset($meta['horario_nino_id']) ? (int)$meta['horario_nino_id'] : null;

                if ($adultQty > 0 && $horarioAdultoId) {
                    $hA = EventoHorario::lockForUpdate()->find($horarioAdultoId);
                    if ($hA) {
                        $disp = max(0, (int)$hA->capacidad - (int)$hA->reservados);
                        if ($adultQty <= $disp) {
                            $hA->reservados = (int)$hA->reservados + $adultQty;
                            $hA->save();
                        } else {
                            Log::warning('Overbooking Adulto prevented', [
                                'horario_id' => $horarioAdultoId,
                                'req' => $adultQty,
                                'disp' => $disp,
                                'order_id' => $order->id
                            ]);
                        }
                    }
                }

                if ($kidQty > 0 && $horarioNinoId) {
                    $hN = EventoHorario::lockForUpdate()->find($horarioNinoId);
                    if ($hN) {
                        $disp = max(0, (int)$hN->capacidad - (int)$hN->reservados);
                        if ($kidQty <= $disp) {
                            $hN->reservados = (int)$hN->reservados + $kidQty;
                            $hN->save();
                        } else {
                            Log::warning('Overbooking Niño prevented', [
                                'horario_id' => $horarioNinoId,
                                'req' => $kidQty,
                                'disp' => $disp,
                                'order_id' => $order->id
                            ]);
                        }
                    }
                }
            });
        }

        if ($event->type === 'checkout.session.expired') {
            $session = $event->data->object;
            $order = Order::where('session_id', $session->id)->first();
            if ($order && $order->status === 'PENDING') {
                $order->status = 'EXPIRED';
                $order->save();
            }
        }

        if ($event->type === 'payment_intent.payment_failed') {
            $pi = $event->data->object;
            $order = Order::where('payment_intent_id', $pi->id)->first();
            if ($order && $order->status === 'PENDING') {
                $order->status = 'FAILED';
                $order->save();
            }
        }

        return response('ok', 200);
    }
}
