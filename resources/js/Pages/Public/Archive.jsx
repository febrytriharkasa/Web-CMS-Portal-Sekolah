import PublicLayout from '@/Layouts/PublicLayout';
import Seo from '@/Components/Seo';
import Card from '@/Components/Card';
import Breadcrumbs from '@/Components/Breadcrumbs';
import Pagination from '@/Components/Pagination';
import { Link, usePage } from '@inertiajs/react';
import { useState, useRef, lazy, Suspense } from 'react';
import { plainText } from '@/lib/content';
import { youtubeThumbnailUrl } from '@/lib/postBlocks';
import { postUrl } from '@/lib/postUrl';
import { StaggerContainer, Reveal } from '@/Components/Reveal';

const MediaPreviewModal = lazy(() => import('@/Components/MediaPreviewModal'));
import { HorizontalRail } from '@/Pages/Welcome';

export default function Archive({ title, type, previewMode = 'standard', items }) {
    const { settings = {} } = usePage().props;
    const [previewItem, setPreviewItem] = useState(null);
    const post = items.data?.[0] ?? null;
    const siteName = settings.site_name || 'SMA Al Islam Krian';

    const renderItems = () => {
        if (type === 'download') {
            return (
                <StaggerContainer className="space-y-4">
                    {items.data.map((post) => {
                        const attachment = post.metadata?.attachments?.[0];
                        return (
                            <div key={post.id} className="stagger-item">
                            <Card className="p-5 sm:p-6">
                                <Link href={postUrl(post)} className="block group">
                                    <h3 className="text-lg font-bold text-slate-900 group-hover:text-primary transition-colors">
                                        {post.title}
                                    </h3>
                                </Link>
                                {post.content && (
                                    <p className="mt-2 text-sm text-slate-600 leading-relaxed line-clamp-3">
                                        {plainText(post.content)}
                                    </p>
                                )}
                                {attachment && (
                                    <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                                        <span className="text-xs text-slate-500">{post.published_at ? new Date(post.published_at).toLocaleDateString('id-ID') : ''}</span>
                                        <a
                                            href={attachment.url}
                                            download={attachment.name}
                                            className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                                        >
                                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Download {attachment.size}
                                        </a>
                                    </div>
                                )}
                            </Card>
                            </div>
                        );
                    })}
                </StaggerContainer>
            );
        }
        
        if (previewMode === 'media') {
            return (
                <StaggerContainer className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    {items.data.map((item) => {
                        const thumb = item.media_type === 'video' && item.video_url ? youtubeThumbnailUrl(item.video_url) : item.featured_image;
                        return (
                            <div key={item.id} className="stagger-item">
                            <Card className="overflow-hidden group flex flex-col h-full bg-white">
                                <button
                                    type="button"
                                    onClick={() => setPreviewItem(item)}
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
                    })}
                </StaggerContainer>
            );
        }

        if (!['agenda', 'prestasi', 'galeri'].includes(type)) {
            return (
                <StaggerContainer className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    {items.data.map((post) => {
                        const thumbnail = post.media_type === 'video' ? youtubeThumbnailUrl(post.video_url) : post.featured_image;
                        return (
                            <div key={post.id} className="stagger-item">
                            <Card hover className="flex flex-col overflow-hidden h-full">
                                <Link href={postUrl(post)} className="block shrink-0">
                                    <div className="relative aspect-[16/10] overflow-hidden bg-slate-100 flex items-center justify-center group">
                                        {thumbnail ? (
                                            <div className="relative h-full w-full">
                                                <img
                                                    src={thumbnail}
                                                    alt={post.title}
                                                    loading="lazy" decoding="async"
                                                    className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                />
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
                                <div className="flex flex-1 flex-col justify-between p-4 sm:p-5">
                                    <div>
                                        <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-600">
                                            <span className="font-bold text-primary">{post.category?.name || title}</span>
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
                    })}
                </StaggerContainer>
            );
        }

        if (type === 'agenda') {
            return (
                <StaggerContainer className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    {items.data.map((agenda) => {
                        const dateVal = agenda.metadata?.event_date || agenda.metadata?.tanggal_agenda || agenda.published_at;
                        return (
                            <div key={agenda.id} className="stagger-item">
                            <Card hover className="flex flex-col overflow-hidden h-full">
                                <Link href={postUrl(agenda)} className="block shrink-0">
                                    <div className="relative aspect-[16/9] w-full bg-slate-100 flex items-center justify-center">
                                        {agenda.featured_image ? (
                                            <img src={agenda.featured_image} alt={agenda.title} loading="lazy" decoding="async" className="w-full h-full object-cover" />
                                        ) : (
                                            <span className="text-xs text-slate-500 font-medium">Gambar tidak tersedia</span>
                                        )}
                                    </div>
                                </Link>
                                <div className="flex flex-1 flex-col justify-between p-4 sm:p-5">
                                    <div>
                                        <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-600">
                                            <span className="font-bold text-secondary">{agenda.category?.name || 'Agenda'}</span>
                                            <span>
                                                {dateVal ? new Date(dateVal).toLocaleDateString('id-ID') : '-'}
                                            </span>
                                        </div>
                                        <h3 className="mt-2 line-clamp-2 break-words text-base font-bold text-slate-900 sm:mt-3 sm:text-lg">
                                            <Link href={postUrl(agenda)} className="hover:text-primary">{agenda.title}</Link>
                                        </h3>
                                    </div>
                                </div>
                            </Card>
                            </div>
                        );
                    })}
                </StaggerContainer>
            );
        }

        if (type === 'prestasi') {
            return (
                <StaggerContainer className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    {items.data.map((ach) => (
                        <div key={ach.id} className="stagger-item">
                        <Card hover className="overflow-hidden flex flex-col h-full">
                            <Link href={postUrl(ach)} className="block shrink-0">
                                <div className="relative aspect-[16/10] w-full bg-slate-100 flex items-center justify-center">
                                    {ach.featured_image ? (
                                        <img src={ach.featured_image} alt={ach.title} loading="lazy" decoding="async" className="w-full h-full object-cover" />
                                    ) : (
                                        <span className="text-xs text-slate-500 font-medium">Gambar tidak tersedia</span>
                                    )}
                                </div>
                            </Link>
                            <div className="flex flex-1 flex-col justify-between p-4 sm:p-5">
                                <div>
                                    <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-600">
                                        <p className="text-xs font-bold uppercase tracking-wide text-tertiary">{ach.category?.name || 'Prestasi'}</p>
                                        <span>{ach.published_at ? new Date(ach.published_at).toLocaleDateString('id-ID') : '-'}</span>
                                    </div>
                                    <h3 className="mt-2 line-clamp-2 break-words text-base font-bold text-slate-900 sm:text-lg">
                                        <Link href={postUrl(ach)} className="hover:text-primary">{ach.title}</Link>
                                    </h3>
                                </div>
                            </div>
                        </Card>
                        </div>
                    ))}
                </StaggerContainer>
            );
        }

        return null;
    };

    return (
        <PublicLayout>
            <Seo
                title={title}
                description={`Kumpulan ${title.toLowerCase()} terbaru dari ${siteName}, lengkap dengan tanggal dan kategori.`}
                image={post?.featured_image}
            />
            <Breadcrumbs items={[{ label: 'Beranda', href: '/' }, { label: post?.category?.name || title }]} />
            <section className="bg-slate-50 py-12">
                
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Reveal>
                    <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h1 className="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{title}</h1>
                            <p className="mt-2 text-xs font-bold uppercase tracking-[0.14em] text-secondary">Informasi {title} sekolah</p>
                        </div>
                    </div>
                    </Reveal>

                    {renderItems()}

                    <Pagination links={items.links} variant="public" />

                    {items.data.length === 0 && (
                        <div className="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-600">
                            Belum ada data {title.toLowerCase()}.
                        </div>
                    )}
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
