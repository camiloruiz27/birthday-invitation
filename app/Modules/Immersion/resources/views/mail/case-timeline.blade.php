<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@600&family=IBM+Plex+Mono:wght@400;500&family=Courier+Prime:wght@400;700&display=swap');

        /* The event body arrives as raw HTML from markdown (see CaseTimelineMail),
           so it can't carry inline styles of its own — this is the only place
           that typography is set. */
        .case-body h1, .case-body h2, .case-body h3 {
            font-family: 'IBM Plex Mono', Consolas, monospace;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 12px;
            color: #5c5236;
            margin: 22px 0 10px;
            border-top: 1px dashed #8a7b57;
            padding-top: 14px;
        }
        .case-body h1:first-child, .case-body h2:first-child, .case-body h3:first-child {
            margin-top: 0;
            border-top: 0;
            padding-top: 0;
        }
        .case-body p { margin: 0 0 14px; }
        .case-body ul, .case-body ol { margin: 0 0 14px; padding-left: 22px; }
        .case-body li { margin: 0 0 6px; }
        .case-body strong { font-weight: 700; }
        .case-body hr { border: none; border-top: 1px dashed #8a7b57; margin: 18px 0; }
        .case-body blockquote {
            margin: 0 0 14px; padding: 2px 0 2px 14px;
            border-left: 3px solid #8a7b57; color: #5c5236;
        }
    </style>
</head>
<body style="margin:0;padding:24px 16px;background:#04070a;font-family:'Courier Prime',Courier,monospace;color:#241f14;">
    <table role="presentation" width="100%" style="max-width:600px;margin:0 auto;background:#182a3a;border:1px solid #2f4356;border-radius:16px;border-collapse:separate;overflow:hidden;">

        @include('immersion::mail.partials.header', [
            'logoPath' => $logoPath,
            'caseCode' => $caseCode,
            'eyebrowText' => 'Expediente confidencial',
            'headerTitle' => $event->title,
            'player' => $player,
        ])

        <tr>
            <td style="padding:22px;">
                <table role="presentation" width="100%" style="background:#f5efe0;border:1px solid #8a7b57;border-collapse:separate;">

                    @if (trim(strip_tags($bodyHtml)) !== '')
                        <tr>
                            <td style="padding:26px 28px 8px;">
                                <div class="case-body" style="font-family:'Courier Prime',Courier,monospace;font-size:14.5px;line-height:1.75;color:#241f14;">
                                    {!! $bodyHtml !!}
                                </div>
                            </td>
                        </tr>
                    @elseif (! empty($gallery))
                        <tr>
                            <td style="padding:26px 28px 8px;">
                                <p style="margin:0;font-family:'Courier Prime',Courier,monospace;font-style:italic;font-size:14.5px;color:#5c5236;">
                                    Todo el contenido de este sobre está en las imágenes de abajo.
                                </p>
                            </td>
                        </tr>
                    @endif

                    @if ($event->isAudio())
                        <tr>
                            <td style="padding:0 28px 22px;">
                                <table role="presentation" width="100%" style="background:#dcd2b4;border-left:3px solid #88924e;border-collapse:separate;">
                                    <tr>
                                        <td style="padding:11px 14px;font-family:'IBM Plex Mono',Consolas,monospace;font-size:12px;color:#5c5236;">
                                            &#128266; Se adjunta una grabación de audio relacionada con este mensaje.
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if (! empty($gallery))
                        <tr>
                            <td style="padding:0 28px;">
                                <p style="margin:0;padding-top:16px;font-family:'IBM Plex Mono',Consolas,monospace;font-size:10.5px;letter-spacing:1.5px;text-transform:uppercase;color:#5c5236;border-top:1px dashed #8a7b57;">
                                    Evidencia y documentos adjuntos
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:12px 20px 10px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                    @foreach (collect($gallery)->chunk(2) as $row)
                                        <tr>
                                            @foreach ($row as $i => $item)
                                                <td width="50%" valign="top" style="padding:8px;">
                                                    <table role="presentation" width="100%" style="background:#ffffff;border:1px solid #8a7b57;border-collapse:separate;">
                                                        <tr>
                                                            <td style="padding:0;">
                                                                <img src="{{ $message->embed($assetPath($item['url'])) }}"
                                                                     alt="{{ $item['caption'] }}" width="280"
                                                                     style="max-width:100%;display:block;border-bottom:1px solid #8a7b57;">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:8px 10px;">
                                                                <span style="display:inline-block;margin-bottom:4px;font-family:'IBM Plex Mono',Consolas,monospace;font-size:9px;letter-spacing:1px;color:#88924e;background:#333a1f;padding:2px 6px;border-radius:3px;">
                                                                    Evid. {{ str_pad((string) ($loop->parent->index * 2 + $i + 1), 2, '0', STR_PAD_LEFT) }}
                                                                </span>
                                                                <div style="font-family:'IBM Plex Mono',Consolas,monospace;font-size:10.5px;line-height:1.4;color:#5c5236;">
                                                                    {{ $item['caption'] }}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            @endforeach
                                            @if ($row->count() === 1)
                                                <td width="50%">&nbsp;</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if ($event->cta_interrogation && $player->game->interrogation_enabled)
                        <tr>
                            <td style="padding:22px 28px 4px;text-align:center;">
                                <a href="{{ route('immersion.player.interrogation.index', $player->access_token) }}"
                                   style="display:inline-block;background:#88924e;color:#0d1109;text-decoration:none;padding:13px 28px;border-radius:999px;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.6px;">
                                    Interrogar a los sospechosos
                                </a>
                                <p style="margin:10px 0 0;font-family:Arial,sans-serif;font-size:12px;color:#5c5236;">
                                    Tienes {{ $interrogationQuestions }} preguntas por persona.
                                </p>
                            </td>
                        </tr>
                    @endif

                    @if ($event->isUnlock())
                        <tr>
                            <td style="padding:18px 28px 26px;text-align:center;">
                                <a href="{{ route('immersion.player.accusation', $player->access_token) }}"
                                   style="display:inline-block;background:transparent;color:#241f14;text-decoration:none;padding:12px 27px;border-radius:999px;border:1.5px solid #241f14;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:0.6px;">
                                    Hacer mi acusación
                                </a>
                            </td>
                        </tr>
                    @endif

                    <tr><td style="padding-bottom:4px;">&nbsp;</td></tr>
                </table>
            </td>
        </tr>

        @include('immersion::mail.partials.footer', [
            'legalText' => 'Uso oficial solamente. No redistribuir. — '.$caseAuthority,
        ])
    </table>
</body>
</html>
