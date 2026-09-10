import { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import Card, { CardHeader } from '../components/ui/Card';
import Button from '../components/ui/Button';
import Captcha from '../components/ui/Captcha';
import { TextField } from '../components/ui/Field';

/**
 * Redeeming a gift code — a free case, free credits, or both at once. A
 * discount code lives on the checkout screens instead (it needs a price to
 * discount); typing one here gets a clear error pointing that out, thrown by
 * RedeemPromoCode and surfaced as a normal validation error on this form.
 */
export default function Redeem() {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        'cf-turnstile-response': '',
    });

    // Bumped after every rejected attempt so the captcha issues a fresh
    // token: the previous one was spent by the submission that failed.
    const [captchaKey, setCaptchaKey] = useState(0);

    function submit(event) {
        event.preventDefault();
        post(route('promo.redeem.store'), {
            preserveScroll: true,
            onError: () => setCaptchaKey((key) => key + 1),
        });
    }

    return (
        <AppLayout current="credits" kicker="Créditos" title="Canjear código">
            <Head title="Canjear código" />

            <Card as="section" className="mx-auto max-w-md">
                <CardHeader
                    title="¿Tienes un código?"
                    description="Un caso, créditos, o ambos — según lo que entregue tu código."
                />

                <form onSubmit={submit} className="space-y-5">
                    <TextField
                        id="code"
                        label="Código"
                        value={data.code}
                        onChange={(value) => setData('code', value.toUpperCase())}
                        error={errors.code}
                        placeholder="LANZAMIENTO2026"
                        autoComplete="off"
                        required
                    />

                    <Captcha
                        onToken={(token) => setData('cf-turnstile-response', token)}
                        error={errors['cf-turnstile-response']}
                        resetKey={captchaKey}
                    />

                    <Button type="submit" loading={processing} fullWidth>
                        {processing ? 'Canjeando…' : 'Canjear'}
                    </Button>
                </form>
            </Card>
        </AppLayout>
    );
}
