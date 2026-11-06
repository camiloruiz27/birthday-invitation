<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablece tu acceso — MisterioCode</title>
</head>
<body style="margin:0;padding:0;background-color:#04070a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#04070a" style="width:100%;margin:0;padding:0;background-color:#04070a;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;margin:0 auto;background-color:#f5efe0;border-radius:16px;overflow:hidden;">
                    @include('emails.account.partials.header', ['mailLabel' => 'Acceso protegido'])

                    <tr>
                        <td style="height:4px;background-color:#a9b660;font-size:0;line-height:0;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="padding:38px 40px 34px;background-color:#f5efe0;font-family:Arial,Helvetica,sans-serif;color:#18252e;">
                            <p style="margin:0 0 12px;font-family:'Courier New',Courier,monospace;font-size:11px;font-weight:700;line-height:16px;letter-spacing:1.7px;text-transform:uppercase;color:#687428;">
                                Solicitud de seguridad
                            </p>
                            <h1 style="margin:0 0 18px;font-size:30px;font-weight:700;line-height:1.16;letter-spacing:-0.5px;color:#16232c;">
                                Restablece el acceso a tu expediente
                            </h1>
                            <p style="margin:0 0 22px;font-size:16px;line-height:25px;color:#42505a;">
                                Hola, {{ $name }}. Recibimos una solicitud para cambiar la contraseña de tu cuenta.
                            </p>
                            <p style="margin:0 0 27px;font-size:14px;line-height:22px;color:#53606a;">
                                Si fuiste tú, elige una contraseña nueva desde el enlace seguro. Si no reconoces esta solicitud, no tienes que hacer nada: tu contraseña actual seguirá funcionando.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 27px;">
                                <tr>
                                    <td align="center" bgcolor="#a9b660" style="border-radius:8px;background-color:#a9b660;">
                                        <a href="{{ $actionUrl }}" style="display:inline-block;padding:15px 23px;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:700;line-height:20px;color:#11180f;text-decoration:none;border-radius:8px;">
                                            Restablecer mi acceso &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" bgcolor="#e8ebd5" style="width:100%;margin:0 0 26px;background-color:#e8ebd5;border-left:3px solid #879541;">
                                <tr>
                                    <td style="padding:14px 16px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;color:#3f4b31;">
                                        <strong>Enlace seguro:</strong> vence en {{ $expiresInMinutes }} minutos. MisterioCode nunca te pedirá tu contraseña por correo.
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px;font-size:12px;line-height:19px;color:#667078;">
                                ¿El botón no funciona? Copia y pega este enlace en tu navegador:
                            </p>
                            <p style="margin:0;font-family:'Courier New',Courier,monospace;font-size:11px;line-height:17px;word-break:break-all;">
                                <a href="{{ $actionUrl }}" style="color:#53651e;text-decoration:underline;">{{ $actionUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    @include('emails.account.partials.footer')
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
