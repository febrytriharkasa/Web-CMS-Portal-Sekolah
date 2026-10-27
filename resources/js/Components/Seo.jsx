import { Head, usePage } from '@inertiajs/react';

const FALLBACK_DESCRIPTION =
    'Portal resmi SMA XYZ: berita sekolah, agenda, prestasi, galeri kegiatan, profil guru, dan informasi PPDB.';

/**
 * Absolutkan URL `/storage/...` supaya canonical dan og:image valid di crawler.
 */
export function absoluteUrl(value, base) {
    if (!value) {
        return null;
    }

    if (/^https?:\/\//i.test(value)) {
        return value;
    }

    return `${base}${value.startsWith('/') ? '' : '/'}${value}`;
}

/**
 * WhatsApp, Facebook, dan X menolak SVG sebagai og:image; cover SVG jatuh ke logo situs.
 */
function shareableImage(value, base) {
    const url = absoluteUrl(value, base);

    return url && !/\.svg(\?|$)/i.test(url) ? url : null;
}

/**
 * Meta tag halaman. Judul dokumen mengikuti template di app.jsx, jadi `title`
 * di sini cukup judul halaman tanpa nama situs.
 */
export default function Seo({
    title,
    description,
    image,
    type = 'website',
    robots = 'index, follow',
    publishedAt = null,
    modifiedAt = null,
    jsonLd = null,
}) {
    const { url, props } = usePage();
    const settings = props.settings || {};
    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    const base = (props.appUrl || origin || '').replace(/\/+$/, '');
    const siteName = settings.site_name || 'SMA XYZ';

    const path = (url || '/').split('?')[0];
    const pageParam = new URLSearchParams((url || '').split('?')[1] || '').get('page');
    const canonical = `${base}${path || '/'}${pageParam && pageParam !== '1' ? `?page=${pageParam}` : ''}`;

    const summary = (description || settings.footer_about || settings.site_tagline || FALLBACK_DESCRIPTION)
        .trim()
        .slice(0, 160);

    const fullTitle = title ? `${title} - ${siteName}` : siteName;
    const ogImage = shareableImage(image, base) || shareableImage(settings.site_logo, base);

    const structuredData = jsonLd
        ? {
              '@context': 'https://schema.org',
              ...jsonLd,
              ...(jsonLd['@type'] === 'EducationalOrganization' ? {} : {
                  publisher: {
                      '@type': 'Organization',
                      name: siteName,
                      ...(shareableImage(settings.site_logo, base) ? { logo: shareableImage(settings.site_logo, base) } : {}),
                  },
              }),
          }
        : null;

    return (
        <Head title={title}>
            <meta name="description" content={summary} />
            <meta name="robots" content={robots} />
            {canonical && <link rel="canonical" href={canonical} />}

            <meta property="og:site_name" content={siteName} />
            <meta property="og:type" content={type} />
            <meta property="og:title" content={fullTitle} />
            <meta property="og:description" content={summary} />
            {canonical && <meta property="og:url" content={canonical} />}
            {ogImage && <meta property="og:image" content={ogImage} />}
            {ogImage && <meta property="og:image:alt" content={title || siteName} />}

            <meta name="twitter:card" content={ogImage ? 'summary_large_image' : 'summary'} />
            <meta name="twitter:title" content={fullTitle} />
            <meta name="twitter:description" content={summary} />
            {ogImage && <meta name="twitter:image" content={ogImage} />}

            {type === 'article' && publishedAt && <meta property="article:published_time" content={publishedAt} />}
            {type === 'article' && modifiedAt && <meta property="article:modified_time" content={modifiedAt} />}

            {structuredData && (
                <script type="application/ld+json">{JSON.stringify(structuredData).replace(/</g, '\\u003c')}</script>
            )}
        </Head>
    );
}
