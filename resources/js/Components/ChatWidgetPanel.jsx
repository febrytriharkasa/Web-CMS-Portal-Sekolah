import { useEffect, useRef, useState } from 'react';
import { motion, useReducedMotion } from 'framer-motion';
import { Link } from '@inertiajs/react';

const SESSION_KEY = 'chat_session_token';

// Balasan asisten memuat tautan halaman apa adanya (mis. /berita/judul-berita),
// jadi teksnya dipecah supaya tiap tautan bisa diklik.
// ponytail: tautan dikenali dari teks balasan; ubah ke daftar tautan
// terstruktur dari API jika model mulai menulis URL dengan format aneh.
const LINK_RE = /(https?:\/\/[^\s]+|\/[a-z0-9-]+\/[a-z0-9-]+)/gi;

function renderText(text, isUser) {
    return String(text ?? '').split(LINK_RE).map((part, index) => {
        if (index % 2 === 0) return part;

        // Buang tanda baca pembungkus yang ikut terbaca, mis. kurung dari
        // "(http://127.0.0.1:8000/berita/judul)".
        const href = part.replace(/[.,;:!?)\]}'"»]+$/, '');
        const trailing = part.slice(href.length);
        const className = `break-words underline underline-offset-2 focus-visible:outline-none focus-visible:ring-2 ${
            isUser ? 'text-white focus-visible:ring-white' : 'text-primary focus-visible:ring-primary'
        }`;

        return (
            <span key={index}>
                {href.startsWith('/') ? (
                    <Link href={href} className={className}>{href}</Link>
                ) : (
                    <a href={href} target="_blank" rel="noopener noreferrer" className={className}>{href}</a>
                )}
                {trailing}
            </span>
        );
    });
}

