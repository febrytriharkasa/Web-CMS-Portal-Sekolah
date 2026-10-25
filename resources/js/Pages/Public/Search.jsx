import PublicLayout from '@/Layouts/PublicLayout';
import Seo from '@/Components/Seo';
import Card from '@/Components/Card';
import Breadcrumbs from '@/Components/Breadcrumbs';
import Pagination from '@/Components/Pagination';
import { Link, router } from '@inertiajs/react';
import { useState, lazy, Suspense } from 'react';
import { youtubeThumbnailUrl } from '@/lib/postBlocks';
import { postUrl } from '@/lib/postUrl';
import { StaggerContainer, Reveal } from '@/Components/Reveal';

const MediaPreviewModal = lazy(() => import('@/Components/MediaPreviewModal'));

function MediaCard({ item, onPreview }) {
    const thumb = item.media_type === 'video' && item.video_url ? youtubeThumbnailUrl(item.video_url) : item.featured_image;
    const hasMedia = item.media_type === 'video' ? Boolean(item.video_url) : Boolean(item.featured_image);
    return (
        <div className="stagger-item">
        <Card key={item.id} className="overflow-hidden group flex flex-col h-full bg-white">
            {hasMedia ? (
                <button
                    type="button"
                    onClick={() => onPreview(item)}
                    className="relative aspect-[16/10] w-full bg-slate-100 overflow-hidden text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary cursor-zoom-in"
                    aria-label={`Preview: ${item.title}`}
                >
                    {item.media_type === 'video' && item.video_url ? (
                        <div className="relative w-full h-full bg-slate-800">
                            {thumb ? (
                                <img src={thumb} alt={item.title} loading="lazy" decoding="async" className="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity" />
                            ) : null}
                            <span className="absolute inset-0 flex items-center justify-center" aria-hidden="true">
                                <svg className="w-10 h-10 text-white drop-shadow" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </span>
                        </div>
                    ) : item.featured_image ? (
                        <div className="relative h-full w-full">
                            <img src={item.featured_image} alt={item.title} loading="lazy" decoding="async" className="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                            <span className="absolute bottom-2 right-2 rounded-md bg-slate-900/60 p-1.5 text-white opacity-0 group-hover:opacity-100 transition-opacity" aria-hidden="true">
                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16zm-3-8h6m-3-3v6" />
                                </svg>
                            </span>
                        </div>
                    ) : (
                        <span className="flex h-full w-full items-center justify-center p-2 text-center text-xs text-slate-500 font-medium">Gambar tidak tersedia</span>
                    )}
                </button>
            ) : (
                <Link href={postUrl(item)} className="block">
                    <div className="relative aspect-[16/10] overflow-hidden bg-slate-100 flex items-center justify-center">
                        <span className="text-xs text-slate-500 font-medium">Gambar tidak tersedia</span>
                    </div>
                </Link>
            )}
            <div className="flex flex-1 items-center bg-white p-3">
                <Link
                    href={postUrl(item)}
                    className="line-clamp-2 break-words text-xs font-bold text-slate-800 transition-colors hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded sm:text-sm"
                    title={`Buka detail: ${item.title}`}
                >
                    {item.title}
                </Link>
            </div>
        </Card>
        </div>
    );
}

