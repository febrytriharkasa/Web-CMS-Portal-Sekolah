import { useEffect, useRef, useState, lazy, Suspense } from 'react';
import PublicLayout from '@/Layouts/PublicLayout';
import Card from '@/Components/Card';
import { Link, usePage } from '@inertiajs/react';
import Seo, { absoluteUrl } from '@/Components/Seo';
import { youtubeThumbnailUrl } from '@/lib/postBlocks';
import { postUrl } from '@/lib/postUrl';
import { imageSrcSet } from '@/lib/imageSrcSet';
import { Reveal, StaggerContainer } from '@/Components/Reveal';

const MediaPreviewModal = lazy(() => import('@/Components/MediaPreviewModal'));

function ArrowLink({ href, children, dark = false }) {
    return <Link href={href} className={`inline-flex min-h-9 items-center gap-1.5 rounded-lg text-xs font-bold transition hover:gap-2.5 focus-visible:outline-none focus-visible:ring-2 sm:min-h-11 sm:gap-2 sm:text-sm sm:hover:gap-3 ${dark ? 'text-emerald-200 hover:text-white focus-visible:ring-white' : 'text-primary focus-visible:ring-primary'}`}>{children}<span aria-hidden="true">→</span></Link>;
}

function SectionHeading({ eyebrow, title, href, linkLabel = 'Lihat semua', dark = false }) {
    return (
        <Reveal>
            <div className="mb-8 flex items-end justify-between gap-4">
                <div className="flex-1">
                    <h2 className={`mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl ${dark ? 'text-white' : 'text-slate-900'}`}>{title}</h2>
                    {eyebrow && <p className={`mt-2 text-xs font-bold uppercase tracking-[0.14em] ${dark ? 'text-emerald-200' : 'text-secondary'}`}>{eyebrow}</p>}
                </div>
                {href && (
                    <>
                        <Link href={href} aria-label={`Lihat semua ${title}`} className="sm:hidden inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-lg border border-slate-300 bg-white text-lg font-bold text-slate-600 hover:border-primary hover:text-primary transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                            <span aria-hidden="true">→</span>
                        </Link>
                        <span className="hidden sm:inline-flex">
                            <ArrowLink href={href} dark={dark}>{linkLabel}</ArrowLink>
                        </span>
                    </>
                )}
            </div>
        </Reveal>
    );
}

function EmptyState({ children }) {
    return <div className="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center text-sm text-slate-600">{children}</div>;
}

export function HorizontalRail({ label, children }) {
    const railRef = useRef(null);

    const scrollRail = (direction) => {
        const rail = railRef.current;
        if (rail) {
            rail.scrollBy({ left: direction * rail.clientWidth, behavior: 'smooth' });
        }
    };

    return (
        <div>
            <div
                ref={railRef}
                role="region"
                aria-label={label}
                tabIndex={0}
                className="flex snap-x snap-mandatory gap-6 overflow-x-auto overflow-y-hidden pb-2 rounded-xl [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
                {children}
            </div>
            <div className="mt-4 flex justify-end gap-2">
                <button
                    type="button"
                    onClick={() => scrollRail(-1)}
                    aria-label={`Geser ${label} ke kiri`}
                    className="grid h-11 w-11 place-items-center rounded-lg border border-slate-300 bg-white text-lg font-bold text-slate-700 transition hover:border-primary hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                >
                    ‹
                </button>
                <button
                    type="button"
                    onClick={() => scrollRail(1)}
                    aria-label={`Geser ${label} ke kanan`}
                    className="grid h-11 w-11 place-items-center rounded-lg border border-slate-300 bg-white text-lg font-bold text-slate-700 transition hover:border-primary hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                >
                    ›
                </button>
            </div>
        </div>
    );
}

function formatPostDate(post) {
    const value = post.metadata?.event_date || post.metadata?.tanggal_agenda || post.published_at;
    return value ? new Date(value).toLocaleDateString('id-ID') : '-';
}

