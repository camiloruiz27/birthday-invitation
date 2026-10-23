import { Head } from '@inertiajs/react';
import Button from '../../components/ui/Button';
import QrCode from '../../components/express/QrCode';

/**
 * The stand's poster: print it, tape it up, people scan it.
 *
 * It is a PRINTED object, so it is light and high-contrast whatever the site's
 * theme is (black on white: it has to scan from a metre away and survive a
 * cheap printer). `url` already carries the event's UTM, so each stand is
 * measured separately.
 *
 * On screen it sits on the console with a print button; when printed, only the
 * poster is left (see `.print-poster` in app.css — it also silences the cookie
 * banner). Raw black/white are deliberate here and only here.
 */
export default function Poster({ event, url, title }) {
    return (
        <div className="min-h-dvh bg-surface px-4 py-6 print:min-h-0 print:bg-white print:p-0">
            <Head title={title || 'Cartel Express'} />

            <div className="mx-auto mb-4 flex max-w-md flex-wrap items-center justify-between gap-3 print:hidden">
                <p className="text-sm text-ink-muted">Vista previa del cartel (A4 vertical).</p>
                <Button onClick={() => window.print()}>Imprimir cartel</Button>
            </div>

            <main
                aria-label="Cartel"
                className="print-poster mx-auto flex aspect-[210/297] w-full max-w-md flex-col items-center justify-between bg-white px-6 py-8 text-center text-black shadow-overlay print:aspect-auto print:h-[270mm] print:max-w-none print:px-[15mm] print:py-[12mm] print:shadow-none"
            >
                <div className="flex items-center gap-3">
                    <img
                        src="/brand/isotipo-192.png"
                        width="48"
                        height="48"
                        alt=""
                        className="h-12 w-12 rounded-lg object-cover print:h-16 print:w-16"
                    />
                    <span className="font-display text-2xl font-semibold print:text-4xl">MisterioCode</span>
                </div>

                <div>
                    <h1 className="font-display text-4xl font-bold leading-[1.02] tracking-tight print:text-7xl">
                        Resuelve un caso en 5 minutos
                    </h1>
                    {event?.name && (
                        <p className="mt-3 text-base font-medium uppercase tracking-widest print:mt-6 print:text-2xl">
                            {event.name}
                        </p>
                    )}
                </div>

                <div className="w-full">
                    <QrCode
                        value={url}
                        size={520}
                        fluid
                        alt={`Código QR: escanéalo para jugar${event?.name ? ` en ${event.name}` : ''}`}
                        className="mx-auto rounded-none! border-4 border-black"
                    />
                    <p className="mt-4 text-xl font-bold print:mt-8 print:text-4xl">
                        Escanea y juega con tus amigos
                    </p>
                    <p className="mt-1 text-sm print:text-xl">
                        De 2 a 5 personas · cada celular ve pistas distintas
                    </p>
                </div>
            </main>
        </div>
    );
}
