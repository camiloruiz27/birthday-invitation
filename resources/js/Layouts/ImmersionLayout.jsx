import { usePage } from '@inertiajs/react';
import PageHeader from '../components/ui/PageHeader';
import Alert from '../components/ui/Alert';

export default function ImmersionLayout({ title, headerActions, children }) {
    const { props } = usePage();
    const status = props.flash?.status;
    const errors = props.errors || {};
    const hasErrors = Object.keys(errors).length > 0;

    return (
        <div className="min-h-screen">
            <PageHeader title={title} actions={headerActions} />
            <main className="mx-auto max-w-5xl px-6 py-8">
                <Alert variant="status">{status}</Alert>
                {hasErrors && (
                    <Alert variant="error">
                        <ul className="list-inside list-disc">
                            {Object.values(errors).map((message, index) => (
                                <li key={index}>{message}</li>
                            ))}
                        </ul>
                    </Alert>
                )}
                {children}
            </main>
        </div>
    );
}
