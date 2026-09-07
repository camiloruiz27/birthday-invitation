import { Head } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import Section from '../../components/public/Section';
import CaseCard from '../../components/public/CaseCard';
import EmptyState from '../../components/ui/EmptyState';
import Button from '../../components/ui/Button';

export default function Catalog({ cases }) {
    return (
        <PublicLayout current="cases.index">
            <Head title="Casos" />

            <Section
                kicker="Catálogo"
                title="Los misterios disponibles"
                description="Cada caso se compra una vez y queda en tu biblioteca para siempre. Puedes dirigirlo las veces que quieras."
            >
                {cases.length === 0 ? (
                    <EmptyState
                        title="Todavía no hay casos publicados"
                        description="Estamos preparando los primeros. Vuelve pronto."
                        action={
                            <Button href={route('home')} variant="secondary">
                                Volver al inicio
                            </Button>
                        }
                    />
                ) : (
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {cases.map((item) => (
                            <CaseCard key={item.slug} mysteryCase={item} />
                        ))}
                    </div>
                )}
            </Section>
        </PublicLayout>
    );
}
