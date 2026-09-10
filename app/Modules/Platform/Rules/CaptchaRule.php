<?php

namespace App\Modules\Platform\Rules;

use App\Modules\Platform\Support\Captcha;
use Illuminate\Contracts\Validation\Rule;

/**
 * Validates the captcha token that Turnstile puts in the form.
 *
 * A rule rather than middleware so the failure arrives the same way every
 * other one does — an ordinary validation error the form can render next to
 * the widget — and so the field name lives in exactly one place per form.
 *
 * The token is single-use: verifying spends it. That is why this is a rule
 * and not something a controller may also call.
 */
class CaptchaRule implements Rule
{
    /**
     * Turnstile's widget writes its token into a hidden input with this
     * name. Fixed by Cloudflare, not by us.
     */
    public const FIELD = 'cf-turnstile-response';

    public function __construct(private ?string $ip = null)
    {
    }

    /**
     * The captcha's slice of a form's validation rules — empty when no keys
     * are configured.
     *
     * Built here rather than written out at each call site because of the
     * `required`: demanding a token when the widget was never rendered would
     * make every form unsubmittable on a machine without Cloudflare keys. One
     * place decides whether the field is expected at all, and the four forms
     * that use it cannot drift apart on that answer.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(?string $ip = null): array
    {
        if (! app(Captcha::class)->enabled()) {
            return [];
        }

        return [self::FIELD => ['required', 'string', new self($ip)]];
    }

    public function passes($attribute, $value): bool
    {
        return app(Captcha::class)->verify(
            is_string($value) ? $value : null,
            $this->ip
        );
    }

    public function message(): string
    {
        return 'No pudimos verificar que no eres un robot. Vuelve a intentarlo.';
    }
}
