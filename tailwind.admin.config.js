import base from './tailwind.config.js';

/**
 * Admin-only utilities. Loaded as a separate stylesheet by AdminLayout so the
 * public bundle does not carry classes used solely by the dashboard.
 *
 * Setiap halaman yang memakai AdminLayout wajib terdaftar di sini. Kalau tidak,
 * utility-nya hanya ada di app.css; karena stylesheet ini dimuat setelahnya,
 * varian breakpoint kecil di sini bisa menimpa varian besar dari app.css
 * (contoh: `sm:grid-cols-2` menang atas `xl:grid-cols-4` di dashboard).
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    ...base,
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/app.jsx',
        './resources/js/Pages/Dashboard.jsx',
        './resources/js/Pages/Admin/**/*.jsx',
        './resources/js/Layouts/AdminLayout.jsx',
        './resources/js/Components/**/*.jsx',
        './resources/js/lib/**/*.js',
    ],
};
