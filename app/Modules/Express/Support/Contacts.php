<?php

namespace App\Modules\Express\Support;

use Illuminate\Support\Str;

/**
 * What a visitor types as "how to reach me", cleaned up for storage.
 */
final class Contacts
{
    /**
     * A nickname as it may be stored and shown to the rest of the group: no
     * markup, no control characters, one space between words, 24 characters.
     */
    public static function nickname(?string $raw): string
    {
        $clean = strip_tags((string) $raw);
        $clean = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $clean) ?? '';
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? '';

        return Str::limit(trim($clean), 24, '');
    }

    /**
     * An email, or a WhatsApp number in international digits (a bare Colombian
     * mobile gets the 57 prefix). Null when what was typed is not valid.
     *
     * @return array{type: string, value: string}|null
     */
    public static function normalize(?string $type, ?string $raw): ?array
    {
        $raw = trim((string) $raw);

        if ($type === 'email') {
            $email = Str::lower($raw);

            return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 120
                ? ['type' => 'email', 'value' => $email]
                : null;
        }

        if ($type === 'whatsapp') {
            $digits = preg_replace('/\D+/', '', $raw) ?? '';

            if (strlen($digits) === 10 && $digits[0] === '3') {
                $digits = '57'.$digits;
            }

            return strlen($digits) >= 10 && strlen($digits) <= 15
                ? ['type' => 'whatsapp', 'value' => $digits]
                : null;
        }

        return null;
    }
}
