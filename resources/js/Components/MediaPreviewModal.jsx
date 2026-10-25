import { useEffect } from 'react';
import { createPortal } from 'react-dom';
import { AnimatePresence, motion } from 'framer-motion';
import { youtubeThumbnailUrl } from '@/lib/postBlocks';

function youtubeEmbedUrl(url) {
    if (!url) return null;
    const match = url.match(
        /(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/,
    );
    return match ? `https://www.youtube.com/embed/${match[1]}?autoplay=1` : null;
}

export default function MediaPreviewModal({
    item = null,
    items = null,
    index = 0,
    onClose,
    onIndexChange = null,
}) {
    const list = items?.length ? items : item ? [item] : [];
    const activeIndex = list.length ? ((index % list.length) + list.length) % list.length : 0;
    const current = list[activeIndex] ?? null;
    const canNavigate = list.length > 1 && typeof onIndexChange === 'function';

    useEffect(() => {
        if (!current) return;

        const onKeyDown = (e) => {
            if (e.key === 'Escape') {
                onClose();
                return;
            }

            if (!canNavigate) return;

            if (e.key === 'ArrowRight') {
                onIndexChange((activeIndex + 1) % list.length);
            } else if (e.key === 'ArrowLeft') {
                onIndexChange((activeIndex - 1 + list.length) % list.length);
            }
        };

        window.addEventListener('keydown', onKeyDown);
        const originalOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        return () => {
            window.removeEventListener('keydown', onKeyDown);
            document.body.style.overflow = originalOverflow;
        };
    }, [current, onClose, canNavigate, activeIndex, list.length, onIndexChange]);

    const isVideo = current?.media_type === 'video' && Boolean(current?.video_url);
    const videoEmbed = isVideo ? youtubeEmbedUrl(current.video_url) : null;
    const imageSrc = current?.featured_image || (!isVideo && current?.video_url ? youtubeThumbnailUrl(current.video_url) : null);

    const goTo = (offset) => {
        if (!canNavigate) return;
        onIndexChange((activeIndex + offset + list.length) % list.length);
    };

    // Modal harus lepas dari PageEnter: transform pada ancestor membuat
    // `fixed` mengacu ke kotak halaman, bukan viewport.
    if (typeof document === 'undefined') {
        return null;
    }

    return createPortal(
        <AnimatePresence>
            {current && (
                <motion.div
                    key="media-preview"
                    className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 md:p-10"
                    role="dialog"
                    aria-modal="true"
                    aria-label={`Preview ${current.title}`}
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.2 }}
                >
                    {/* Backdrop */}
                    <div
                        onClick={onClose}
                        className="absolute inset-0 bg-slate-950/85 backdrop-blur-sm cursor-zoom-out"
                    />

                    {/* Modal Container */}
                    <motion.div
                        className="relative z-10 flex max-h-full max-w-5xl w-full flex-col overflow-hidden rounded-2xl bg-slate-900 text-white shadow-2xl border border-white/10"
                        initial={{ opacity: 0, scale: 0.94, y: 24 }}
                        animate={{ opacity: 1, scale: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.94, y: 24 }}
                        transition={{ type: 'spring', stiffness: 340, damping: 28 }}
                    >
                    {/* Header */}
                    <div className="flex items-center justify-between gap-4 border-b border-white/10 px-5 py-3">
                        <p className="line-clamp-1 text-sm font-semibold text-white/90">
                            {current.title}
                        </p>
                        <div className="flex shrink-0 items-center gap-2">
                            {canNavigate && (
                                <span className="text-xs font-semibold tabular-nums text-white/60" aria-live="polite">
                                    {activeIndex + 1} / {list.length}
                                </span>
                            )}
                            <button
                                type="button"
                                onClick={onClose}
                                className="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white/70 hover:bg-white/10 hover:text-white transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                                aria-label="Tutup preview"
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    {/* Media Display */}
                    <div className="relative flex items-center justify-center bg-black/50 p-2 sm:p-4 max-h-[75vh] overflow-hidden">
                        {isVideo && videoEmbed ? (
                            <div className="aspect-video w-full max-w-4xl overflow-hidden rounded-lg shadow-lg">
                                <iframe
                                    src={videoEmbed}
                                    title={current.title}
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowFullScreen
                                    className="h-full w-full border-0"
                                />
                            </div>
                        ) : imageSrc ? (
                            <img
                                src={imageSrc}
                                alt={current.title}
                                className="max-h-[70vh] w-auto max-w-full rounded-lg object-contain shadow-lg"
                            />
                        ) : (
                            <div className="py-20 text-sm text-slate-400">Media tidak tersedia</div>
                        )}

                        {canNavigate && (
                            <>
                                <button
                                    type="button"
                                    onClick={() => goTo(-1)}
                                    aria-label="Media sebelumnya"
                                    className="absolute left-2 top-1/2 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-slate-950/60 text-white/80 transition hover:bg-slate-950 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white sm:left-4"
                                >
                                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" aria-hidden="true">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    onClick={() => goTo(1)}
                                    aria-label="Media berikutnya"
                                    className="absolute right-2 top-1/2 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-slate-950/60 text-white/80 transition hover:bg-slate-950 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white sm:right-4"
                                >
                                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2" aria-hidden="true">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </>
                        )}
                    </div>
                </motion.div>
            </motion.div>
        )}
        </AnimatePresence>,
        document.body,
    );
}