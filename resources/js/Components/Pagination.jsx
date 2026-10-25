import { Link } from '@inertiajs/react';

export default function Pagination({ links, className = '', variant = 'admin' }) {
    // Laravel adds prev/next wrapping links; hide when single page.
    if (!links || links.length <= 3) return null;

    const isPublic = variant === 'public';

    const styles = isPublic
        ? {
              nav: 'mt-10 flex flex-wrap items-center justify-center gap-1.5',
              disabled:
                  'inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-300',
              base: 'inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg border px-3 text-sm font-semibold transition',
              active: 'border-primary bg-primary text-white',
              idle: 'border-slate-200 bg-white text-slate-700 hover:border-primary hover:text-primary',
          }
        : {
              nav: 'flex flex-wrap items-center justify-end gap-1 border-t border-slate-100 px-4 py-3',
              disabled:
                  'inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-medium text-slate-300',
              base: 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-medium transition',
              active: 'bg-primary text-white',
              idle: 'text-slate-600 hover:bg-slate-100',
          };

    return (
        <nav aria-label="Navigasi halaman" className={`${styles.nav} ${className}`}>
            {links.map((link, index) => {
                const label = link.label
                    .replace('&laquo;', isPublic ? '«' : '‹')
                    .replace('&raquo;', isPublic ? '»' : '›')
                    .replace('Previous', '')
                    .replace('Next', '');

                if (!link.url) {
                    return (
                        <span
                            key={index}
                            aria-disabled="true"
                            className={styles.disabled}
                            dangerouslySetInnerHTML={{ __html: label }}
                        />
                    );
                }

                return (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll={isPublic ? false : true}
                        onSuccess={isPublic ? () => window.scrollTo({ top: 0, behavior: 'smooth' }) : undefined}
                        aria-current={link.active ? 'page' : undefined}
                        className={`${styles.base} ${link.active ? styles.active : styles.idle}`}
                        dangerouslySetInnerHTML={{ __html: label }}
                    />
                );
            })}
        </nav>
    );
}
