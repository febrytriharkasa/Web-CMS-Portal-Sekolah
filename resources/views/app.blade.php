@php
    // Meta dirakit di server supaya crawler yang tidak menjalankan JS (WhatsApp,
    // Facebook) tetap menerima judul, deskripsi, dan gambar per halaman.
    $pageProps = $page['props'] ?? [];
    $settings = $pageProps['settings'] ?? [];

    $siteName = data_get($settings, 'site_name') ?: config('app.name', 'SMALISKA');
    $fallbackDescription = data_get($settings, 'footer_about')
        ?: (data_get($settings, 'site_tagline')
        ?: 'Portal resmi SMA Al Islam Krian (SMALISKA): berita sekolah, agenda, prestasi, galeri kegiatan, profil guru, dan informasi PPDB.');

    $component = $page['component'] ?? '';
    $metaTitle = null;
    $metaDescription = $fallbackDescription;
    $metaImage = data_get($settings, 'site_logo');
    $metaType = 'website';
    $metaRobots = 'index, follow';

    if ($component === 'Public/PostDetail') {
        $post = $pageProps['post'] ?? null;
        $metaTitle = data_get($post, 'title');
        $metaDescription = \Illuminate\Support\Str::limit(
            trim(strip_tags(html_entity_decode((string) data_get($post, 'content'), ENT_QUOTES | ENT_HTML5, 'UTF-8'))),
            160,
        ) ?: $fallbackDescription;
        $metaImage = data_get($post, 'featured_image') ?: $metaImage;
        $metaType = 'article';
    } elseif ($component === 'Public/Page') {
        $staticPage = $pageProps['page'] ?? null;
        $metaTitle = data_get($staticPage, 'title');
        $metaDescription = \Illuminate\Support\Str::limit(
            trim(strip_tags(html_entity_decode((string) data_get($staticPage, 'content'), ENT_QUOTES | ENT_HTML5, 'UTF-8'))),
            160,
        ) ?: $fallbackDescription;
        $metaImage = data_get($staticPage, 'image') ?: $metaImage;
        $metaRobots = data_get($pageProps, 'draftPreview') ? 'noindex, nofollow' : $metaRobots;
    } elseif ($component === 'Public/Archive') {
        $metaTitle = $pageProps['title'] ?? null;
        $metaDescription = 'Kumpulan '.\Illuminate\Support\Str::lower((string) $metaTitle).' terbaru dari '.$siteName.', lengkap dengan tanggal dan kategori.';
        $metaImage = data_get($pageProps, 'items.data.0.featured_image') ?: $metaImage;
    } elseif ($component === 'Public/Search') {
        $keyword = $pageProps['keyword'] ?? '';
        $metaTitle = $keyword ? 'Pencarian: '.$keyword : 'Pencarian';
        $metaDescription = 'Cari berita, agenda, prestasi, dan galeri kegiatan sekolah.';
        $metaRobots = 'noindex, follow';
    } elseif ($component === 'Public/Statistics') {
        $metaTitle = 'Statistik Sekolah';
        $metaDescription = 'Data statistik '.$siteName.': jumlah siswa, guru, tenaga kependidikan, berita, prestasi, agenda, dan dokumentasi.';
    } elseif ($component === 'Public/NotFound') {
        $metaTitle = 'Halaman tidak ditemukan';
        $metaRobots = 'noindex, nofollow';
    } elseif ($component === 'Welcome') {
        $metaImage = data_get($settings, 'hero_slide_1_image')
            ?: (data_get($settings, 'hero_background_image') ?: $metaImage);
    }

    $metaFullTitle = $metaTitle ? $metaTitle.' - '.$siteName : $siteName;
    $metaImageUrl = $metaImage ? url($metaImage) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ $metaFullTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <meta name="robots" content="{{ $metaRobots }}">
        <meta name="theme-color" content="#065F46">
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">

        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:type" content="{{ $metaType }}">
        <meta property="og:title" content="{{ $metaFullTitle }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        @if ($metaImageUrl)
            <meta property="og:image" content="{{ $metaImageUrl }}">
        @endif
        <meta name="twitter:card" content="{{ $metaImageUrl ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $metaFullTitle }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script>
            (function () {
                var stored = localStorage.getItem('theme') || 'system';
                var dark = stored === 'dark' || (stored === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (dark) document.documentElement.classList.add('dark');
            })();
        </script>

        @php $gaId = trim((string) data_get($settings, 'google_analytics_id')); @endphp
        @if ($gaId !== '' && app()->environment('production'))
            {{-- GA4 gtag.js. Page view Inertia SPA ditangkap enhanced measurement,
                 jadi tidak ada gtag('config') ulang di tiap navigasi. --}}
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag() { dataLayer.push(arguments); }
                gtag('js', new Date());
                gtag('config', @js($gaId));
            </script>
        @endif

        <!-- Fonts: dimuat tanpa memblokir render; teks langsung tampil dengan font cadangan. -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" media="print" onload="this.media='all'">
        <noscript>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap">
        </noscript>

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
        @inertiaHead
        @php
            $heroImage = data_get($pageProps, 'settings.hero_slide_1_image')
                ?: data_get($pageProps, 'settings.hero_background_image');
            $heroSrcset = $heroImage ? \App\Support\ImageVariant::srcset($heroImage) : null;
            $heroSizes = '(min-width: 640px) 100vw, 100vw';

            // Salinan slide 1 untuk hero statis. Fallback harus sama dengan
            // objek slides di resources/js/Pages/Welcome.jsx; ubah keduanya
            // bersamaan kalau teks hero berubah.
            $heroTag = data_get($settings, 'hero_slide_1_tag') ?: 'Portal resmi SMA XYZ';
            $heroTitle = data_get($settings, 'hero_slide_1_title')
                ?: (data_get($settings, 'hero_title') ?: 'Membentuk Generasi Rabbani & Berprestasi');
            $heroBtnText = data_get($settings, 'hero_slide_1_btn_text') ?: 'Sambutan Kepala Sekolah';
            $heroBtnUrl = data_get($settings, 'hero_slide_1_btn_url') ?: '#sambutan';
            $heroBtnExternal = ! str_starts_with($heroBtnUrl, '/');

            // Vite dev mengirim CSS lewat JS, jadi hero statis akan sempat tampil
            // tanpa Tailwind. Saat hot file ada, biarkan React yang merender.
            $renderStaticHero = $heroImage
                && $component === 'Welcome'
                && ! file_exists(public_path('hot'));
        @endphp
        @if ($heroImage && $component === 'Welcome')
            {{-- Hero dilukis dari HTML ini supaya LCP tidak menunggu React hydrate. --}}
            <link rel="preload" as="image" href="{{ $heroImage }}"
                @if ($heroSrcset) imagesrcset="{{ $heroSrcset }}" imagesizes="{{ $heroSizes }}" @endif
                fetchpriority="high">
        @endif
    </head>
    <body class="font-sans antialiased">
        {{-- Root Inertia ditulis manual (bukan @inertia) supaya hero bisa ikut
             di HTML pertama: LCP jadi tugas server, bukan menunggu React hydrate.
             SSR tidak dipakai di aplikasi ini, jadi tidak ada yang hilang. --}}
        <div id="app" data-page="{{ json_encode($page) }}">
            @if ($renderStaticHero)
                {{-- React mengganti isi #app saat mount. Teks ikut dicetak di sini
                     supaya pengunjung tidak melihat foto kosong lebih dulu; tombol
                     dibuat tidak fokusable agar tidak dobel dengan versi React. --}}
                <div id="hero-static" aria-hidden="true">
                    <section class="relative isolate overflow-hidden bg-slate-900 text-white flex items-center w-full min-h-[19rem] aspect-[4/3] sm:aspect-[16/7] sm:py-10 lg:py-16 sm:min-h-[24rem] lg:min-h-[30rem]">
                        <img src="{{ $heroImage }}"
                            @if ($heroSrcset) srcset="{{ $heroSrcset }}" sizes="{{ $heroSizes }}" @endif
                            alt="" width="1920" height="1080" fetchpriority="high" decoding="async"
                            class="absolute inset-0 -z-20 h-full w-full object-cover object-center">
                        <div class="absolute inset-0 -z-10 bg-slate-950/70"></div>
                        <div class="mx-auto grid w-full max-w-7xl items-center gap-4 px-4 sm:px-6 lg:grid-cols-[minmax(0,1.25fr)_minmax(18rem,0.75fr)] relative z-10">
                            <div class="max-w-3xl flex items-center">
                                <div class="w-full">
                                    <p class="mb-1.5 sm:mb-3 text-xs font-bold uppercase tracking-[0.18em] text-emerald-200">{{ $heroTag }}</p>
                                    <h1 class="max-w-3xl break-words text-[clamp(1.75rem,7vw,2rem)] font-extrabold leading-[1.2] tracking-[-0.015em] sm:text-4xl md:text-5xl lg:text-6xl">{{ $heroTitle }}</h1>
                                    <div class="mt-4 sm:mt-6 flex flex-wrap gap-3 items-center">
                                        <a href="{{ $heroBtnUrl }}"
                                            target="{{ $heroBtnExternal ? '_blank' : '_self' }}"
                                            rel="{{ $heroBtnExternal ? 'noopener noreferrer' : '' }}"
                                            tabindex="-1"
                                            class="inline-flex min-h-9 sm:min-h-12 items-center justify-center rounded-lg bg-emerald-200 px-4 py-2 sm:px-6 sm:py-3 text-xs sm:text-sm font-bold text-emerald-950 transition hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-primary shadow-lg">{{ $heroBtnText }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            @endif
        </div>
    </body>
</html>
