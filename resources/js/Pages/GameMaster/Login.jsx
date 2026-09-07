import { useForm } from '@inertiajs/react';
import ImmersionLayout from '../../Layouts/ImmersionLayout';
import Card from '../../components/ui/Card';
import Button from '../../components/ui/Button';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({ password: '' });

    function submit(e) {
        e.preventDefault();
        post(route('immersion.gm.login.attempt'));
    }

    return (
        <ImmersionLayout title="Panel del Game Master">
            <Card className="mx-auto max-w-sm">
                <p className="immersion-stamp text-xs uppercase tracking-[0.2em] text-muted">Acceso restringido</p>
                <h2 className="mt-2 text-lg font-bold">Panel del Game Master</h2>

                <form onSubmit={submit} className="mt-6 space-y-4">
                    <div>
                        <label htmlFor="password" className="block text-sm font-bold">Contraseña</label>
                        <input
                            type="password"
                            id="password"
                            required
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            className="mt-1 w-full border-2 border-ink bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ink"
                        />
                        {errors.password && <p className="mt-1 text-xs text-red-800">{errors.password}</p>}
                    </div>
                    <Button type="submit" disabled={processing} className="w-full">
                        Entrar
                    </Button>
                </form>
            </Card>
        </ImmersionLayout>
    );
}
