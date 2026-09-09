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
                <p style="margin:0;font-size:11px;letter-spacing:2px;text-transform:uppercase;">Caso {{ $caseCode }} &mdash; Confidencial</p>
                <h1 style="margin:6px 0 0;font-size:20px;">{{ $event->title }}</h1>
                <p style="margin:8px 0 0;font-size:12px;color:#c9b98a;">Para: {{ $player->name }}</p>
            </td>
        </tr>

        @if (trim(strip_tags($bodyHtml)) !== '')
            <tr>
                <td style="padding:26px 28px 6px;">
                    <div style="font-size:15.5px;line-height:1.75;">{!! $bodyHtml !!}</div>
                </td>
            </tr>
        @elseif (! empty($gallery))
            <tr>
                <td style="padding:26px 28px 6px;">
                    <p style="margin:0;font-style:italic;color:#5c5236;">Todo el contenido de este sobre está en las imágenes de abajo.</p>
                </td>
            </tr>
        @endif

        @if ($event->isAudio())
            <tr>
                <td style="padding:14px 28px 0;">
                    <table role="presentation" width="100%" style="border-left:3px solid #241f14;background:#efe6ce;">
                        <tr><td style="padding:10px 14px;font-size:13px;color:#5c5236;">
                            🔊 Se adjunta una grabación de audio relacionada con este mensaje.
                        </td></tr>
                    </table>
                </td>
            </tr>
        @endif

        @if (! empty($gallery))
            <tr>
                <td style="padding:24px 28px 8px;">
                    <p style="margin:0 0 4px;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;color:#5c5236;border-top:1px dashed #8a7b57;padding-top:16px;">
                        Evidencia y documentos adjuntos
                    </p>
                </td>
            </tr>
            <tr>
                <td style="padding:0 20px 10px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                        @foreach (collect($gallery)->chunk(2) as $row)
                            <tr>
                                @foreach ($row as $item)
                                    <td width="50%" valign="top" style="padding:8px;">
                                        <table role="presentation" width="100%" style="background:#ffffff;border:1px solid #8a7b57;">
                                            <tr><td style="padding:6px;">
                                                <img src="{{ $message->embed($assetPath($item['url'])) }}"
                                                     alt="{{ $item['caption'] }}" width="280" style="max-width:100%;display:block;">
                                            </td></tr>
                                            <tr><td style="padding:0 8px 8px;font-size:11px;line-height:1.4;color:#5c5236;">
                                                {{ $item['caption'] }}
                                            </td></tr>
                                        </table>
                                    </td>
                                @endforeach
                                @if ($row->count() === 1)
                                    <td width="50%"></td>
                                @endif
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        @endif

        @if ($event->cta_interrogation && $player->game->interrogation_enabled)
            <tr>
                <td style="padding:24px 28px 4px;text-align:center;">
                    <a href="{{ route('immersion.player.interrogation.index', $player->access_token) }}"
                       style="display:inline-block;background:#241f14;color:#e9e2d0;text-decoration:none;padding:12px 24px;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
                        Interrogar a los sospechosos
                    </a>
                    <p style="margin:8px 0 0;font-size:12px;color:#5c5236;">Tienes {{ $interrogationQuestions }} preguntas por persona.</p>
                </td>
            </tr>
        @endif

        @if ($event->isUnlock())
            <tr>
                <td style="padding:24px 28px 4px;text-align:center;">
                    <a href="{{ route('immersion.player.accusation', $player->access_token) }}"
                       style="display:inline-block;background:#241f14;color:#e9e2d0;text-decoration:none;padding:12px 24px;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
                        Hacer mi acusacion
                    </a>
                </td>
            </tr>
        @endif

        <tr>
            <td style="padding:20px 24px 16px;">&nbsp;</td>
        </tr>
        <tr>
            <td style="padding:14px 24px;background:#dcd2b4;font-size:11px;color:#5c5236;">
                Uso oficial solamente. No redistribuir. &mdash; {{ $caseAuthority }}
            </td>
        </tr>
    </table>
</body>
</html>
