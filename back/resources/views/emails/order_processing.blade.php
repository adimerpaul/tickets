<!doctype html>
<html>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial, sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                <tr>
                    <td style="background:#0f172a;color:#ffffff;padding:16px 20px;">
                        <div style="font-size:18px;font-weight:700;">Tu pedido esta en proceso</div>
                        <div style="font-size:12px;color:#cbd5e1;">Orden #{{ $order->id }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 20px;color:#111827;">
                        <p style="margin:0 0 10px 0;">Hola, {{ $order->email }}.</p>
                        <p style="margin:0 0 14px 0;color:#4b5563;">
                            Estamos procesando tu pedido. En breve recibiras otro correo con las entradas.
                        </p>

                        @if(!empty($custom_message))
                            <p style="margin:0 0 12px 0;">{{ $custom_message }}</p>
                        @endif

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:12px;">
                            <tr>
                                <td style="font-size:11px;color:#6b7280;">Fecha</td>
                                <td align="right" style="font-size:12px;font-weight:700;">
                                    @php
                                        $meta = $order->metadata ?? $order->meta ?? [];
                                        $fecha = data_get($meta, 'date', null);
                                        if (!$fecha && !empty($order->starts_at)) {
                                            $fecha = \Carbon\Carbon::parse($order->starts_at)->format('Y-m-d');
                                        }
                                    @endphp
                                    {{ $fecha ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size:11px;color:#6b7280;">Hora</td>
                                <td align="right" style="font-size:12px;font-weight:700;">
                                    @php
                                        $hora = data_get($meta, 'time', null);
                                        if (!$hora && !empty($order->starts_at)) {
                                            $hora = \Carbon\Carbon::parse($order->starts_at)->format('H:i');
                                        }
                                    @endphp
                                    {{ $hora ?? '-' }}
                                </td>
                            </tr>
                        </table>

                        <div style="padding:10px;border:1px dashed #cbd5e1;border-radius:8px;text-align:center;">
                            <div style="font-size:11px;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;">Orden</div>
                            <div style="font-size:14px;font-weight:700;">#{{ $order->id }}</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:12px 20px;font-size:11px;color:#6b7280;border-top:1px solid #e5e7eb;">
                        Gracias por tu compra.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