function PostCard({ post }) {
    const thumbnail = post.media_type === 'video' ? youtubeThumbnailUrl(post.video_url) : post.featured_image;

    return (
        <Card hover className="flex flex-col overflow-hidden h-full">
                <Link href={postUrl(post)} className="block">
                    <div className="relative aspect-[16/9] sm:aspect-[16/10] overflow-hidden bg-slate-100 shrink-0">
                        {thumbnail ? (
                            <div className="relative h-full w-full">
                                <img src={thumbnail} alt={post.title} loading="lazy" decoding="async" className="h-full w-full object-cover" />
                                {post.media_type === 'video' && (
                                    <span className="absolute inset-0 flex items-center justify-center bg-slate-900/35" aria-hidden="true">
                                        <svg className="h-9 w-9 text-white drop-shadow" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </span>
                                )}
                            </div>
                        ) : (
                            <div className="flex h-full w-full items-center justify-center text-xs text-slate-500 font-medium">Gambar tidak tersedia</div>
                        )}
                    </div>
                </Link>
                <div className="flex flex-1 flex-col justify-between p-3.5 sm:p-5">
                    <div>
                        <div className="flex min-h-8 flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-600">
                            <span className="font-bold text-primary">{post.category?.name || 'Konten'}</span>
                            <span>{formatPostDate(post)}</span>
                        </div>
                        <h3 className="mt-1.5 min-h-10 line-clamp-2 break-words text-sm font-bold text-slate-900 sm:mt-3 sm:min-h-14 sm:text-lg">
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
    );
}

function PostSection({ id, eyebrow, title, href, posts, tone = 'plain', previewMode = 'standard', onPreview }) {
    return (
        <section id={id} className={tone === 'muted' ? 'bg-slate-50 py-12 sm:py-20' : 'bg-white py-12 sm:py-20'}>
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeading eyebrow={eyebrow} title={title} href={href} />
                {posts.length ? (
                    <StaggerContainer>
                    <HorizontalRail label={title}>
                        {posts.slice(0, 8).map((post, idx) => (
                            previewMode === 'media' ? (
                                <div
                                    key={post.id}
                                    className="stagger-item w-[calc((100%-1.5rem)/2)] shrink-0 snap-start sm:w-[calc((100%-1.5rem)/2)] lg:w-[calc((100%-3rem)/3)] xl:w-[calc((100%-4.5rem)/4)]"
                                >
                                    <Card className="overflow-hidden flex flex-col h-full bg-white group">
                                        <button
                                            type="button"
                                            onClick={() => onPreview(post)}
                                            className="relative aspect-[16/9] sm:aspect-[16/10] w-full bg-slate-100 overflow-hidden text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary cursor-zoom-in"
                                            aria-label={`Lihat gambar penuh: ${post.title}`}
                                        >
                                            {(() => {
                                                const thumb = post.media_type === 'video' ? youtubeThumbnailUrl(post.video_url) : post.featured_image;
                                                return thumb ? (
                                                    <div className="relative h-full w-full">
                                                        <img
                                                            src={thumb}
                                                            alt={post.title}
                                                            loading="lazy" decoding="async"
                                                            className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                        />
                                                        {post.media_type === 'video' ? (
                                                            <span className="absolute inset-0 flex items-center justify-center bg-slate-900/35" aria-hidden="true">
                                                                <svg className="h-9 w-9 text-white drop-shadow" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                                            </span>
                                                        ) : (
                                                            <span className="absolute bottom-2 right-2 rounded-md bg-slate-900/60 p-1.5 text-white opacity-0 group-hover:opacity-100 transition-opacity" aria-hidden="true">
                                                                <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16zm-3-8h6m-3-3v6" />
                                                                </svg>
                                                            </span>
                                                        )}
                                                    </div>
                                                ) : (
                                                    <div className="flex h-full w-full items-center justify-center text-xs text-slate-500 font-medium">Gambar tidak tersedia</div>
                                                );
                                            })()}
                                        </button>
                                        <div className="flex flex-1 items-center bg-white p-3">
                                            <Link
                                                href={postUrl(post)}
                                                className="line-clamp-2 break-words text-xs font-bold text-slate-800 transition-colors hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded sm:text-sm"
                                                title={`Buka detail: ${post.title}`}
                                            >
                                                {post.title}
                                            </Link>
                                        </div>
                                    </Card>
                                </div>
                            ) : (
                                <div key={post.id} className="stagger-item w-[calc((100%-1.5rem)/2)] shrink-0 snap-start sm:w-[calc((100%-1.5rem)/2)] lg:w-[calc((100%-3rem)/3)] xl:w-[calc((100%-4.5rem)/4)]">
                                    <PostCard post={post} />
                                </div>
                            )
                        ))}
                        </HorizontalRail>
                    </StaggerContainer>
                ) : <EmptyState>Belum ada konten pada kategori ini.</EmptyState>}
            </div>
        </section>
    );
}

