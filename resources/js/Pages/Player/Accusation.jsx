import { Head, Link, useForm } from '@inertiajs/react';
import ImmersionLayout from '../../Layouts/ImmersionLayout';
import Card from '../../components/ui/Card';
import Button from '../../components/ui/Button';

export default function Accusation({ player, unlocked }) {
    const { data, setData, post, processing } = useForm({
        suspect_name: player.accusation?.suspect_name || '',
        weapon: player.accusation?.weapon || '',
        motive: player.accusation?.motive || '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('immersion.player.accusation.store', player.access_token));
    }

    return (
        <ImmersionLayout
            title="Acusación final"
            headerActions={
                <Link
                    href={route('immersion.player.inbox', player.access_token)}
                    className="border-2 border-paper px-3 py-1 text-xs uppercase tracking-wide hover:bg-paper hover:text-ink"
                >
                    &larr; Bandeja
                </Link>
            }
        >
            <Head title="Acusación final" />

            {!unlocked ? (
                <div className="border-2 border-dashed border-border-soft p-6 text-center text-sm text-muted">
                    El formulario de acusación todavía no está disponible. El Game Master lo habilitará cuando corresponda.
                </div>
            ) : (
                <Card>
                    <h2 className="immersion-stamp text-sm uppercase tracking-[0.2em] text-muted">Tu acusación, {player.name}</h2>
                    <p className="mt-2 text-sm text-muted">
                        Puedes enviarla y actualizarla las veces que quieras mientras el Game Master no revele la solución.
                    </p>

                    <form onSubmit={submit} className="mt-6 space-y-4">
                        <div>
                            <label htmlFor="suspect_name" className="block text-sm font-bold">Sospechoso</label>
                            <input
                                type="text"
                                id="suspect_name"
                                required
                                value={data.suspect_name}
                                onChange={(e) => setData('suspect_name', e.target.value)}
                                className="mt-1 w-full border-2 border-ink bg-white px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label htmlFor="weapon" className="block text-sm font-bold">Arma o método</label>
                            <input
                                type="text"
                                id="weapon"
                                required
                                value={data.weapon}
                                onChange={(e) => setData('weapon', e.target.value)}
                                className="mt-1 w-full border-2 border-ink bg-white px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label htmlFor="motive" className="block text-sm font-bold">Motivo</label>
                            <textarea
                                id="motive"
                                rows={4}
                                required
                                value={data.motive}
                                onChange={(e) => setData('motive', e.target.value)}
                                className="mt-1 w-full border-2 border-ink bg-white px-3 py-2 text-sm"
                            />
                        </div>

                        <Button type="submit" disabled={processing} className="w-full">
                            Enviar acusación
                        </Button>
                    </form>
                </Card>
            )}
        </ImmersionLayout>
    );
}