export default function ChatWidgetPanel({ onClose, settings = {} }) {
    const reduce = useReducedMotion();
    const [closing, setClosing] = useState(false);
    
    const assistantName = settings.chat_assistant_name || 'SMA XYZ Virtual Assistant';
    const siteName = settings.site_name || 'SMA XYZ';
    
    const defaultGreeting = settings.chat_greeting
        ? settings.chat_greeting.replace('{name}', assistantName).replace('{site_name}', siteName)
        : `Assalamu'alaikum! Saya ${assistantName}, asisten virtual ${siteName}. Ada yang bisa kami bantu seputar pendaftaran PPDB atau informasi sekolah?`;
    
    const [messages, setMessages] = useState([
        { role: 'assistant', text: defaultGreeting }
    ]);
    const [input, setInput] = useState('');
    const [loading, setLoading] = useState(false);
    const [sessionToken, setSessionToken] = useState(() => {
        try {
            return window.sessionStorage.getItem(SESSION_KEY);
        } catch {
            return null;
        }
    });
    const scrollRef = useRef(null);

    useEffect(() => {
        if (!sessionToken) return;

        let cancelled = false;

        fetch(`/api/chat/history?session_token=${encodeURIComponent(sessionToken)}`, {
            headers: { Accept: 'application/json' },
        })
            .then((res) => (res.ok ? res.json() : null))
            .then((data) => {
                if (cancelled || !data?.messages?.length) return;
                setMessages((prev) => [
                    ...prev,
                    ...data.messages.map((m) => ({ role: m.role, text: m.content })),
                ]);
            })
            .catch(() => {
                // Riwayat gagal dimuat — biarkan sapaan saja, chat tetap bisa dipakai.
            });

        return () => { cancelled = true; };
    }, [sessionToken]);

    useEffect(() => {
        const el = scrollRef.current;
        if (el) {
            el.scrollTop = el.scrollHeight;
        }
    }, [messages, loading]);

    const handleSend = async (e) => {
        e.preventDefault();
        if (!input.trim() || loading) return;

        const userText = input.trim();
        setInput('');
        setMessages((prev) => [...prev, { role: 'user', text: userText }]);
        setLoading(true);

        try {
            const res = await fetch('/api/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    message: userText,
                    session_token: sessionToken,
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                setMessages((prev) => [...prev, {
                    role: 'assistant',
                    text: data.message || 'Mohon maaf, terjadi gangguan. Silakan coba lagi.',
                }]);
                return;
            }

            if (data.session_token && data.session_token !== sessionToken) {
                setSessionToken(data.session_token);
                try {
                    window.sessionStorage.setItem(SESSION_KEY, data.session_token);
                } catch {
                    // Penyimpanan browser tidak tersedia — sesi baru tiap kirim.
                }
            }

            setMessages((prev) => [...prev, { role: 'assistant', text: data.reply }]);
        } catch (err) {
            setMessages((prev) => [...prev, { role: 'assistant', text: 'Mohon maaf, terjadi gangguan koneksi. Silakan coba lagi.' }]);
        } finally {
            setLoading(false);
        }
    };

    const requestClose = () => {
        if (reduce || closing) {
            onClose();
            return;
        }

        setClosing(true);
    };

    return (
        <div className="fixed bottom-6 right-6 z-50">
            <motion.div
                initial={reduce ? false : { opacity: 0, scale: 0.85, y: 30 }}
                animate={closing ? { opacity: 0, scale: 0.9, y: 20 } : { opacity: 1, scale: 1, y: 0 }}
                transition={closing
                    ? { duration: 0.18, ease: 'easeIn' }
                    : { type: 'spring', stiffness: 350, damping: 25 }}
                onAnimationComplete={() => { if (closing) onClose(); }}
                style={{ transformOrigin: 'bottom right' }}
                className="flex h-[450px] w-80 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl sm:w-96"
            >
                <div className="flex items-center justify-between bg-primary p-4 text-white">
                    <div className="flex items-center gap-2">
                        <div className="h-2.5 w-2.5 rounded-full bg-emerald-400" aria-hidden="true" />
                        <span className="text-sm font-bold">{assistantName}</span>
                    </div>
                    <button
                        type="button"
                        onClick={requestClose}
                        aria-label="Tutup chat"
                        className="rounded text-white/80 transition-colors hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                    >
                        <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div ref={scrollRef} className="flex-1 space-y-3 overflow-y-auto bg-slate-50 p-4 text-sm" aria-live="polite">
                    {messages.map((m, idx) => (
                        <motion.div
                            key={idx}
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.22, ease: 'easeOut' }}
                            className={`flex ${m.role === 'user' ? 'justify-end' : 'justify-start'}`}
                        >
                            <div className={`max-w-[80%] break-words rounded-lg p-3 ${m.role === 'user' ? 'rounded-br-none bg-primary text-white' : 'rounded-bl-none border border-slate-200 bg-white text-slate-800 shadow-sm'}`}>{renderText(m.text, m.role === 'user')}</div>
                        </motion.div>
                    ))}
                    {loading && (
                        <div className="flex justify-start">
                            <div className="rounded-lg border border-slate-200 bg-white p-3 text-xs text-slate-500 animate-pulse">
                                Sedang mengetik...
                            </div>
                        </div>
                    )}
                    <p className="text-center text-[10px] text-slate-400">Jangan kirim data pribadi ke asisten ini.</p>
                </div>

                <form onSubmit={handleSend} className="flex gap-2 border-t border-slate-200 bg-white p-2">
                    <input
                        type="text"
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        placeholder="Ketik pertanyaan Anda..."
                        aria-label="Tulis pertanyaan"
                        className="flex-1 rounded-lg border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-primary"
                    />
                    <button
                        type="submit"
                        disabled={loading || !input.trim()}
                        aria-label="Kirim pesan"
                        className="rounded-lg bg-primary p-2 text-white transition hover:bg-primary-light disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                    >
                        <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
            </motion.div>
        </div>
    );
}