function AlumniSection({ id, title, href, posts }) {
    return (
        <section id={id} className="bg-primary py-16 text-white sm:py-20">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <SectionHeading eyebrow="Kisah alumni" title={title} href={href} dark />
                <div>
                    {posts.length ? (
                    <StaggerContainer>
                        <HorizontalRail label={title}>
                        {posts.slice(0, 8).map((testi) => (
                            <div
                                key={testi.id}
                                className="stagger-item w-[calc((100%-1.5rem)/2)] shrink-0 snap-start sm:w-[calc((100%-1.5rem)/2)] lg:w-[calc((100%-3rem)/3)] xl:w-[calc((100%-4.5rem)/4)]"
                            >
                                <Card hover className="bg-white p-4 h-full sm:p-6">
                                    <p className="break-words text-xs italic leading-6 text-slate-600 sm:text-sm sm:leading-7">“{testi.content}”</p>
                                    <p className="mt-3 break-words text-sm font-bold text-slate-900 sm:mt-5 sm:text-base">{testi.title}</p>
                                    <p className="mt-1 break-words text-xs text-slate-600">Alumni {testi.metadata?.graduation_year || '-'} · {testi.metadata?.current_profession || 'Profesi belum diisi'}</p>
                                </Card>
                            </div>
                        ))}
                        </HorizontalRail>
                    </StaggerContainer>
                    ) : <EmptyState>Belum ada cerita alumni.</EmptyState>}
                </div>
            </div>
        </section>
    );
}

