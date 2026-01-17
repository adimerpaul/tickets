<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_amount' => 'required|integer|min:1', // en centavos
            'customer_email' => 'nullable|email',
            'metadata' => 'nullable|array',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = array_map(function ($it) {
            return [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $it['name'],
                    ],
                    'unit_amount' => (int) $it['unit_amount'], // centavos
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

        // aquí podrías guardar "pendiente" en tu BD con $session->id

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

        // Evento más importante para “pagado”
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // $session->payment_status === 'paid'
            // $session->id
            // $session->amount_total (centavos)
            // $session->metadata (tu data)

            Log::info('Stripe paid', ['session_id' => $session->id]);

            // 1) buscar en tu BD por session_id
            // 2) marcar como PAGADO
            // 3) emitir ticket / enviar email / etc.
        }

        return response('ok', 200);
    }
}
