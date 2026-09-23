{{--
    Dark footer bar matching partials/header.blade.php.
    Expects: $legalText
--}}
<tr>
    <td style="padding:16px 26px;background:#182a3a;border-top:1px solid #1f2f3d;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td valign="middle">
                    <span style="font-family:'IBM Plex Mono',Consolas,monospace;font-size:10.5px;letter-spacing:1px;color:#6f6858;">
                        MisterioCode
                    </span>
                </td>
                <td valign="middle" align="right">
                    <span style="font-family:'IBM Plex Mono',Consolas,monospace;font-size:10.5px;color:#6f6858;">
                        {{ $legalText }}
                    </span>
                </td>
            </tr>
        </table>
    </td>
</tr>