export default function Welcome({ settings = {}, posts = [], homeSections = [], galleries = [], staff = [] }) {
    const { appUrl = '' } = usePage().props;
    const [previewItem, setPreviewItem] = useState(null);
    const toneFor = (index) => (index % 2 === 0 ? 'muted' : 'plain');
    const slides = [
        // Teks slide 1 diduplikasi sebagai hero statis di
        // resources/views/app.blade.php; jaga fallback-nya tetap sama.
        {
            tag: settings.hero_slide_1_tag || 'Portal resmi SMA XYZ',
            title: settings.hero_slide_1_title || settings.hero_title || 'Membentuk Generasi Rabbani & Berprestasi',
            btnText: settings.hero_slide_1_btn_text || 'Sambutan Kepala Sekolah',
            btnUrl: settings.hero_slide_1_btn_url || '#sambutan',
            image: settings.hero_slide_1_image || settings.hero_background_image || null,
        },
        {
            tag: settings.hero_slide_2_tag || 'Penerimaan Siswa Baru',
            title: settings.hero_slide_2_title || 'Pendaftaran Peserta Didik Baru',
            btnText: settings.hero_slide_2_btn_text || 'Daftar Sekarang',
            btnUrl: settings.hero_slide_2_btn_url || settings.ppdb_link || 'https://ppdb.smaxyz.sch.id',
            image: settings.hero_slide_2_image || null,
        },
    ];

    const [currentSlide, setCurrentSlide] = useState(0);
    const [previousImage, setPreviousImage] = useState(null);
    const lastSlideRef = useRef(0);

    // Rotasi pertama ditahan 10 detik. Setiap pergantian foto memunculkan
    // kandidat LCP baru di mata browser (foto slide 2 baru dilukis saat itu),
    // sehingga LCP halaman ikut terdorong ke detik ke-6 walau foto pertama
    // sudah terlihat sejak HTML pertama. Menahan rotasi sedikit juga memberi
    // pembaca waktu menyelesaikan judul hero sebelum teksnya berganti.
    useEffect(() => {
        let interval;

        const start = setTimeout(() => {
            interval = setInterval(() => {
                setCurrentSlide((prev) => (prev + 1) % slides.length);
            }, 6000);
        }, 10000);

        return () => {
            clearTimeout(start);
            clearInterval(interval);
        };
    }, [slides.length]);

    // Keep the outgoing image for one crossfade cycle; CSS handles both fades.
    useEffect(() => {
        const last = lastSlideRef.current;
        lastSlideRef.current = currentSlide;

        if (last === currentSlide) return undefined;

        const previous = slides[last]?.image;
        if (!previous || previous === slides[currentSlide]?.image) return undefined;

        setPreviousImage(previous);
        const timer = setTimeout(() => setPreviousImage(null), 760);
        return () => clearTimeout(timer);
    }, [currentSlide, slides.map((slide) => slide.image).join('|')]);

    // Panaskan foto slide berikutnya setelah LCP selesai. Varian responsif ikut
    // diminta lewat srcset supaya browser tidak mengunduh file 1920px penuh.
    useEffect(() => {
        const next = slides[(currentSlide + 1) % slides.length];
        if (!next?.image) return undefined;

        const warm = () => {
            const img = new window.Image();
            const srcset = imageSrcSet(next.image);

            if (srcset) {
                img.srcset = srcset;
                img.sizes = '100vw';
            }

            img.src = next.image;
        };

        const id = setTimeout(warm, 6000);
        return () => clearTimeout(id);
    }, [currentSlide, slides.map((slide) => slide.image).join('|')]);

    const activeSlide = slides[currentSlide];
    const galeriIndex = homeSections.length + 1;
    const gtkIndex = homeSections.length + 2;

    const organizationLd = {
        '@type': 'EducationalOrganization',
        name: settings.site_name || 'SMA XYZ',
        ...(appUrl ? { url: appUrl } : {}),
        ...(settings.site_logo ? { logo: absoluteUrl(settings.site_logo, appUrl) } : {}),
        ...(settings.footer_about || settings.site_tagline
            ? { description: settings.footer_about || settings.site_tagline }
            : {}),
        ...(settings.contact_phone ? { telephone: settings.contact_phone } : {}),
        ...(settings.contact_email ? { email: settings.contact_email } : {}),
        ...(settings.contact_address
            ? { address: { '@type': 'PostalAddress', streetAddress: settings.contact_address } }
            : {}),
        sameAs: [
            settings.social_instagram,
            settings.social_facebook,
            settings.social_youtube,
            settings.social_tiktok,
            settings.social_twitter,
            settings.social_linkedin,
        ].filter((url) => typeof url === 'string' && url.startsWith('http')),
    };

    return (
        <PublicLayout settings={settings}>
            <Seo
                description={settings.footer_about || settings.site_tagline}
                image={slides[0].image}
                jsonLd={organizationLd}
            />

            {/* Hero section dibuat lebih compact di desktop, tetap proporsional di mobile */}
            <section className="relative isolate overflow-hidden bg-slate-900 text-white flex items-center w-full min-h-[19rem] aspect-[4/3] sm:aspect-[16/7] sm:py-10 lg:py-16 sm:min-h-[24rem] lg:min-h-[30rem]">
                
                {/* Gambar slide: lapisan lama memudar keluar, lapisan baru masuk */}
                {previousImage && (
                    <img
                        key={`previous-${previousImage}`}
                        src={previousImage}
                        srcSet={imageSrcSet(previousImage)}
                        sizes="100vw"
                        alt=""
                        aria-hidden="true"
                        width={1920}
                        height={1080}
                        loading="eager"
                        decoding="async"
                        className="hero-image-out absolute inset-0 -z-20 h-full w-full object-cover object-center"
                    />
                )}
                {activeSlide.image && (
                    <img
                        key={activeSlide.image}
                        src={activeSlide.image}
                        srcSet={imageSrcSet(activeSlide.image)}
                        sizes="100vw"
                        alt={activeSlide.title}
                        width={1920}
                        height={1080}
                        loading="eager"
                        fetchpriority={currentSlide === 0 ? 'high' : 'auto'}
                        decoding="async"
                        className="hero-image-in absolute inset-0 -z-20 h-full w-full object-cover object-center"
                    />
                )}

                {/* Overlay gelap / primary */}
                <div className={`absolute inset-0 -z-10 transition-colors duration-1000 ${activeSlide.image ? 'bg-slate-950/70' : 'bg-primary'}`} />

                {/* Konten teks dengan animasi berbeda per elemen */}
                <div className="mx-auto grid w-full max-w-7xl items-center gap-4 px-4 sm:px-6 lg:grid-cols-[minmax(0,1.25fr)_minmax(18rem,0.75fr)] relative z-10">
                    <div className="max-w-3xl flex items-center">
                        <div key={currentSlide} className="w-full">
                            <p className="hero-copy-tag mb-1.5 sm:mb-3 text-xs font-bold uppercase tracking-[0.18em] text-emerald-200">
                                {activeSlide.tag}
                            </p>
                            <h1 className="hero-copy-title max-w-3xl break-words text-[clamp(1.75rem,7vw,2rem)] font-extrabold leading-[1.2] tracking-[-0.015em] sm:text-4xl md:text-5xl lg:text-6xl">
                                {activeSlide.title}
                            </h1>
                            <p className="hero-copy-sub mt-2 sm:mt-4 max-w-2xl break-words text-[13px] leading-snug text-emerald-50/90 sm:text-lg sm:leading-6">
                                {activeSlide.subtitle}
                            </p>
                            <div className="hero-copy-cta mt-4 sm:mt-6 flex flex-wrap gap-3 items-center">
                                <a
                                    href={activeSlide.btnUrl}
                                    target={activeSlide.btnUrl.startsWith('/') ? "_self" : "_blank"}
                                    rel={activeSlide.btnUrl.startsWith('/') ? "" : "noopener noreferrer"}
                                    className="inline-flex min-h-9 sm:min-h-12 items-center justify-center rounded-lg bg-emerald-200 px-4 py-2 sm:px-6 sm:py-3 text-xs sm:text-sm font-bold text-emerald-950 transition hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary shadow-lg"
                                >
                                    {activeSlide.btnText}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Carousel Dots - Vertikal, sejajar vertikal dengan judul (tengah hero) */}
                <div className="absolute right-3 sm:right-8 top-1/2 -translate-y-1/2 z-20">
                    <div className="flex flex-col items-center gap-0.5 rounded-full border border-white/10 bg-black/30 px-1.5 py-2 sm:px-2 sm:py-3">
                        {slides.map((_, idx) => (
                            <button
                                key={idx}
                                type="button"
                                onClick={() => setCurrentSlide(idx)}
                                aria-label={`Tampilkan slide ${idx + 1}`}
                                className="group grid h-6 w-6 place-items-center rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
                            >
                                <span
                                    aria-hidden="true"
                                    className={`block rounded-full transition-all ${
                                        idx === currentSlide
                                            ? 'h-4 w-1.5 bg-emerald-300 sm:h-6 sm:w-2'
                                            : 'h-1.5 w-1.5 bg-white/40 group-hover:bg-white/70 sm:h-2 sm:w-2'
                                    }`}
                                />
                            </button>
                        ))}
                    </div>
                </div>

            </section>

            <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-14 relative z-20">
                <Reveal delay={0.2}>
                <div className="bg-white/90 backdrop-blur-sm rounded-2xl p-6 shadow-card">
                    <h2 className="sr-only">Layanan pintas</h2>
                    <StaggerContainer className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {[{ title: settings.quick_link_1_title || 'Pendaftaran', url: settings.quick_link_1_url || 'https://ppdb.smaxyz.sch.id', desc: settings.quick_link_1_desc || 'Portal Resmi Pendaftaran PPDB Online' },
                        { title: settings.quick_link_2_title || 'SMA XYZ via mobile', url: settings.quick_link_2_url || 'https://mobile.smaxyz.or.id', desc: settings.quick_link_2_desc || 'Aplikasi Layanan Mobile SMA XYZ' },
                        { title: settings.quick_link_3_title || 'Perpustakaan SMA XYZ', url: settings.quick_link_3_url || 'https://perpus.smaxyz.or.id', desc: settings.quick_link_3_desc || 'Layanan Perpustakaan Digital' },
                    ].map((link, idx) => (
                        <div key={idx} className="stagger-item">
                            <a href={link.url} target="_blank" rel="noopener noreferrer" className="block group h-full">
                                <Card hover className="p-4 h-full flex flex-col justify-between border-slate-200/80 transition bg-surface-container-low sm:p-6">
                                    <div>
                                        <span className="text-xs font-bold uppercase tracking-wider text-primary">Layanan Pintas</span>
                                        <h3 className="mt-2 break-words text-lg font-extrabold text-slate-900 transition group-hover:text-primary sm:text-xl">{link.title}</h3>
                                        <p className="mt-2 break-words text-sm text-slate-600">{link.desc}</p>
                                    </div>
                                    <div className="mt-4 flex items-center text-sm font-bold text-primary gap-1 group-hover:gap-2 transition-all">
                                        <span>Akses Layanan</span>
                                        <span aria-hidden="true">→</span>
                                    </div>
                                </Card>
                            </a>
                        </div>
                     ))}
                    </StaggerContainer>
                </div>
                </Reveal>
            </section>

            {/* Berita is permanent: it always leads the home page. */}
            <PostSection
                id="berita"
                eyebrow="Update terkini seputar berita"
                title="Berita Sekolah"
                href="/berita"
                posts={posts}
                tone={toneFor(0)}
            />

            {/* Sections the editor enabled per category, in the order set in Kategori. */}
            {homeSections.map((section, idx) => (
                section.slug === 'cerita-alumni' ? (
                    <AlumniSection
                        key={section.id}
                        id={section.slug}
                        title={section.name}
                        href={`/${section.slug}`}
                        posts={section.posts}
                    />
                ) : (
                    <PostSection
                        key={section.id}
                        eyebrow={`Update terkini ${section.name}`}
                        id={section.slug}
                        title={section.name}
                        href={`/${section.slug}`}
                        posts={section.posts}
                        tone={toneFor(idx + 1)}
                        previewMode={section.preview_mode}
                        onPreview={setPreviewItem}
                    />
                )
            ))}

            {/* Galeri aktivitas and Guru & Tendik are permanent, after the adjustable sections. */}
            <section id="galeri" className={toneFor(galeriIndex) === 'muted' ? 'bg-slate-50 py-12 sm:py-20' : 'bg-white py-12 sm:py-20'}>
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <SectionHeading eyebrow="Lihat kegiatan kami ketika di sekolah" title="Galeri Aktivitas" href="/galeri" />
                    {galleries.length ? (
                        <StaggerContainer>
                        <HorizontalRail label="Galeri Aktivitas">
                            {galleries.slice(0, 8).map((item) => {
                                const thumb = item.media_type === 'video' ? youtubeThumbnailUrl(item.video_url) : item.featured_image;
                                return (
                                <div
                                    key={item.id}
                                    className="stagger-item w-[calc((100%-1.5rem)/2)] shrink-0 snap-start sm:w-[calc((100%-1.5rem)/2)] lg:w-[calc((100%-3rem)/3)] xl:w-[calc((100%-4.5rem)/4)]"
                                >
                                    <Card className="overflow-hidden flex flex-col h-full bg-white group">
                                        <button
                                            type="button"
                                            onClick={() => setPreviewItem(item)}
                                            className="relative aspect-[16/9] sm:aspect-[16/10] w-full bg-slate-100 overflow-hidden text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary cursor-zoom-in"
                                            aria-label={`Lihat gambar penuh: ${item.title}`}
                                        >
                                            {thumb ? (
                                                <div className="relative h-full w-full">
                                                    <img
                                                        src={thumb}
                                                        alt={item.title}
                                                        loading="lazy" decoding="async"
                                                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                    />
                                                    {item.media_type === 'video' ? (
                                                        <span className="absolute inset-0 flex items-center justify-center bg-slate-900/35" aria-hidden="true">
                                                            <svg className="h-9 w-9 text-white drop-shadow" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                                        </span>
                                                    ) : (
                                                        <span className="absolute bottom-2 right-2 rounded-md bg-slate-900/60 p-1.5 text-white opacity-0 group-hover:opacity-100 transition-opacity" aria-hidden="true">
                                                            <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                                                                <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16zm-3-8h6m-3-3v6" />
                                                            </svg>
                                                        </span>
                                                    )}
                                                </div>
                                            ) : (
                                                <div className="flex h-full w-full items-center justify-center text-xs text-slate-500 font-medium">Gambar tidak tersedia</div>
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
                        </HorizontalRail>
                    </StaggerContainer>
                    ) : <EmptyState>Belum ada dokumentasi galeri.</EmptyState>}
                </div>
            </section>

            <section id="gtk" className={toneFor(gtkIndex) === 'muted' ? 'bg-slate-50 py-12 sm:py-20' : 'bg-white py-12 sm:py-20'}>
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <SectionHeading eyebrow="Tenaga pendidik profesional" title="Guru dan tenaga kependidikan" href="/guru-tendik" />
                    {staff.length ? (
                        <StaggerContainer>
                        <HorizontalRail label="Guru dan tenaga kependidikan">
                            {staff.slice(0, 8).map((person) => (
                                <div
                                    key={person.id}
                                    className="stagger-item w-[calc((100%-1.5rem)/2)] shrink-0 snap-start sm:w-[calc((100%-1.5rem)/2)] lg:w-[calc((100%-3rem)/3)] xl:w-[calc((100%-4.5rem)/4)]"
                                >
                                    <Card hover className="group p-3 text-center h-full sm:p-4">
                                        <Link
                                            href={`/guru-tendik/${person.id}`}
                                            className="block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-lg"
                                        >
                                            {person.photo ? (
                                                <img
                                                    src={person.photo}
                                                    alt={`Foto ${person.name}`}
                                                    loading="lazy"
                                                    decoding="async"
                                                    className="mx-auto mb-2 h-12 w-12 rounded-full border-2 border-primary/20 object-cover transition-transform duration-300 group-hover:scale-105 sm:mb-3 sm:h-20 sm:w-20"
                                                />
                                            ) : (
                                                <div className="mx-auto mb-2 grid h-12 w-12 place-items-center rounded-full bg-primary/10 text-xs font-bold text-primary transition-transform duration-300 group-hover:scale-105 sm:mb-3 sm:h-20 sm:w-20 sm:text-sm">
                                                    {person.name.charAt(0)}
                                                </div>
                                            )}
                                            <h3 className="break-words text-xs font-bold text-slate-900 transition-colors group-hover:text-primary sm:text-sm">{person.name}</h3>
                                        </Link>
                                        <p className="mt-0.5 break-words text-xs leading-4 text-slate-600 sm:mt-1 sm:leading-5">{person.role}</p>
                                    </Card>
                                </div>
                            ))}
                        </HorizontalRail>
                    </StaggerContainer>
                    ) : <EmptyState>Belum ada data Guru dan Tendik.</EmptyState>}
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
