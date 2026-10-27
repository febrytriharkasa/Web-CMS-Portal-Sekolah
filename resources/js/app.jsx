import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { ThemeProvider } from './Contexts/ThemeContext';

// Nama situs diambil dari pengaturan sekolah saat halaman pertama dimuat, jadi
// judul tab tidak bergantung pada APP_NAME yang masih "laravel".
let siteName = import.meta.env.VITE_APP_NAME || 'SMA XYZ';

// Proteksi InfinityFree: jika request XHR tidak membawa cookie __test, server
// membalas halaman HTML (bukan JSON Inertia) sehingga Inertia menampilkan modal
// putih. Kita cegah modal tersebut dan arahkan user lewat full page reload agar
// browser menyelesaikan challenge cookie InfinityFree secara normal.
router.on('invalid', (event) => {
    // Cegah Inertia menampilkan modal putih default
    event.preventDefault();

    const responseUrl =
        event?.detail?.response?.request?.responseURL ||
        event?.detail?.error?.config?.url ||
        window.location.href;

    // Anti-looping: jika URL yang sama sudah pernah mengalihkan, pulangkan ke
    // halaman utama agar tidak berputar tanpa henti
    const key = `inertia-invalid-${responseUrl}`;
    try {
        if (sessionStorage.getItem(key)) {
            window.location.href = '/';
            return;
        }
        sessionStorage.setItem(key, '1');
    } catch {
        // sessionStorage tidak tersedia (sandbox) — lanjut redirect saja
    }

    // Redirect full page: cookie InfinityFree hanya bisa di-set oleh
    // navigasi browser biasa, bukan lewat XHR
    window.location.href = responseUrl;
});

createInertiaApp({
    // Judul kosong berarti halaman tidak punya judul sendiri (beranda): pakai nama situs.
    title: (title) => (title ? `${title} - ${siteName}` : siteName),
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.jsx`,
            import.meta.glob('./Pages/**/*.jsx'),
        ),
    setup({ el, App, props }) {
        siteName = props.initialPage.props.settings?.site_name || siteName;

        const root = createRoot(el);

        root.render(
            <ThemeProvider>
                <App {...props} />
            </ThemeProvider>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});
