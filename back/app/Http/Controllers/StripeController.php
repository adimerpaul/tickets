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

        $session = CheckoutSession::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,

            'success_url' => rtrim(env('FRONTEND_URL'), '/') . '/pago-exitoso?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => rtrim(env('FRONTEND_URL'), '/') . '/pago-cancelado',

            'customer_email' => $validated['customer_email'] ?? null,
            'metadata' => $validated['metadata'] ?? [],
        ]);
//        validated['items'] dividir entre 100
        $validated['items']= array_map(function ($it) {
            $it['unit_amount'] = (int)$it['unit_amount']/100;
            return $it;
        }, $validated['items']);

        // Total en centavos (calculado desde tus items)
        $amountTotal = 0;
        foreach ($validated['items'] as $it) {
//            error_log('Item: ' . json_encode($it));
//            $amountTotal += ((int)$it['unit_amount']/100) * ((int)$it['qty']);
            $amountTotal += ((int)$it['unit_amount']) * ((int)$it['qty']);
        }

        // ✅ Guardar PENDING en BD
//        error_log('validated: ' . json_encode($validated));
        Order::create([
            'session_id' => $session->id,
            'email' => $validated['customer_email'] ?? null,
            'amount_total' => $amountTotal,
            'currency' => 'eur',
            'status' => 'PENDING',
            'metadata' => $validated['metadata'] ?? null,
            'items' => $validated['items'],
        ]);

        return response()->json([
            'checkout_url' => $session->url,
            'session_id' => $session->id,
        ]);
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
