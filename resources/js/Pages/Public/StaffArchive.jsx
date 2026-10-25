import PublicLayout from '@/Layouts/PublicLayout';
import Seo from '@/Components/Seo';
import Breadcrumbs from '@/Components/Breadcrumbs';
import Card from '@/Components/Card';
import Pagination from '@/Components/Pagination';
import { Link } from '@inertiajs/react';
import { StaggerContainer, Reveal } from '@/Components/Reveal';

const FILTERS = [
    { key: null, label: 'Semua' },
    { key: 'guru', label: 'Guru' },
    { key: 'tendik', label: 'Tenaga Kependidikan' },
];

const typeLabels = {
    guru: 'Guru',
    tendik: 'Tendik',
    gtk: 'GTK',
};

function StaffCard({ person }) {
    const initials = (person.name || '?')
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();

    return (
        <Card hover className="group relative flex flex-col items-center p-5 text-center h-full">
            <div className="relative mb-4 h-24 w-24 shrink-0 sm:h-28 sm:w-28">
                {person.photo ? (
                    <img
                        src={person.photo}
                        alt={`Foto ${person.name}`}
                        loading="lazy"
                        decoding="async"
                        className="h-full w-full rounded-full border-2 border-slate-200 object-cover shadow-sm transition-transform duration-300 group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center rounded-full bg-primary/10 text-xl font-bold text-primary border-2 border-primary/20 shadow-sm transition-transform duration-300 group-hover:scale-105">
                        {initials}
                    </div>
                )}
            </div>

            <span
                className={`mb-2 inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                    person.type === 'guru'
                        ? 'bg-tertiary/10 text-tertiary'
                        : 'bg-secondary/10 text-secondary'
                }`}
            >
                {typeLabels[person.type] || person.type}
            </span>

            <h3 className="line-clamp-2 text-sm font-bold text-slate-900 transition-colors group-hover:text-primary sm:text-base">
                <Link
                    href={`/guru-tendik/${person.id}`}
                    className="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded"
                >
                    <span className="absolute inset-0" aria-hidden="true" />
                    {person.name}
                </Link>
            </h3>

            <p className="mt-1 line-clamp-2 text-xs text-slate-600 sm:text-sm">
                {person.role}
            </p>
        </Card>
    );
}

export default function StaffArchive({ title = 'Guru dan Tenaga Kependidikan', type = null, counts = {}, items }) {
    const staffList = items?.data ?? [];

    return (
        <PublicLayout>
            <Seo
                title={title}
                description="Profil pendidik dan tenaga kependidikan berdedikasi SMA Al Islam Krian."
            />
            <Breadcrumbs items={[{ label: 'Beranda', href: '/' }, { label: title }]} />

            <section className="bg-slate-50 py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Reveal>
                        <div className="mb-8">
                            <h1 className="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                                {title}
                            </h1>
                            <p className="mt-2 text-xs font-bold uppercase tracking-[0.14em] text-secondary">
                                Tenaga pendidik dan kependidikan profesional
                            </p>
                        </div>
                    </Reveal>

                    <Reveal delay={0.08}>
                        <div className="mb-8 flex flex-wrap gap-2" role="group" aria-label="Filter kategori staff">
                            {FILTERS.map((filter) => {
                                const active = type === filter.key;
                                const count = filter.key ? counts[filter.key] : counts.all;

                                return (
                                    <Link
                                        key={filter.label}
                                        href={filter.key ? `/guru-tendik?type=${filter.key}` : '/guru-tendik'}
                                        preserveScroll
                                        aria-current={active ? 'true' : undefined}
                                        className={`inline-flex min-h-10 items-center gap-2 rounded-full px-4 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 ${
                                            active
                                                ? 'bg-primary text-white shadow-sm'
                                                : 'bg-white text-slate-700 shadow-sm ring-1 ring-slate-200 hover:text-primary'
                                        }`}
                                    >
                                        {filter.label}
                                        <span
                                            className={`rounded-full px-2 py-0.5 text-xs tabular-nums ${
                                                active ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'
                                            }`}
                                        >
                                            {count ?? 0}
                                        </span>
                                    </Link>
                                );
                            })}
                        </div>
                    </Reveal>

                    {staffList.length > 0 ? (
                        <>
                            <StaggerContainer className="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                                {staffList.map((person) => (
                                    <div key={person.id} className="stagger-item">
                                        <StaffCard person={person} />
                                    </div>
                                ))}
                            </StaggerContainer>

                            <Pagination links={items.links} variant="public" />
                        </>
                    ) : (
                        <div className="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-600">
                            Belum ada data guru atau tenaga kependidikan pada kategori ini.
                        </div>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
