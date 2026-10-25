import { lazy, Suspense, useState } from 'react';
import { usePage } from '@inertiajs/react';

// Launcher stays lightweight; the animated panel loads only after a click.
const ChatWidgetPanel = lazy(() => import('./ChatWidgetPanel'));

export default function ChatWidget() {
    const { settings = {} } = usePage().props;
    const enabled = settings.chat_enabled !== '0';
    const [open, setOpen] = useState(false);

    if (!enabled) {
        return null;
    }

    if (open) {
        return (
            <Suspense fallback={null}>
                <ChatWidgetPanel onClose={() => setOpen(false)} settings={settings} />
            </Suspense>
        );
    }

    return (
        <button
            type="button"
            onClick={() => setOpen(true)}
            className="chat-launcher fixed bottom-6 right-6 z-50 flex items-center gap-2 rounded-full bg-primary p-4 text-white shadow-lg transition hover:bg-primary-light focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
        >
            <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span className="sr-only text-sm font-semibold sm:not-sr-only sm:inline">Tanya SMA XYZ AI</span>
        </button>
    );
}