function StandardCard({ post }) {
    const thumbnail = post.media_type === 'video' ? youtubeThumbnailUrl(post.video_url) : post.featured_image;
    const hasMedia = post.media_type === 'video' ? Boolean(post.video_url) : Boolean(post.featured_image);
    return (
        <div className="stagger-item">
        <Card key={post.id} hover className="flex flex-col overflow-hidden h-full">
            {hasMedia ? (
                <Link href={postUrl(post)} className="block shrink-0">
                    <div className="relative aspect-[16/10] overflow-hidden bg-slate-100 flex items-center justify-center group">
                        {thumbnail ? (
                            <div className="relative h-full w-full">
                                <img src={thumbnail} alt={post.title} loading="lazy" decoding="async" className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                {post.media_type === 'video' && (
                                    <span className="absolute inset-0 flex items-center justify-center bg-slate-900/35" aria-hidden="true">
                                        <svg className="h-9 w-9 text-white drop-shadow" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </span>
                                )}
                            </div>
                        ) : (
                            <span className="text-xs text-slate-500 font-medium">Gambar tidak tersedia</span>
                        )}
                    </div>
                </Link>
            ) : (
                <Link href={postUrl(post)} className="block shrink-0">
                    <div className="relative aspect-[16/10] overflow-hidden bg-slate-100 flex items-center justify-center">
                        <span className="text-xs text-slate-500 font-medium">Gambar tidak tersedia</span>
                    </div>
                </Link>
            )}
            <div className="flex flex-1 flex-col justify-between p-4 sm:p-5">
                <div>
                    <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-600">
                        {post.category && (
                            <span className="font-bold text-primary">{post.category.name}</span>
                        )}
                        <span>
                            {post.published_at ? new Date(post.published_at).toLocaleDateString('id-ID') : '-'}
                        </span>
                    </div>
                    <h3 className="mt-2 line-clamp-2 break-words text-base font-bold text-slate-900 sm:mt-3 sm:text-lg">
                        <Link href={postUrl(post)} className="hover:text-primary">{post.title}</Link>
                    </h3>
                    {post.category?.name?.toLowerCase() === 'berita' && post.user?.name && (
                        <p className="mt-1 break-words text-xs text-slate-500 sm:mt-2">
                            <span className="font-semibold text-slate-700">{post.user.name}</span>
                        </p>
                    )}
                </div>
            </div>
        </Card>
        </div>
    );
}

