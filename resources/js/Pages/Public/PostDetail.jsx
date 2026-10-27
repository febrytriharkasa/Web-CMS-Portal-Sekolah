import { useState, lazy, Suspense } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import Seo, { absoluteUrl } from '@/Components/Seo';
import Breadcrumbs from '@/Components/Breadcrumbs';
import DataTable from '@/Components/DataTable';
import RichContent from '@/Components/RichContent';
import { blocksFromMetadata, youtubeThumbnailUrl } from '@/lib/postBlocks';
import { plainText } from '@/lib/content';
import { postUrl } from '@/lib/postUrl';
import { Reveal } from '@/Components/Reveal';

const MediaPreviewModal = lazy(() => import('@/Components/MediaPreviewModal'));

// Media can be an uploaded image, an uploaded video, or a YouTube link.
// Turning a YouTube URL into an embed URL keeps the video inside the article instead of sending readers away.
function youtubeEmbedUrl(url) {
    if (!url) {
        return null;
    }
    const match = url.match(
        /(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/,
    );
    return match ? `https://www.youtube.com/embed/${match[1]}` : null;
}

function PostMedia({ post }) {
    const [previewItem, setPreviewItem] = useState(null);
    const youtube = post.media_type === 'video' ? youtubeEmbedUrl(post.video_url) : null;

    // Video: satu container 16:9 dengan iframe absolute supaya rasio embed
    // tidak terpotong (sebelumnya wrapper 16:9 vs tombol 16:10 bikin ke-zoom).
    if (youtube) {
        return (
            <figure className="mb-6">
                <div className="relative aspect-video w-full overflow-hidden rounded-2xl bg-slate-900 shadow-md">
                    <iframe
                        src={youtube}
                        title={post.title}
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowFullScreen
                        className="absolute inset-0 h-full w-full border-0"
                    />
                </div>
                <figcaption className="mt-2 px-2 text-xs italic text-slate-500">
                    Video — {post.category?.name || 'SMA XYZ'}
                </figcaption>
            </figure>
        );
    }

    // Gambar: biarkan rasio asli gambar yang menentukan tinggi card, jadi tidak
    // ada ruang kosong di bawah dan tidak ada bagian yang terpotong.
    if (post.featured_image) {
        return (
            <figure className="mb-6">
                <div className="flex justify-center">
                    <button
                        type="button"
                        onClick={() => setPreviewItem(post)}
                        className="block max-w-full cursor-zoom-in overflow-hidden rounded-2xl bg-slate-100 shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        aria-label={`Perbesar gambar: ${post.title}`}
                    >
                        <img
                            src={post.featured_image}
                            alt={post.title}
                            decoding="async"
                            className="block h-auto max-h-[70vh] w-auto max-w-full object-contain"
                        />
                    </button>
                </div>
                <figcaption className="mt-2 px-2 text-xs italic text-slate-500">
                    Dokumentasi — {post.category?.name || 'SMA XYZ'}
                </figcaption>
                {previewItem && (
                    <Suspense fallback={null}>
                        <MediaPreviewModal item={previewItem} onClose={() => setPreviewItem(null)} />
                    </Suspense>
                )}
            </figure>
        );
    }

    return null;
}

function MetadataTable({ metadata, schema, categoryName }) {
    const labels = Object.fromEntries(
        (Array.isArray(schema) ? schema : [])
            .filter((field) => field?.key && field?.label)
            .map((field) => [field.key, field.label]),
    );
    const formatKey = (key) => key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    const rows = Object.entries(metadata || {}).filter(
        ([key, val]) =>
            key !== 'achievement_table' &&
            key !== 'tables' &&
            key !== 'blocks' &&
            key !== 'attachments' &&
            val !== null &&
            val !== '' &&
            !(Array.isArray(val) && val.length === 0),
    );
    if (rows.length === 0) {
        return null;
    }
    return (
        <div className="mt-8">
            <div className="overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                <div className="bg-primary px-5 py-3">
                    <h3 className="text-sm font-bold text-white">Detail {categoryName || 'Informasi'}</h3>
                </div>
                <table className="w-full table-fixed text-left border-collapse text-sm">
                    <tbody className="divide-y divide-slate-100">
                        {rows.map(([k, v], rowIndex) => (
                            <tr key={k} className={rowIndex % 2 === 0 ? 'bg-slate-50/50' : 'bg-white'}>
                                <td className="w-1/3 px-5 py-3 font-semibold text-slate-700">{labels[k] || formatKey(k)}</td>
                                <td className="px-5 py-3 text-slate-700 break-words">{String(v)}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

function DownloadAttachment({ attachment }) {
    return (
        <div className="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <h3 className="text-sm font-bold text-slate-800 mb-3">Lampiran Berkas</h3>
            <div className="flex flex-col gap-3">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div className="min-w-0">
                        <span className="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Nama Berkas:</span>
                        <p className="text-sm font-semibold text-slate-800 break-all">{attachment.name}</p>
                    </div>
                    <div className="shrink-0">
                        <span className="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Ukuran:</span>
                        <p className="text-sm font-medium text-slate-700">{attachment.size}</p>
                    </div>
                </div>
                <a
                    href={attachment.url}
                    download={attachment.name}
                    className="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                >
                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download
                </a>
            </div>
        </div>
    );
}

function PostNavCard({ post }) {
    const thumb = post.media_type === 'video' ? youtubeThumbnailUrl(post.video_url) : post.featured_image;
    return (
        <Link
            href={postUrl(post)}
            className="group rounded-lg border border-slate-200 bg-white hover:border-primary hover:shadow-md transition-all overflow-hidden flex flex-col"
        >
            {thumb ? (
                <div className="relative aspect-video overflow-hidden">
                    <img src={thumb} alt={post.title} loading="lazy" decoding="async" className="h-full w-full object-cover" />
                    {post.media_type === 'video' && (
                        <span className="absolute inset-0 flex items-center justify-center bg-slate-900/35" aria-hidden="true">
                            <svg className="h-5 w-5 text-white drop-shadow" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z" />
                            </svg>
                        </span>
                    )}
                </div>
            ) : (
                <div className="relative aspect-video overflow-hidden bg-slate-100 flex items-center justify-center text-xs text-slate-500">Tidak ada gambar</div>
            )}
            <div className="p-3 flex flex-col gap-1 flex-1">
                {post.category && (
                    <span className="text-xs font-bold text-primary uppercase tracking-wide">{post.category.name}</span>
                )}
                <h4 className="break-words text-xs font-bold text-slate-800 transition-colors line-clamp-2 leading-snug group-hover:text-primary">
                    {post.title}
                </h4>
            </div>
        </Link>
    );
}

function SidebarPostLink({ item }) {
    const thumb = item.media_type === 'video' ? youtubeThumbnailUrl(item.video_url) : item.featured_image;
    const publishedAt = item.published_at ? new Date(item.published_at).toLocaleDateString('id-ID') : null;

    return (
        <Link href={postUrl(item)} className="flex gap-3 group">
            <div className="relative w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-slate-100">
                {thumb ? (
                    <>
                        <img src={thumb} alt={item.title} loading="lazy" decoding="async" className="w-full h-full object-cover group-hover:scale-105 transition-transform" />
                        {item.media_type === 'video' && (
                            <span className="absolute inset-0 flex items-center justify-center bg-slate-900/35" aria-hidden="true">
                                <svg className="h-5 w-5 text-white drop-shadow" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </span>
                        )}
                    </>
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-slate-500 text-xs">-</div>
                )}
            </div>
            <div className="flex flex-col justify-between h-16 min-w-0 py-0.5">
                <span className="break-words text-sm font-semibold text-slate-800 transition-colors line-clamp-2 leading-snug group-hover:text-primary">
                    {item.title}
                </span>
                <span className="text-xs text-slate-500 line-clamp-1">
                    {item.category?.name}
                    {item.category?.name && publishedAt ? ' · ' : ''}
                    {publishedAt}
                </span>
            </div>
        </Link>
    );
}

export default function PostDetail({ post, relatedPosts = [], recentPosts = [], prevPost = null, nextPost = null }) {
    const publishedDate = post.published_at
        ? new Date(post.published_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
        : null;

    const readingMinutes = Math.max(1, Math.round(plainText(post.content).split(/\s+/).filter(Boolean).length / 200));

    const blocks = blocksFromMetadata(post.metadata);

    const { settings, appUrl = '' } = usePage().props;
    const excerpt = plainText(post.content).slice(0, 160);

    const articleLd = {
        '@type': 'NewsArticle',
        headline: post.title,
        ...(excerpt ? { description: excerpt } : {}),
        ...(post.featured_image ? { image: [absoluteUrl(post.featured_image, appUrl)] } : {}),
        ...(post.published_at ? { datePublished: post.published_at } : {}),
        ...(post.updated_at ? { dateModified: post.updated_at } : {}),
        ...(post.category?.name ? { articleSection: post.category.name } : {}),
        author: { '@type': 'Person', name: post.user?.name || 'Humas SMA XYZ' },
    };

    const sidebarCta = {
        enabled: settings?.sidebar_cta_enabled !== '0',
        title: settings?.sidebar_cta_title || '',
        heading: settings?.sidebar_cta_heading || '',
        description: settings?.sidebar_cta_description || '',
        buttonText: settings?.sidebar_cta_button_text || '',
        buttonUrl: settings?.sidebar_cta_button_url || '',
    };

    return (
        <PublicLayout>
            <Seo
                title={post.title}
                description={excerpt}
                image={post.featured_image}
                type="article"
                publishedAt={post.published_at}
                modifiedAt={post.updated_at}
                jsonLd={articleLd}
            />
            <Breadcrumbs
                items={[
                    { label: 'Beranda', href: '/' },
                    ...(post.category ? [{ label: post.category.name, href: `/${post.category.slug}` }] : []),
                    { label: post.title },
                ]}
            />

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <Reveal as="article" className="lg:col-span-8 flex flex-col">
                        <header className="flex flex-col gap-4 pb-6">
                            <div className="flex flex-wrap items-center gap-2">
                                {post.category && (
                                    <p className="mt-2 text-xs font-bold uppercase tracking-[0.14em] text-secondary">{post.category.name}</p>
                                )}
                            </div>

                            <h1 className="break-words text-2xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                                {post.title}
                            </h1>

                            <div className="flex flex-wrap items-center justify-between gap-4 pt-1">
                                <div className="flex min-w-0 items-center gap-3">
                                    <div className="w-10 h-10 shrink-0 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                                        {(post.user?.name || 'H').charAt(0).toUpperCase()}
                                    </div>
                                    <div className="flex min-w-0 flex-col">
                                        <span className="break-words text-sm font-bold text-slate-800">{post.user?.name || 'Humas SMA XYZ'}</span>
                                        <span className="text-xs text-slate-500">Tim Media &amp; Publikasi</span>
                                    </div>
                                </div>

                                <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                    {publishedDate && (
                                        <span className="flex items-center gap-1">
                                            <svg className="w-3.5 h-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                            {publishedDate}
                                        </span>
                                    )}
                                    <span className="flex items-center gap-1">
                                        <svg className="w-3.5 h-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                        {readingMinutes} Menit Baca
                                    </span>
                                </div>
                            </div>
                        </header>

                        <PostMedia post={post} />

                        <RichContent
                            html={post.content}
                            className="text-slate-700 leading-relaxed text-base"
                        />

                        {blocks.map((block, idx) => {
                            if (block.type === 'text') {
                                return (
                                    <RichContent
                                        key={idx}
                                        html={block.content}
                                        className="mt-6 text-slate-700 leading-relaxed text-base"
                                    />
                                );
                            }
                            if (block.type === 'table') {
                                return (
                                    <DataTable
                                        key={idx}
                                        title={block.title}
                                        description={block.description}
                                        columns={block.columns}
                                        rows={block.rows}
                                    />
                                );
                            }
                            if (block.type === 'callout') {
                                return (
                                    <aside
                                        key={idx}
                                        className="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900"
                                    >
                                        <RichContent html={block.content} className="text-sm leading-relaxed" />
                                    </aside>
                                );
                            }
                            if (block.type === 'blockquote') {
                                return (
                                    <blockquote
                                        key={idx}
                                        className="mt-6 border-l-4 border-primary/40 pl-4 text-base italic text-slate-600 leading-relaxed"
                                    >
                                        <RichContent html={block.content} />
                                        {block.source && (
                                            <cite className="mt-2 block text-sm not-italic text-slate-500">— {block.source}</cite>
                                        )}
                                    </blockquote>
                                );
                            }
                            return null;
                        })}

                        {post.metadata?.attachments?.[0] && (
                            <DownloadAttachment attachment={post.metadata.attachments[0]} />
                        )}

                        <MetadataTable metadata={post.metadata} schema={post.category?.metadata_schema} categoryName={post.category?.name} />

                        <div className="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-50 rounded-2xl p-4">
                            <div className="flex flex-wrap items-center gap-2">
                                <span className="text-xs font-bold text-slate-600">Bagikan:</span>
                                {[['WhatsApp', '#'], ['Facebook', '#'], ['Salin Tautan', '#']].map(([label, href]) => (
                                    <a
                                        key={label}
                                        href={href}
                                        className="px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-primary hover:text-white hover:border-primary transition-colors text-slate-700 text-xs font-medium shadow-sm"
                                    >
                                        {label}
                                    </a>
                                ))}
                            </div>
                            {post.category && (
                                <span className="text-xs text-slate-500">#{post.category.name?.replace(/\s/g, '')}</span>
                            )}
                        </div>

                        {(prevPost || nextPost) && (
                            <div className="mt-10 grid grid-cols-2 gap-4">
                                {prevPost ? (
                                    <div className="flex flex-col gap-2">
                                        <span className="text-xs font-bold uppercase tracking-wide text-slate-500">← Sebelumnya</span>
                                        <PostNavCard post={prevPost} />
                                    </div>
                                ) : (
                                    <div />
                                )}
                                {nextPost && (
                                    <div className="flex flex-col gap-2">
                                        <span className="text-xs font-bold uppercase tracking-wide text-slate-500 text-right">Selanjutnya →</span>
                                        <PostNavCard post={nextPost} />
                                    </div>
                                )}
                            </div>
                        )}
                    </Reveal>

                    <Reveal as="aside" delay={0.1} className="lg:col-span-4 flex flex-col gap-6 lg:sticky lg:top-28">
                        
                        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col gap-4 p-5">
                            <span className="font-bold text-slate-800 text-xl">Cari Informasi</span>
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    const input = e.target.elements.search;
                                    if (input.value.trim()) {
                                        router.get('/cari', { q: input.value.trim() });
                                    }
                                }}
                                className="flex gap-2"
                            >
                                <input
                                    type="text"
                                    name="search"
                                    placeholder="Ketik kata kunci..."
                                    className="flex-1 rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary"
                                />
                                <button
                                    type="submit"
                                    className="px-4 py-2 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-colors"
                                >
                                    Cari
                                </button>
                            </form>
                        </div>

                        {recentPosts.length > 0 && (
                            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col gap-4 p-5">
                                <div className="flex items-center justify-between">
                                    <span className="font-bold text-slate-800 text-xl">Postingan Terbaru</span>
                                    <Link href="/berita" className="text-sm font-bold text-primary hover:underline">Lihat Semua</Link>
                                </div>
                                <div className="flex flex-col gap-4">
                                    {recentPosts.map((recent) => (
                                        <SidebarPostLink key={recent.id} item={recent} />
                                    ))}
                                </div>
                            </div>
                        )}

                        {relatedPosts.length > 0 && (
                            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col gap-4 p-5">
                                <div className="flex items-center justify-between">
                                    <span className="font-bold text-slate-800 text-xl">{post.category.name} Terkait</span>
                                    <Link href="/berita" className="text-sm font-bold text-primary hover:underline">Lihat Semua</Link>
                                </div>
                                <div className="flex flex-col gap-4">
                                    {relatedPosts.map((rel) => (
                                        <SidebarPostLink key={rel.id} item={rel} />
                                    ))}
                                </div>
                            </div>
                        )}

                        {sidebarCta.enabled && sidebarCta.heading && (
                            <div className="rounded-2xl bg-primary text-white shadow-md flex flex-col gap-3 p-5 relative overflow-hidden">
                                <div className="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-white/10 pointer-events-none" />
                                {sidebarCta.title && (
                                    <span className="text-xs font-bold text-emerald-200 uppercase tracking-wide">{sidebarCta.title}</span>
                                )}
                                <h4 className="font-bold text-lg leading-tight">{sidebarCta.heading}</h4>
                                {sidebarCta.description && (
                                    <p className="text-sm text-white/80">{sidebarCta.description}</p>
                                )}
                                {sidebarCta.buttonUrl && sidebarCta.buttonText && (
                                    <a
                                        href={sidebarCta.buttonUrl}
                                        className="mt-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-primary font-bold text-sm hover:bg-slate-100 transition-colors shadow-sm"
                                    >
                                        {sidebarCta.buttonText}
                                        <span aria-hidden="true">→</span>
                                    </a>
                                )}
                            </div>
                        )}
                    </Reveal>
                </div>
            </div>
        </PublicLayout>
    );
}
