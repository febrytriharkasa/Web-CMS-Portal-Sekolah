import PublicLayout from '@/Layouts/PublicLayout';
import Seo from '@/Components/Seo';
import Breadcrumbs from '@/Components/Breadcrumbs';
import Pagination from '@/Components/Pagination';
import { Link } from '@inertiajs/react';
import { postUrl } from '@/lib/postUrl'
import { useEffect, useState, lazy, Suspense } from 'react';
import { youtubeThumbnailUrl } from '@/lib/postBlocks';
import { StaggerContainer, Reveal } from '@/Components/Reveal';

const MediaPreviewModal = lazy(() => import('@/Components/MediaPreviewModal'));

const FILTERS = [
    { key: null, label: 'Semua' },
    { key: 'image', label: 'Gambar' },
    { key: 'video', label: 'Video' },
];

const EMPTY_TEXT = {
    image: 'Belum ada gambar di galeri ini.',
    video: 'Belum ada video di galeri ini.',
    all: 'Belum ada media di galeri ini.',
};

function GalleryTile({ item, eager, onPreview }) {
    const isVideo = item.media_type === 'video';
    const thumbnail =
        isVideo && item.video_url
            ? youtubeThumbnailUrl(item.video_url)
            : item.featured_image;

    return (
        <figure className="stagger-item group relative overflow-hidden rounded-xl bg-slate-200 shadow-sm">
            <div className="aspect-square">
                {thumbnail ? (
                    <img
                        src={thumbnail}
                        alt={item.title}
                        loading={eager ? 'eager' : 'lazy'}
                        decoding="async"
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                ) : (
                    <span className="flex h-full w-full items-center justify-center p-3 text-center text-xs font-medium text-slate-500">
                        Media tidak tersedia
                    </span>
                )}
            </div>

            <span
                className="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-transparent"
                aria-hidden="true"
            />

            {isVideo && (
                <span
                    className="pointer-events-none absolute inset-0 grid place-items-center"
                    aria-hidden="true"
                >
                    <span className="grid h-11 w-11 place-items-center rounded-full bg-white/90 text-slate-900 shadow-lg">
                        <svg className="h-5 w-5 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z" />
                        </svg>
                    </span>
                </span>
            )}

            <span className="pointer-events-none absolute left-2 top-2 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-700">
                {isVideo ? 'Video' : 'Gambar'}
            </span>

            <figcaption className="pointer-events-none absolute inset-x-0 bottom-0 z-20 p-2">
                <Link
                    href={postUrl(item)}
                    className="pointer-events-auto flex min-h-11 items-center rounded text-xs font-semibold leading-5 text-white underline-offset-2 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-white sm:text-sm"
                >
                    <span className="line-clamp-2">{item.title}</span>
                </Link>
            </figcaption>

            <button
                type="button"
                onClick={onPreview}
                aria-label={`Buka ${isVideo ? 'video' : 'gambar'}: ${item.title}`}
                className="absolute inset-0 z-10 cursor-zoom-in rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary"
            />
        </figure>
    );
}

export default function Gallery({ title = 'Galeri', items, media = null, counts = {} }) {
    const [previewIndex, setPreviewIndex] = useState(null);
    const photos = items.data ?? [];
    const total = counts.all ?? photos.length;

    useEffect(() => {
        setPreviewIndex(null);
    }, [items.current_page, media]);

    return (
        <PublicLayout>
            <Seo
                title={title}
                description={`Kumpulan foto dan video ${title.toLowerCase()} dari SMA Al Islam Krian.`}
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
                            Dokumentasi kegiatan
                        </p>
                    </div>
                    </Reveal>

                    <Reveal delay={0.08}>
                    <div className="mb-8 flex flex-wrap gap-2" role="group" aria-label="Filter media">
                        {FILTERS.map((filter) => {
                            const active = media === filter.key;
                            const count = filter.key ? counts[filter.key] : counts.all;

                            return (
                                <Link
                                    key={filter.label}
                                    href={filter.key ? `/galeri?media=${filter.key}` : '/galeri'}
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

                    {photos.length > 0 ? (
                        <>
                            <StaggerContainer className="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                                {photos.map((item, index) => (
                                    <GalleryTile
                                        key={item.id}
                                        item={item}
                                        eager={index < 4}
                                        onPreview={() => setPreviewIndex(index)}
                                    />
                                ))}
                            </StaggerContainer>

                            <Pagination links={items.links} variant="public" />
                        </>
                    ) : (
                        <div className="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-600">
                            {EMPTY_TEXT[media ?? 'all']}
                        </div>
                    )}
                </div>
            </section>

            {previewIndex !== null && (
                <Suspense fallback={null}>
                    <MediaPreviewModal
                        items={photos}
                        index={previewIndex}
                        onIndexChange={setPreviewIndex}
                        onClose={() => setPreviewIndex(null)}
                    />
                </Suspense>
            )}
        </PublicLayout>
    );
}
