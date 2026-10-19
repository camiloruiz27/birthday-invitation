import { Head } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import Section from '../../components/public/Section';
import CaseCard from '../../components/public/CaseCard';
import EmptyState from '../../components/ui/EmptyState';
import Button from '../../components/ui/Button';
import Reveal from '../../components/ui/Reveal';
import CaseDiscoveryControls from '../../components/cases/CaseDiscoveryControls';
import useCaseDiscovery from '../../hooks/useCaseDiscovery';

export default function Catalog({ cases }) {
    const discovery = useCaseDiscovery(cases, 'catalog');

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
                ) : <>
                    <CaseDiscoveryControls discovery={discovery} />

                    {discovery.results.length === 0 ? (
                        <EmptyState
                            title="No encontramos casos con esos criterios"
                            description="Prueba con otra búsqueda o elimina algunos filtros para ver más misterios."
                            action={<Button type="button" onClick={discovery.reset} variant="secondary">Limpiar filtros</Button>}
                        />
                    ) : (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {discovery.results.map((item, index) => (
                                <Reveal key={item.slug} delay={(index % 3) * 0.1}>
                                    <CaseCard mysteryCase={item} />
                                </Reveal>
                            ))}
                        </div>
                    )}
                </>}
            </Section>
        </PublicLayout>
    );
}
