<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .card { border: 1px solid #ddd; padding: 16px; border-radius: 10px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 6px; }
        .muted { color: #666; }
        .row { display: flex; justify-content: space-between; margin-top: 10px; }
        .qr { margin-top: 14px; text-align: center; }
        .footer { margin-top: 18px; font-size: 10px; color: #777; }
    </style>
</head>
<body>
<div class="card">
    <div class="title">Entrada - Pago confirmado</div>
    <div class="muted">Orden #{{ $order->id }}</div>

    <div class="row">
        <div>
            <div><b>Email:</b> {{ $order->email }}</div>
            <div><b>Fecha:</b> {{ data_get($order->meta, 'date', '-') }}</div>
            <div><b>Hora:</b> {{ data_get($order->meta, 'time', '-') }}</div>
        </div>
        <div style="text-align:right">
            <div><b>Total:</b> €{{ number_format($order->total / 100, 2) }}</div>
            <div><b>Adultos:</b> {{ data_get($order->meta, 'adults', 0) }}</div>
            <div><b>Niños:</b> {{ data_get($order->meta, 'kids', 0) }}</div>
        </div>
    </div>

    <div class="qr">
        {{-- Opcional: QR con la session_id o un código --}}
        <div class="muted">Código: {{ $order->session_id }}</div>
    </div>

    <div class="footer">
        Presenta este PDF en la entrada. Válido solo para la fecha/hora indicada.
    </div>
</div>
</body>
</html>
