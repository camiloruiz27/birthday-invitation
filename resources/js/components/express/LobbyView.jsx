import { useState } from 'react';
import Alert from '../ui/Alert';
import Button from '../ui/Button';
import Card from '../ui/Card';
import { copyText } from '../ui/InAppBrowserNotice';
import PlayerList from './PlayerList';
import QrCode from './QrCode';

/**
 * The waiting room: the code and the QR to bring the others in, who is here,
 * and — for the host — the button that starts the round. Everything a friend
 * standing next to you needs is on this one screen.
 */
export default function LobbyView({ state, onStart }) {
    const { room, me } = state;
    const players = Array.isArray(state.players) ? state.players : [];
    const host = players.find((player) => player.is_gm);
    const count = players.length;

    const [starting, setStarting] = useState(false);
    const [error, setError] = useState(null);
    const [shareNote, setShareNote] = useState(null);

    async function start() {
        setStarting(true);
        setError(null);

        const result = await onStart();

        setStarting(false);

        if (!result.ok) {
            setError(
                result.code === 'not_enough_players'
                    ? `Faltan jugadores: se necesitan al menos ${room.min_players}.`
                    : result.message || 'No se pudo iniciar. Intenta de nuevo.'
            );
        }
    }

    async function share() {
        const url = room.join_url;

        if (!url) return;

        if (typeof navigator !== 'undefined' && typeof navigator.share === 'function') {
            try {
                await navigator.share({
                    title: `Sala ${room.code}`,
                    text: `Únete a mi sala para resolver "${room.case?.title}" en 3 minutos.`,
                    url,
                });

                return;
            } catch (shareError) {
                // The person closed the sheet: nothing to do. Anything else
                // falls through to copying the link.
                if (shareError?.name === 'AbortError') return;
            }
        }

        const copied = await copyText(url);

        setShareNote(copied ? 'Enlace copiado.' : 'No pudimos copiarlo. Mantén pulsado el enlace de abajo.');
        setTimeout(() => setShareNote(null), 3000);
    }

    return (
        <div className="mx-auto w-full max-w-xl space-y-5 px-4 py-6 sm:px-6">
            <div>
                <p className="case-stamp text-[10px] text-accent">{room.case?.genre || 'Sala de espera'}</p>
                <h1 className="mt-1 font-display text-2xl font-semibold leading-tight text-ink">
                    {room.case?.title}
                </h1>
                {room.case?.tagline && <p className="mt-1 text-sm text-ink-muted">{room.case.tagline}</p>}
            </div>

            <Card as="section" aria-label="Invita a tus amigos" className="text-center">
                <p className="text-sm text-ink-muted">Código de la sala</p>
                <p
                    className="mt-1 select-all font-mono text-5xl font-medium tracking-[0.18em] text-ink"
                    aria-label={`Código ${String(room.code ?? '').split('').join(' ')}`}
                >
                    {room.code}
                </p>

                <div className="mt-4 flex justify-center">
                    <QrCode
                        value={room.join_url}
                        size={208}
                        alt={`Código QR para unirse a la sala ${room.code}`}
                    />
                </div>

                <p className="mt-3 text-sm text-ink-muted">
                    Que escaneen el QR o escriban el código en <span className="text-ink">Tengo un código</span>.
                </p>

                {room.join_url && (
                    <>
                        <Button variant="secondary" fullWidth className="mt-4" onClick={share}>
                            Compartir enlace
                        </Button>
                        <p className="mt-2 min-h-5 text-xs text-ink-muted" aria-live="polite">
                            {shareNote}
                        </p>
                        <p className="select-all break-all text-[11px] text-ink-subtle">{room.join_url}</p>
                    </>
                )}
            </Card>

            <Card as="section" aria-label="Jugadores">
                <div className="mb-3 flex items-baseline justify-between gap-3">
                    <h2 className="text-base font-semibold text-ink">Jugadores</h2>
                    <span className="tabular text-sm text-ink-muted">
                        {count} de {room.max_players}
                        <span className="text-ink-subtle"> · mínimo {room.min_players}</span>
                    </span>
                </div>

                <PlayerList players={players} />

                {room.joinable === false && count >= room.max_players && (
                    <p className="mt-3 text-xs text-ink-muted">La sala está llena.</p>
                )}
            </Card>

            {me?.is_gm ? (
                <div>
                    <Alert variant="error">{error}</Alert>
                    <Button size="lg" fullWidth loading={starting} disabled={!me.can_start} onClick={start}>
                        Iniciar
                    </Button>
                    <p className="mt-2 text-center text-sm text-ink-muted" aria-live="polite">
                        {me.can_start
                            ? 'Cuando todos estén, pulsa Iniciar: el reloj arranca de inmediato.'
                            : `Faltan jugadores: invita de ${room.min_players - 1} a ${room.max_players - 1} amigos.`}
                    </p>
                </div>
            ) : (
                <Card as="section" className="text-center" aria-live="polite">
                    <p className="font-semibold text-ink">
                        Esperando a que {host?.nickname ?? 'el anfitrión'} inicie
                    </p>
                    <p className="mt-1 text-sm text-ink-muted">
                        No cierres esta pantalla: empieza sola cuando arranque el reloj.
                    </p>
                </Card>
            )}
        </div>
    );
}
