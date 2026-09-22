import { useEffect, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import Card, { CardHeader } from '../components/ui/Card';
import Button from '../components/ui/Button';
import Captcha from '../components/ui/Captcha';
import { TextField, SelectField } from '../components/ui/Field';

/**
 * Redeeming a gift code — a free case, free credits, or both at once. A
 * discount code lives on the checkout screens instead (it needs a price to
 * discount); typing one here gets a clear error pointing that out, thrown by
 * RedeemPromoCode and surfaced as a normal validation error on this form.
 *
 * A code that lets the redeemer pick their own case only reveals that on the
 * first submit (a `case_slug` validation error) — the form doesn't know in
 * advance what kind of code was typed. Once shown, the picker stays up even
 * if a later error is about something else, since by then a case may already
 * be chosen.
 */
export default function Redeem({ cases }) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
        case_slug: '',
        'cf-turnstile-response': '',
    });

    const [showCasePicker, setShowCasePicker] = useState(false);

    // Bumped after every rejected attempt so the captcha issues a fresh
    // token: the previous one was spent by the submission that failed.
    const [captchaKey, setCaptchaKey] = useState(0);

    useEffect(() => {
        if (errors.case_slug) setShowCasePicker(true);
    }, [errors.case_slug]);

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

                    {showCasePicker && (
                        <SelectField
                            id="case_slug"
                            label="Elige un caso"
                            value={data.case_slug}
                            onChange={(value) => setData('case_slug', value)}
                            error={errors.case_slug}
                            options={[
                                { value: '', label: 'Selecciona un caso…' },
                                ...cases.map((item) => ({ value: item.slug, label: item.name })),
                            ]}
                            required
                        />
                    )}

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
