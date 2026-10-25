import { useEffect, useRef, useState } from 'react';

const VISIBILITY_FALLBACK_MS = 1500;

function isOnScreen(node) {
    if (!node) return false;
    const rect = node.getBoundingClientRect();
    return rect.bottom > 0 && rect.top < window.innerHeight;
}

function useVisibleOnce({ once = true, margin = '-40px' } = {}) {
    const ref = useRef(null);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const node = ref.current;

        if (!node || typeof IntersectionObserver === 'undefined') {
            setVisible(true);
            return undefined;
        }

        let shown = false;
        const reveal = () => {
            if (shown) return;
            shown = true;
            setVisible(true);
        };

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    reveal();
                    if (once) observer.disconnect();
                } else if (!once) {
                    shown = false;
                    setVisible(false);
                }
            },
            { rootMargin: margin },
        );

        observer.observe(node);

        /*
         * Jaring pengaman kalau observer diam (mis. elemen tanpa box).
         * Wajib cek posisi: tanpa cek ini, semua elemen dipaksa tampil
         * setelah 1.5s sehingga animasinya habis dimainkan di luar layar.
         */
        const fallbackTimer = setTimeout(() => {
            if (isOnScreen(node)) reveal();
        }, VISIBILITY_FALLBACK_MS);

        return () => {
            clearTimeout(fallbackTimer);
            observer.disconnect();
        };
    }, [once, margin]);

    return { ref, visible };
}

export function Reveal({ children, className = '', delay = 0, duration = 0.5, y = 24, once = true, as: Component = 'div', style, ...rest }) {
    const { ref, visible } = useVisibleOnce({ once });

    return (
        <Component
            {...rest}
            ref={ref}
            className={`${className} reveal${visible ? ' is-revealed' : ''}`.trim()}
            style={{
                '--reveal-delay': `${delay}s`,
                '--reveal-duration': `${duration}s`,
                '--reveal-y': `${y}px`,
                ...style,
            }}
        >
            {children}
        </Component>
    );
}

export function StaggerContainer({ children, className = '', stagger = 0.08, once = true, style, ...rest }) {
    const { ref, visible } = useVisibleOnce({ once });

    return (
        <div
            {...rest}
            ref={ref}
            className={`${className}${visible ? ' is-revealed' : ''}`.trim()}
            style={{ '--stagger-step': `${stagger}s`, ...style }}
        >
            {children}
        </div>
    );
}
