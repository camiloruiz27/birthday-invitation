<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Un mensaje de {{ $suspectName }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@600&family=Courier+Prime:wght@400;700&display=swap');
    </style>
</head>
{{-- Deliberately not styled as another case file: the investigation is over
     and this is not evidence, it's someone writing back. No dark chrome, no
     logo — see the note on CaseEpilogueMail. It still shares the same paper
     palette and Courier Prime body as the branded mails, so it reads as the
     same family without looking like another envelope. --}}
<body style="margin:0;padding:24px 16px;background:#e9e2d0;font-family:'Courier Prime',Courier,monospace;color:#241f14;">
    <table role="presentation" width="100%" style="max-width:560px;margin:0 auto;background:#f5efe0;border:1px solid #8a7b57;border-radius:16px;border-collapse:separate;overflow:hidden;">
        <tr>
            <td style="padding:26px 30px 20px;border-bottom:1px dashed #8a7b57;">
                <table role="presentation" cellpadding="0" cellspacing="0">
                    <tr>
                        @if ($suspectPhotoUrl && file_exists($assetPath($suspectPhotoUrl)))
                            <td valign="top" style="padding-right:16px;">
                                <img src="{{ $message->embed($assetPath($suspectPhotoUrl)) }}"
                                     alt="{{ $suspectName }}"
                                     width="56" height="56"
                                     style="display:block;width:56px;height:56px;border-radius:50%;object-fit:cover;border:1px solid #8a7b57;">
                            </td>
                        @endif
                        <td valign="top">
                            <p style="margin:0;font-family:'IBM Plex Mono',Consolas,monospace;font-size:10.5px;letter-spacing:1.5px;text-transform:uppercase;color:#5c5236;">
                                El caso está cerrado
                            </p>
                            <h1 style="margin:6px 0 0;font-family:'Outfit','Segoe UI',Arial,sans-serif;font-weight:600;font-size:19px;color:#241f14;">
                                {{ $suspectName }} te escribió
                            </h1>
                            <p style="margin:6px 0 0;font-family:Arial,sans-serif;font-size:12.5px;color:#5c5236;">
                                Para: {{ $player->name }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding:24px 30px;">
                {{-- The message itself, verbatim, paragraph by paragraph. --}}
                @foreach (preg_split('/\R{2,}/', trim($body)) as $paragraph)
                    <p style="margin:0 0 15px;font-family:'Courier Prime',Courier,monospace;font-size:14.5px;line-height:1.85;color:#241f14;">{{ $paragraph }}</p>
                @endforeach
            </td>
        </tr>

        <tr>
            <td style="padding:0 30px 26px;">
                <table role="presentation" width="100%" style="background:#dcd2b4;border-left:3px solid #241f14;border-collapse:separate;">
                    <tr>
                        <td style="padding:13px 16px;font-family:Arial,sans-serif;font-size:13px;line-height:1.6;color:#3a3423;">
                            @if ($wasCorrect)
                                Señalaste a la persona correcta.
                            @else
                                No era {{ $suspectName }}.
                            @endif
                            <a href="{{ $solutionUrl }}" style="color:#241f14;text-decoration:underline;">
                                Vuelve a la solución del caso
                            </a>
                            para ver la reconstrucción completa y quién más acertó.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
