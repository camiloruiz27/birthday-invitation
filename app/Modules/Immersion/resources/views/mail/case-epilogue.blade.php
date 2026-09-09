<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Un mensaje de {{ $suspectName }}</title>
</head>
<body style="margin:0;padding:24px;background:#e9e2d0;font-family:Georgia,'Times New Roman',serif;color:#241f14;">
    <table role="presentation" width="100%" style="max-width:560px;margin:0 auto;background:#f5efe0;border:1px solid #8a7b57;">
        <tr>
            <td style="padding:22px 26px 18px;border-bottom:1px dashed #8a7b57;">
                <table role="presentation" cellpadding="0" cellspacing="0">
                    <tr>
                        @if ($suspectPhotoUrl && file_exists($assetPath($suspectPhotoUrl)))
                            <td valign="top" style="padding-right:14px;">
                                <img src="{{ $message->embed($assetPath($suspectPhotoUrl)) }}"
                                     alt="{{ $suspectName }}"
                                     width="64"
                                     style="display:block;width:64px;height:64px;object-fit:cover;border:1px solid #8a7b57;">
                            </td>
                        @endif
                        <td valign="top">
                            <p style="margin:0;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#5c5236;">
                                El caso está cerrado
                            </p>
                            <h1 style="margin:5px 0 0;font-size:19px;">{{ $suspectName }} te escribió</h1>
                            <p style="margin:6px 0 0;font-size:12.5px;color:#5c5236;">
                                Para: {{ $player->name }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding:24px 28px;">
                {{-- The message itself, verbatim, paragraph by paragraph. --}}
                @foreach (preg_split('/\R{2,}/', trim($body)) as $paragraph)
                    <p style="margin:0 0 14px;font-size:15.5px;line-height:1.8;">{{ $paragraph }}</p>
                @endforeach
            </td>
        </tr>

        <tr>
            <td style="padding:0 28px 24px;">
                <table role="presentation" width="100%"
                       style="border-left:3px solid #241f14;background:#efe6ce;">
                    <tr>
                        <td style="padding:12px 16px;font-size:13px;color:#3a3423;">
                            @if ($wasCorrect)
                                Señalaste a la persona correcta.
                            @else
                                No era {{ $suspectName }}.
                            @endif
                            <a href="{{ $solutionUrl }}" style="color:#241f14;">
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
