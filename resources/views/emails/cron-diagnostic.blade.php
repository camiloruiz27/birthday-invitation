<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del cron — MisterioCode</title>
</head>
<body style="margin:0;padding:0;background-color:#04070a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#04070a" style="background-color:#04070a;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#182a3a;border-radius:16px;overflow:hidden;font-family:Arial,Helvetica,sans-serif;">

                    <tr>
                        <td style="padding:24px 32px;border-bottom:1px solid #2f4356;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-right:10px;vertical-align:middle;">
                                        <img src="{{ $message->embed($logoPath) }}" width="32" height="32" alt="MisterioCode" style="display:block;border-radius:8px;">
                                    </td>
                                    <td style="vertical-align:middle;font-size:16px;font-weight:bold;color:#e8e0d2;">
                                        MisterioCode
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 6px;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#a3ae66;font-family:'Courier New',Courier,monospace;">
                                Diagnóstico automático &middot; envío #{{ $sequence }}
                            </p>
                            <h1 style="margin:0 0 20px;font-size:22px;line-height:1.3;color:#e8e0d2;">
                                El cron está funcionando
                            </h1>

                            <p style="margin:0 0 24px;font-size:14px;line-height:1.6;color:#a89f8e;">
                                Este correo se generó y se envió solo, sin que nadie lo disparara a mano.
                                Si te llegó, el camino completo &mdash; el cron del servidor, la cola y el
                                envío de correo &mdash; funciona de punta a punta.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#080f14;border-radius:12px;">
                                @foreach ($rows as $row)
                                    <tr>
                                        <td style="padding:12px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #1f2f3d;' }}font-size:12px;color:#6f6858;font-family:'Courier New',Courier,monospace;text-transform:uppercase;letter-spacing:1px;">
                                            {{ $row['label'] }}
                                        </td>
                                        <td style="padding:12px 16px;{{ $loop->last ? '' : 'border-bottom:1px solid #1f2f3d;' }}font-size:13px;color:#e8e0d2;text-align:right;font-family:'Courier New',Courier,monospace;">
                                            {{ $row['value'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            <p style="margin:24px 0 0;font-size:12px;line-height:1.6;color:#6f6858;">
                                Programado cada 7 minutos, solo para pruebas. Apágalo en cuanto confirmes
                                que todo funciona: pon
                                <code style="color:#a3ae66;">PLATFORM_CRON_DIAGNOSTIC_ENABLED=false</code>
                                en el <code style="color:#a3ae66;">.env</code> del servidor.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:14px 32px;background-color:#080f14;font-size:11px;color:#6f6858;">
                            Enviado a las {{ $sentAt->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }} &middot; MisterioCode
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
