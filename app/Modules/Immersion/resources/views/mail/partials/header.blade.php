{{--
    Dark "envelope" chrome shared by the branded case emails (timeline drops,
    the access-link resend). Not used by the epilogue mail — that one is
    deliberately unbranded, see case-epilogue.blade.php.

    Expects: $logoPath, $caseCode (nullable), $eyebrowText, $headerTitle, $player
--}}
<tr>
    <td style="padding:20px 26px 14px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td valign="middle">
                    <table role="presentation" cellpadding="0" cellspacing="0">
                        <tr>
                            <td valign="middle" style="padding-right:10px;">
                                <img src="{{ $message->embed($logoPath) }}" width="30" height="30"
                                     alt="MisterioCode"
                                     style="display:block;width:30px;height:30px;border-radius:8px;">
                            </td>
                            <td valign="middle">
                                <span style="font-family:'Outfit','Segoe UI',Arial,sans-serif;font-weight:600;font-size:14.5px;color:#e8e0d2;">
                                    MisterioCode
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
                @if ($caseCode)
                    <td valign="middle" align="right">
                        <span style="display:inline-block;font-family:'IBM Plex Mono',Consolas,monospace;font-size:10.5px;letter-spacing:1.2px;text-transform:uppercase;color:#a3ae66;background:#333a1f;border:1px solid #4a5730;padding:4px 10px;border-radius:999px;white-space:nowrap;">
                            Caso {{ $caseCode }}
                        </span>
                    </td>
                @endif
            </tr>
        </table>
        <div style="height:1px;line-height:1px;font-size:1px;background:#1f2f3d;margin-top:14px;">&nbsp;</div>
    </td>
</tr>
<tr>
    <td style="padding:18px 26px 20px;">
        <p style="margin:0 0 8px;font-family:'IBM Plex Mono',Consolas,monospace;font-size:10.5px;letter-spacing:1.6px;text-transform:uppercase;color:#6f6858;">
            {{ $eyebrowText }}
        </p>
        <h1 style="margin:0;font-family:'Outfit','Segoe UI',Arial,sans-serif;font-weight:600;font-size:21px;line-height:1.3;color:#e8e0d2;">
            {{ $headerTitle }}
        </h1>
        <p style="margin:10px 0 0;font-family:Arial,sans-serif;font-size:12px;color:#a89f8e;">
            Para: {{ $player->name }}
        </p>
    </td>
</tr>
<tr>
    <td style="background:#88924e;background:linear-gradient(90deg,#333a1f,#88924e 45%,#a3ae66 100%);height:4px;line-height:4px;font-size:1px;">&nbsp;</td>
</tr>