export default function Search({ keyword = '', items, pages = [], staff = [], counts = {} }) {
    const [searchVal, setSearchVal] = useState(keyword);
    const [previewItem, setPreviewItem] = useState(null);
    const [viewMode, setViewMode] = useState('standard');

    const hasPosts = (items.data?.length ?? 0) > 0;
    const hasPages = pages.length > 0;
    const hasStaff = staff.length > 0;
    const totalResults = (counts.posts ?? items.total ?? 0) + (counts.pages ?? pages.length) + (counts.staff ?? staff.length);

    const handleSearch = (e) => {
        e.preventDefault();
        const trimmed = searchVal.trim();
        router.get('/cari', trimmed ? { q: trimmed } : {});
    };

    return (
        <PublicLayout>
            <Seo
                title={keyword ? `Pencarian: ${keyword}` : 'Pencarian'}
                description="Cari berita, agenda, prestasi, dan galeri kegiatan sekolah."
                robots="noindex, follow"
            />
            <Breadcrumbs
                items={[
                    { label: 'Beranda', href: '/' },
                    { label: 'Pencarian' },
                    ...(keyword ? [{ label: `“${keyword}”`, muted: true }] : []),
                ]}
            />
            <section className="bg-slate-50 py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Reveal>
                    <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h1 className="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                                {keyword ? `Hasil Pencarian: "${keyword}"` : 'Pencarian Informasi'}
                            </h1>
                            <p className="mt-2 text-xs font-bold uppercase tracking-[0.14em] text-secondary">
                                Ditemukan {totalResults} data
                            </p>
                        </div>

                        <form onSubmit={handleSearch} className="flex gap-2 max-w-md w-full sm:w-auto">
                            <input
                                type="text"
                                value={searchVal}
                                onChange={(e) => setSearchVal(e.target.value)}
                                placeholder="Ketik kata kunci..."
                                className="flex-1 min-w-[200px] rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary bg-white shadow-sm"
                            />
                            <button
                                type="submit"
                                className="px-4 py-2 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-colors shadow-sm shrink-0"
                            >
                                Cari
                            </button>
                        </form>
                    </div>
                    </Reveal>

                    {keyword && (hasPages || hasStaff) && (
                        <div className="mb-10 space-y-8">
                            {hasPages && (
                                <section aria-labelledby="hasil-laman">
                                    <h2 id="hasil-laman" className="mb-3 text-xs font-bold uppercase tracking-[0.14em] text-slate-500">
                                        Laman
                                    </h2>
                                    <StaggerContainer className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        {pages.map((page) => (
                                            <div key={page.slug} className="stagger-item">
                                            <Link
                                                href={`/${page.slug}`}
                                                className="group rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                            >
                                                <h3 className="text-sm font-bold text-slate-900 transition-colors group-hover:text-primary">
                                                    {page.title}
                                                </h3>
                                                {page.excerpt && (
                                                    <p className="mt-1 line-clamp-2 text-xs leading-5 text-slate-600">{page.excerpt}</p>
                                                )}
                                            </Link>
                                            </div>
                                        ))}
                                    </StaggerContainer>
                                </section>
                            )}

                            {hasStaff && (
                                <section aria-labelledby="hasil-gtk">
                                    <h2 id="hasil-gtk" className="mb-3 text-xs font-bold uppercase tracking-[0.14em] text-slate-500">
                                        Guru dan Tendik
                                    </h2>
                                    <StaggerContainer className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                        {staff.map((person) => (
                                            <div key={person.id} className="stagger-item">
                                            <Link
                                                href="/guru-tendik"
                                                className="group flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                            >
                                                <span className="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-primary/10 text-sm font-bold text-primary" aria-hidden="true">
                                                    {person.name.charAt(0)}
                                                </span>
                                                <span className="min-w-0">
                                                    <span className="block truncate text-sm font-bold text-slate-900 transition-colors group-hover:text-primary">{person.name}</span>
                                                    <span className="block truncate text-xs text-slate-600">{person.role}</span>
                                                </span>
                                            </Link>
                                            </div>
                                        ))}
                                    </StaggerContainer>
                                </section>
                            )}
                        </div>
                    )}

                    {hasPosts && (
                        <>
                            {keyword && (hasPages || hasStaff) && (
                                <h2 className="mb-3 text-xs font-bold uppercase tracking-[0.14em] text-slate-500">
                                    Berita dan konten
                                </h2>
                            )}

                            <Reveal>
                            <div className="mb-6 flex items-center justify-between gap-4">
                                <div className="flex items-center gap-3">
                                    <span className="text-sm font-medium text-slate-700">Tampilan:</span>
                                    <div className="inline-flex rounded-lg bg-white p-1 shadow-sm" role="radiogroup" aria-label="Pilih mode tampilan">
                                        <button
                                            type="button"
                                            onClick={() => setViewMode('standard')}
                                            aria-pressed={viewMode === 'standard'}
                                            className={`inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md transition ${viewMode === 'standard' ? 'bg-primary text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'}`}
                                        >
                                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                            Daftar
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setViewMode('media')}
                                            aria-pressed={viewMode === 'media'}
                                            className={`inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md transition ${viewMode === 'media' ? 'bg-primary text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'}`}
                                        >
                                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                                            Galeri
                                        </button>
                                    </div>
                                </div>
                            </div>
                            </Reveal>

                            {viewMode === 'media' ? (
                                <StaggerContainer className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                                    {items.data.map((item) => (
                                        <MediaCard key={item.id} item={item} onPreview={setPreviewItem} />
                                    ))}
                                </StaggerContainer>
                            ) : (
                                <StaggerContainer className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                                    {items.data.map((post) => (
                                        <StandardCard key={post.id} post={post} />
                                    ))}
                                </StaggerContainer>
                            )}
                        </>
                    )}

                    {!hasPosts && !hasPages && !hasStaff && (
                        <div className="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-600">
                            {keyword
                                ? `Tidak ditemukan hasil untuk kata kunci “${keyword}”.`
                                : 'Belum ada konten yang bisa ditampilkan.'}
                        </div>
                    )}

                    <Pagination links={items.links} variant="public" />
                </div>
            </section>

            {previewItem && (
                <Suspense fallback={null}>
                    <MediaPreviewModal item={previewItem} onClose={() => setPreviewItem(null)} />
                </Suspense>
            )}
        </PublicLayout>
    );
}
