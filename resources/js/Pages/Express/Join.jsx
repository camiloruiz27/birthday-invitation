import { Head, useForm } from '@inertiajs/react';
import ExpressLayout, { ExpressContainer } from '../../Layouts/ExpressLayout';
import Alert from '../../components/ui/Alert';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Card from '../../components/ui/Card';
import PlayerFields, { PLAYER_FIELD_DEFAULTS, useRefusalNotice } from '../../components/express/PlayerFields';

/**
 * Why a room cannot be joined, in words. The server sends a short code or its
 * own sentence; an unknown one still gets a sensible message.
 */
function explain(reason) {
    const key = String(reason ?? '').toLowerCase();

    if (['full', 'room_full', 'sala_llena'].includes(key)) {
        return 'La sala ya está llena.';
    }

    if (['started', 'playing', 'accusing', 'in_progress', 'join_closed', 'running'].includes(key)) {
        return 'La ronda ya está en curso y no se puede entrar a mitad de camino.';
    }

    if (['finished', 'reveal', 'closed', 'over', 'ended'].includes(key)) {
        return 'Esa ronda ya terminó.';
    }

    if (['not_found', 'missing', 'expired'].includes(key)) {
        return 'No encontramos esa sala. Revisa el código.';
    }

    // A real sentence from the server.
    if (String(reason ?? '').includes(' ')) return String(reason);

    return 'Esta sala ya no admite jugadores.';
}

function Unjoinable({ room, event }) {
    return (
        <Card as="section" className="mt-6 text-center">
            <h2 className="font-display text-xl font-semibold text-ink">No puedes entrar a esta sala</h2>
            <p className="mt-2 text-sm text-ink-muted">{explain(room.reason)}</p>
            <p className="mt-1 text-sm text-ink-muted">Crea la tuya y juega con tus amigos.</p>
            <Button
                href={route('express.create', event?.slug ? { e: event.slug } : {})}
                size="lg"
                fullWidth
                className="mt-5"
            >
                Crear mi propia sala
            </Button>
        </Card>
    );
}

export default function Join({ event, room, consent = null, errors: serverErrors = {} }) {
    const { data, setData, post, processing, errors, transform } = useForm({
        ...PLAYER_FIELD_DEFAULTS,
        // Harmless for the backend (the room knows its event) but keeps the
        // session's event right if the post fails and the page re-renders.
        event: event?.slug ?? '',
    });
    const [refusal, clearRefusal] = useRefusalNotice();
    const allErrors = { ...serverErrors, ...errors };

    transform((payload) => ({
        ...payload,
        contact: payload.contact_type === 'none' ? '' : payload.contact.trim(),
        nickname: payload.nickname.trim(),
    }));

    function submit(submitEvent) {
        submitEvent.preventDefault();
        clearRefusal();
        post(route('express.join.store', room.code));
    }

    return (
        <ExpressLayout brandHref={route('express.home')}>
            <Head title="Unirme a una sala" />

            <ExpressContainer className="py-8">
                <div className="flex flex-wrap items-center gap-2">
                    {event?.name && <p className="case-stamp text-[10px] text-accent">{event.name}</p>}
                    <Badge tone="neutral">
                        Sala <span className="ml-1 font-mono">{room.code}</span>
                    </Badge>
                </div>

                <h1 className="mt-2 font-display text-3xl font-semibold tracking-tight text-ink">
                    {room.case_title || 'Unirme a una sala'}
                </h1>

                {room.joinable ? (
                    <>
                        <p className="mt-2 text-sm text-ink-muted">
                            Elige un apodo y entra: el anfitrión inicia cuando estén todos.
                        </p>

                        <form onSubmit={submit} className="relative mt-6 space-y-6">
                            <Alert variant="error">{allErrors.website || refusal}</Alert>

                            <PlayerFields data={data} setData={setData} errors={allErrors} consent={consent} />

                            <Button type="submit" size="lg" fullWidth loading={processing}>
                                Entrar a la sala
                            </Button>
                        </form>
                    </>
                ) : (
                    <Unjoinable room={room} event={event} />
                )}
            </ExpressContainer>
        </ExpressLayout>
    );
}
