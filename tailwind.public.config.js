import base from './tailwind.config.js';

/**
 * Public/auth pages only. Admin-only editors are excluded so their utility
 * classes never land in the CSS every visitor downloads.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    ...base,
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/app.jsx',
        './resources/js/Pages/Welcome.jsx',
        './resources/js/Pages/Dashboard.jsx',
        './resources/js/Pages/Public/**/*.jsx',
        './resources/js/Pages/Auth/**/*.jsx',
        './resources/js/Pages/Profile/**/*.jsx',
        './resources/js/Layouts/PublicLayout.jsx',
        './resources/js/Layouts/GuestLayout.jsx',
        './resources/js/Layouts/AuthenticatedLayout.jsx',
        './resources/js/lib/**/*.js',
        './resources/js/Components/ActionIcon.jsx',
        './resources/js/Components/ApplicationLogo.jsx',
        './resources/js/Components/Badge.jsx',
        './resources/js/Components/Breadcrumbs.jsx',
        './resources/js/Components/Button.jsx',
        './resources/js/Components/Card.jsx',
        './resources/js/Components/ChatWidget.jsx',
        './resources/js/Components/ChatWidgetPanel.jsx',
        './resources/js/Components/Checkbox.jsx',
        './resources/js/Components/DataTable.jsx',
        './resources/js/Components/DangerButton.jsx',
        './resources/js/Components/Dropdown.jsx',
        './resources/js/Components/Icon.jsx',
        './resources/js/Components/InputError.jsx',
        './resources/js/Components/InputLabel.jsx',
        './resources/js/Components/MediaPreviewModal.jsx',
        './resources/js/Components/Modal.jsx',
        './resources/js/Components/NavLink.jsx',
        './resources/js/Components/PageEnter.jsx',
        './resources/js/Components/Pagination.jsx',
        './resources/js/Components/PrimaryButton.jsx',
        './resources/js/Components/PublicPageSkeleton.jsx',
        './resources/js/Components/ResponsiveNavLink.jsx',
        './resources/js/Components/Reveal.jsx',
        './resources/js/Components/RichContent.jsx',
        './resources/js/Components/SecondaryButton.jsx',
        './resources/js/Components/Seo.jsx',
        './resources/js/Components/TextInput.jsx',
        './resources/js/Components/ThemeToggle.jsx',
    ],
};
