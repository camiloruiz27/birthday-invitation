import { Head, Link } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import Container from '../../components/ui/Container';
import EmptyState from '../../components/ui/EmptyState';
import Button from '../../components/ui/Button';
import BlogCard from '../../components/blog/BlogCard';
import { Pagination } from './Index';

export default function Archive({ archive, posts }) {
    return <PublicLayout current="blog.index"><Head title={`${archive.name} | Blog`} /><Container width="wide" className="py-10 sm:py-16"><Link href={route('blog.index')} className="text-sm text-ink-muted hover:text-ink">← Todo el blog</Link><p className="mt-8 text-xs font-medium uppercase tracking-widest text-accent">{archive.type}</p><h1 className="mt-3 font-display text-4xl font-semibold tracking-tight text-ink sm:text-5xl">{archive.name}</h1>{archive.description && <p className="mt-4 max-w-2xl text-lg text-ink-muted">{archive.description}</p>}{posts.data.length ? <><div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">{posts.data.map((post) => <BlogCard key={post.slug} post={post} />)}</div><Pagination links={posts.links} /></> : <EmptyState title="AÃºn no hay artÃ­culos publicados" description="Estamos preparando contenido para este tema." action={<Button href={route('blog.index')} variant="secondary">Ver el blog</Button>} />}</Container></PublicLayout>;
}
