export default function PageHeader({ kicker = 'Caso SF 554301 · Confidencial', title, actions }) {
    return (
        <header className="border-double border-4 border-ink bg-ink px-6 py-4 text-paper">
            <div className="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4">
                <div>
                    <p className="immersion-stamp text-xs uppercase tracking-widest text-accent">{kicker}</p>
                    <h1 className="mt-1 text-xl font-bold">{title}</h1>
                </div>
                {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
            </div>
        </header>
    );
}
