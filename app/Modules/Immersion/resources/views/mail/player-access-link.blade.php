<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tu acceso al caso</title>
</head>
<body style="margin:0;padding:24px;background:#e9e2d0;font-family:Georgia,'Times New Roman',serif;color:#241f14;">
    <table role="presentation" width="100%" style="max-width:560px;margin:0 auto;background:#f5efe0;border:1px solid #8a7b57;">
        <tr>
            <td style="padding:18px 24px;background:#241f14;color:#e9e2d0;">
                <p style="margin:0;font-size:11px;letter-spacing:2px;text-transform:uppercase;">
                    @if ($caseCode)Caso {{ $caseCode }} &mdash; @endif Confidencial
                </p>
                <h1 style="margin:6px 0 0;font-size:20px;">Tu acceso al caso</h1>
                <p style="margin:8px 0 0;font-size:12px;color:#c9b98a;">Para: {{ $player->name }}</p>
            </td>
        </tr>

        <tr>
            <td style="padding:26px 28px 10px;">
                <p style="margin:0 0 14px;font-size:15.5px;line-height:1.75;">
                    Te invitaron a investigar <strong>{{ $caseName }}</strong>. Este enlace es tuyo
                    y solo tuyo &mdash; ábrelo para leer el expediente a medida que llegue.
                </p>
            </td>
        </tr>

        <tr>
            <td style="padding:4px 28px 28px;text-align:center;">
                <a href="{{ $inboxUrl }}"
                   style="display:inline-block;background:#241f14;color:#e9e2d0;text-decoration:none;padding:12px 24px;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
                    Abrir mi expediente
                </a>
                <p style="margin:10px 0 0;font-size:11.5px;color:#5c5236;word-break:break-all;">
                    {{ $inboxUrl }}
                </p>
            </td>
        </tr>

        <tr>
            <td style="padding:14px 24px;background:#dcd2b4;font-size:11px;color:#5c5236;">
                No compartas este enlace: es tu identidad dentro del caso. &mdash; {{ $caseAuthority }}
            </td>
        </tr>
    </table>
</body>
</html>
