import { Head } from '@inertiajs/react';
import Brand from '../components/ui/Brand';
import Button from '../components/ui/Button';

const ERROR_DETAILS = {
    403: {
        title: 'Acceso restringido',
        message: 'Este expediente no est\u00e1 autorizado para tu acceso. Vuelve al inicio para continuar la investigaci\u00f3n.',
    },
    404: {
        title: 'La pista no est\u00e1 aqu\u00ed',
        message: 'La p\u00e1gina o el expediente que buscas no existe, o pudo haber cambiado de ubicaci\u00f3n.',
    },
    419: {
        title: 'La sesi\u00f3n expir\u00f3',
        message: 'El enlace de esta investigaci\u00f3n ya no est\u00e1 vigente. Regresa al inicio para continuar.',
    },
    500: {
        title: 'El expediente no pudo procesarse',
        message: 'Ocurri\u00f3 un problema al abrir esta parte de la investigaci\u00f3n. Int\u00e9ntalo de nuevo desde el inicio.',
    },
    502: {
        title: 'Conexi\u00f3n no disponible',
        message: 'No pudimos comunicarnos con el servicio necesario para abrir el expediente. Vuelve al inicio e int\u00e9ntalo m\u00e1s tarde.',
    },
};

export default function Error({ status }) {
    const detail = ERROR_DETAILS[status] ?? ERROR_DETAILS[500];

    return (
        <>
            <Head title={`${status} \u2014 ${detail.title}`} />

            <main className="flex min-h-dvh bg-surface px-5 py-6 sm:px-8 sm:py-10">
                <div className="mx-auto flex w-full max-w-6xl flex-col">
                    <Brand />

                    <section className="my-auto grid max-w-3xl gap-8 py-16 sm:py-24" aria-labelledby="error-title">
                        <p className="case-stamp text-sm text-accent">Expediente no disponible</p>

                        <div className="flex items-start gap-5 sm:gap-8">
                            <span
                                aria-hidden="true"
                                className="font-mono text-5xl font-semibold tracking-tight text-ink-subtle sm:text-7xl"
                            >
                                {status}
                            </span>
                            <span aria-hidden="true" className="mt-2 h-px flex-1 bg-accent-dim" />
                        </div>

                        <div>
                            <h1 id="error-title" className="font-display text-4xl font-semibold tracking-tight text-ink sm:text-6xl">
                                {detail.title}
                            </h1>
                            <p className="mt-5 max-w-xl text-base leading-relaxed text-ink-muted sm:text-lg">
                                {detail.message}
                            </p>
                        </div>

                        <div>
                            <Button href={route('home')} size="lg">
                                Volver al inicio
                            </Button>
                        </div>
                    </section>

                    <p className="case-stamp text-xs text-ink-subtle">MisterioCode \u00b7 Sistema de investigaci\u00f3n</p>
                </div>
            </main>
        </>
    );
}
