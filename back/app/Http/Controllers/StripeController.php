<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Webhook;
use App\Mail\TicketPaidMail;
use Illuminate\Support\Facades\Mail;
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

        Stripe::setApiKey(config('services.stripe.secret'));

        // 1) Line items para Stripe (centavos, int)
        $lineItems = array_map(function ($it) {
            return [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => $it['name']],
                    'unit_amount' => (int) $it['unit_amount'], // centavos
                ],
                'quantity' => (int) $it['qty'],
            ];
        }, $validated['items']);

        // 2) Crear sesión de Stripe
        $session = \Stripe\Checkout\Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
//            'success_url' => rtrim(env('FRONTEND_URL'), '/') . '/pago-exitoso?session_id={CHECKOUT_SESSION_ID}',
            'success_url' => rtrim(env('FRONTEND_URL'), '/') . '/pedido?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => rtrim(env('FRONTEND_URL'), '/') . '/pago-cancelado',
            'customer_email' => $validated['customer_email'] ?? null,
            'metadata' => $validated['metadata'] ?? [],
        ]);

        // 3) Preparar items para guardar en BD (EUR con 2 decimales)
        $itemsForDb = array_map(function ($it) {
            $it['unit_amount'] = round(((float) $it['unit_amount']) / 100, 2); // de centavos a euros
            $it['qty'] = (int) $it['qty'];
            return $it;
        }, $validated['items']);

        // 4) Calcular total (EUR con 2 decimales)
        $amountTotal = 0.00;
        foreach ($itemsForDb as $it) {
            $amountTotal = round($amountTotal + ((float)$it['unit_amount'] * (int)$it['qty']), 2);
        }

        // 5) Guardar orden PENDING en BD
        Order::create([
            'codigo_pedido'=>$this->generateOrderCode(),
            'session_id' => $session->id,
            'email' => $validated['customer_email'] ?? null,
            'amount_total' => $amountTotal,     // ej: 12.50 / 37.00
            'currency' => 'eur',
            'status' => 'PENDING',
            'metadata' => $validated['metadata'] ?? null,
            'items' => $itemsForDb,
            'dni' => $validated['metadata']['dni'] ?? null,
            'nombre_completo' => $validated['metadata']['nombre_completo'] ?? null,
            'nacionalidad' => $validated['metadata']['nacionalidad'] ?? null,
            'entrada_tipo' => $validated['metadata']['entrada_tipo'] ?? null,
        ]);

        return response()->json([
            'checkout_url' => $session->url,
            'session_id' => $session->id,
        ]);
    }
    function generateOrderCode(){
//        GEM000001 CODIGO DE PEDIDO
        $lastOrder = Order::orderBy('id', 'desc')->first();
        if(!$lastOrder){
            return 'GEM000001';
        }
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
            error_log('Webhook checkout.session.completed received for session ID: ' . $session->id);

            $order = Order::where('session_id', $session->id)->first();

            if (!$order) {
                Log::warning('Order not found for session', ['session_id' => $session->id]);
                return response('ok', 200);
            }

            if ($order->status === 'PAID') {
                return response('ok', 200);
            }

            // email fallback
            $email = $order->email
                ?? ($session->customer_details->email ?? null)
                ?? ($session->customer_email ?? null);

            $order->email = $email;
            $order->status = 'PAID';
            $order->paid_at = now();
            $order->payment_intent_id = $session->payment_intent ?? null;
            $order->save();

//            if ($order->email) {
//                Mail::to($order->email)->send(new TicketPaidMail($order));
//            } else {
//                Log::warning('Paid but no email to send', ['order_id' => $order->id]);
//            }
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
