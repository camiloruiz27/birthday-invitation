<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $event->title }}</title>
</head>
<body style="margin:0;padding:24px;background:#e9e2d0;font-family:Georgia,'Times New Roman',serif;color:#241f14;">
    <table role="presentation" width="100%" style="max-width:640px;margin:0 auto;background:#f5efe0;border:1px solid #8a7b57;">
        <tr>
            <td style="padding:18px 24px;background:#241f14;color:#e9e2d0;">
                <p style="margin:0;font-size:11px;letter-spacing:2px;text-transform:uppercase;">Caso SF 554301 &mdash; Confidencial</p>
                <h1 style="margin:6px 0 0;font-size:20px;">{{ $event->title }}</h1>
            </td>
        </tr>
        <tr>
            <td style="padding:24px;font-size:15px;line-height:1.6;">
                <p style="margin-top:0;">Para: {{ $player->name }}</p>
                @if (trim(strip_tags($bodyHtml)) !== '')
                    <div>{!! $bodyHtml !!}</div>
                @elseif (! empty($gallery))
                    <p style="font-style:italic;color:#5c5236;">Todo el contenido de este sobre está en las imágenes de abajo.</p>
                @endif

                @if (! empty($gallery))
                    <div style="margin-top:20px;padding-top:16px;border-top:1px dashed #8a7b57;">
                        @foreach ($gallery as $item)
                            <div style="margin-bottom:16px;">
                                <img src="{{ $message->embed(public_path('immersion/gallery/'.$item['file'])) }}"
                                     alt="{{ $item['caption'] }}" width="320" style="max-width:100%;border:1px solid #8a7b57;display:block;">
                                <p style="margin:4px 0 0;font-size:11px;color:#5c5236;">{{ $item['caption'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($event->isAudio())
                    <p style="margin-top:20px;font-size:13px;color:#5c5236;">Se adjunta una grabacion de audio relacionada con este mensaje.</p>
                @endif

                @if ($event->cta_interrogation && $player->game->interrogation_enabled)
                    <div style="margin-top:24px;text-align:center;">
                        <a href="{{ route('immersion.player.interrogation.index', $player->access_token) }}"
                           style="display:inline-block;background:#241f14;color:#e9e2d0;text-decoration:none;padding:12px 24px;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
                            Interrogar a los sospechosos
                        </a>
                        <p style="margin-top:8px;font-size:12px;color:#5c5236;">Tienes 5 preguntas por persona.</p>
                    </div>
                @endif

                @if ($event->isUnlock())
                    <div style="margin-top:24px;text-align:center;">
                        <a href="{{ route('immersion.player.accusation', $player->access_token) }}"
                           style="display:inline-block;background:#241f14;color:#e9e2d0;text-decoration:none;padding:12px 24px;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
                            Hacer mi acusacion
                        </a>
                    </div>
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding:14px 24px;background:#dcd2b4;font-size:11px;color:#5c5236;">
                Uso oficial solamente. No redistribuir. &mdash; Departamento de Policia de San Francisco
            </td>
        </tr>
    </table>
</body>
</html>
