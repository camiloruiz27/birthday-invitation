<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu acceso al caso</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@600&family=IBM+Plex+Mono:wght@400;500&family=Courier+Prime:wght@400;700&display=swap');
    </style>
</head>
<body style="margin:0;padding:24px 16px;background:#04070a;font-family:'Courier Prime',Courier,monospace;color:#241f14;">
    <table role="presentation" width="100%" style="max-width:520px;margin:0 auto;background:#182a3a;border:1px solid #2f4356;border-radius:16px;border-collapse:separate;overflow:hidden;">

        @include('immersion::mail.partials.header', [
            'logoPath' => $logoPath,
            'caseCode' => $caseCode,
            'eyebrowText' => 'Confidencial',
            'headerTitle' => 'Tu acceso al caso',
            'player' => $player,
        ])

        <tr>
            <td style="padding:22px;">
                <table role="presentation" width="100%" style="background:#f5efe0;border:1px solid #8a7b57;border-collapse:separate;">
                    <tr>
                        <td style="padding:26px 28px 6px;">
                            <p style="margin:0;font-family:'Courier Prime',Courier,monospace;font-size:14.5px;line-height:1.75;color:#241f14;">
                                Te invitaron a investigar <strong>{{ $caseName }}</strong>. Este enlace es tuyo
                                y solo tuyo &mdash; ábrelo para leer el expediente a medida que llegue.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 28px 28px;text-align:center;">
                            <a href="{{ $inboxUrl }}"
                               style="display:inline-block;background:#88924e;color:#0d1109;text-decoration:none;padding:13px 28px;border-radius:999px;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.6px;">
                                Abrir mi expediente
                            </a>
                            <div style="margin-top:14px;font-family:'IBM Plex Mono',Consolas,monospace;font-size:11.5px;color:#5c5236;background:#dcd2b4;border:1px solid #8a7b57;padding:9px 12px;word-break:break-all;">
                                {{ $inboxUrl }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        @include('immersion::mail.partials.footer', [
            'legalText' => 'No compartas este enlace: es tu identidad dentro del caso. — '.$caseAuthority,
        ])
    </table>
</body>
</html>
