import { Link, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import '../../css/admin.css';
import Button from '@/Components/Button';
import Icon from '@/Components/Icon';
import ThemeToggle from '@/Components/ThemeToggle';

export default function AdminLayout({ children, title }) {
    const page = usePage();
    const { auth, settings } = page.props;
    const url = page.url || '';
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [settingsOpen, setSettingsOpen] = useState(false);
    const [userMenuOpen, setUserMenuOpen] = useState(false);
    const userMenuRef = useRef(null);

    const siteName = settings?.site_name || 'SMALISKA';
    const siteLogo = settings?.site_logo;
    const user = auth?.user;
    const userName = user?.name || 'Admin';
    const userEmail = user?.email || '';

    useEffect(() => {
        if (!userMenuOpen) return;

        const closeOutside = (event) => {
            if (!userMenuRef.current?.contains(event.target)) setUserMenuOpen(false);
        };
        const closeEscape = (event) => {
            if (event.key === 'Escape') setUserMenuOpen(false);
        };

        document.addEventListener('pointerdown', closeOutside);
        document.addEventListener('keydown', closeEscape);
        return () => {
            document.removeEventListener('pointerdown', closeOutside);
            document.removeEventListener('keydown', closeEscape);
        };
    }, [userMenuOpen]);

    useEffect(() => {
        if (!sidebarOpen) return;

        const closeEscape = (event) => {
            if (event.key === 'Escape') setSidebarOpen(false);
        };

        document.addEventListener('keydown', closeEscape);
        return () => document.removeEventListener('keydown', closeEscape);
    }, [sidebarOpen]);

    const isStaff = auth?.user?.role === 'staff';
    const isSuperAdmin = auth?.user?.role === 'superadmin';

    const navigation = [
        { name: 'Dashboard', href: '/admin/dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        ...(!isStaff ? [
            { divider: true, label: 'Kelola Konten' },
            { name: 'Berita & Artikel', href: '/admin/posts', icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z' },
            { name: 'Halaman Statis', href: '/admin/pages', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { name: 'Data Guru & Tendik', href: '/admin/staff', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
            { divider: true, label: 'Pengaturan Tampilan' },
            { name: 'Halaman Beranda', href: '/admin/home-content', icon: 'M3 3h18v18H3V3zm4 4h10M7 11h10M7 15h6' },
            { name: 'Kategori Konten', href: '/admin/categories', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' },
        ] : []),
    ];

    const adminOnlyNavigation = isSuperAdmin ? [
        { divider: true, label: 'Administrasi' },
        { name: 'Kelola Pengguna', href: '/admin/users', icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z' },
    ] : [];

    const fullNavigation = [...navigation, ...adminOnlyNavigation];

    const settingsMenu = [
        { name: 'Identitas & Logo Situs', href: '/admin/settings/footer' },
        { name: 'Menu Navigasi (Navbar)', href: '/admin/settings/navigation' },
        { name: 'Link Topbar', href: '/admin/settings/topbar' },
        { name: 'Banner Beranda (Hero)', href: '/admin/settings/hero' },
        { name: 'Kontak & Link PPDB', href: '/admin/settings/contact' },
        { name: 'Statistik Sekolah', href: '/admin/settings/statistics' },
        { name: 'Kartu PPDB (Sidebar Artikel)', href: '/admin/settings/sidebar-cta' },
        { name: 'Layanan Pintas (3 Card)', href: '/admin/settings/quick-links' },
        { name: 'Media Sosial', href: '/admin/settings/social-media' },
        { name: 'Chat AI', href: '/admin/settings/chat' },
    ];

    const isActive = (href) => url.startsWith(href);
    const isSettingsActive = url.startsWith('/admin/settings');
    const navigationItems = (list, mobile = false) => list.map((item) =>
        item.divider ? (
            mobile ? (
                <div key={item.label} className="pb-2 pt-4">
                    <p className="px-3 text-xs font-bold uppercase tracking-wider text-slate-400">{item.label}</p>
                </div>
            ) : (
                <li key={item.label} className="pb-2 pt-4">
                    <p className="px-3 text-xs font-bold uppercase tracking-wider text-slate-400">{item.label}</p>
                </li>
            )
        ) : mobile ? (
            <Link
                key={item.name}
                href={item.href}
                onClick={() => setSidebarOpen(false)}
                className={`flex min-h-11 items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition ${
                    isActive(item.href) ? 'bg-primary text-white' : 'text-slate-700 hover:bg-slate-100'
                }`}
            >
                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" d={item.icon} />
                </svg>
                {item.name}
            </Link>
        ) : (
            <li key={item.name}>
                <Link
                    href={item.href}
                    className={`flex min-h-11 items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition ${
                        isActive(item.href) ? 'bg-primary text-white' : 'text-slate-700 hover:bg-slate-100'
                    }`}
                >
                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" d={item.icon} />
                    </svg>
                    {item.name}
                </Link>
            </li>
        )
    );

    return (
        <div className="min-h-screen bg-surface">
            <div className="lg:pl-64">
                <div className="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-slate-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        className="-m-2.5 p-2.5 text-slate-700 lg:hidden"
                        onClick={() => setSidebarOpen(true)}
                    >
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <div className="flex-1 text-lg font-semibold text-slate-800">{title}</div>

                    <div className="flex items-center gap-x-3 sm:gap-x-4">
                        <ThemeToggle />

                        <Button href="/" variant="outline" target="_blank" rel="noreferrer" icon="external-link">Lihat beranda</Button>

                        <div ref={userMenuRef} className="relative">
                            <button
                                type="button"
                                onClick={() => setUserMenuOpen((v) => !v)}
                                aria-label="Menu akun pengguna"
                                aria-haspopup="menu"
                                aria-expanded={userMenuOpen}
                                className="flex min-h-11 items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                            >
                                <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
                                    {userName.split(' ').map((n) => n[0]).join('').slice(0, 2).toUpperCase()}
                                </div>
                                <span className="hidden max-w-[120px] truncate sm:inline md:max-w-[180px]">{userName}</span>
                                <svg
                                    className={`h-4 w-4 text-slate-400 transition-transform ${userMenuOpen ? 'rotate-180' : ''}`}
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="2"
                                    stroke="currentColor"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            {userMenuOpen && (
                                <div
                                    role="menu"
                                    aria-label="Menu pengguna"
                                    className="absolute right-0 top-full z-[60] mt-2 w-56 divide-y divide-slate-100 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg"
                                >
                                    <div className="px-4 py-3">
                                        <p className="truncate text-sm font-semibold text-slate-800">{userName}</p>
                                        {userEmail && <p className="truncate text-xs text-slate-500">{userEmail}</p>}
                                        {roleLabels[user?.role] && (
                                            <span className={`mt-2 inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${roleBadgeClasses[user.role]}`}>
                                                {roleLabels[user.role]}
                                            </span>
                                        )}
                                    </div>
                                    <div className="p-1">
                                        <Link
                                            href={route('profile.edit')}
                                            onClick={() => setUserMenuOpen(false)}
                                            role="menuitem"
                                            className="flex min-h-11 w-full items-center gap-2.5 rounded-md px-3 text-sm text-slate-700 transition hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                        >
                                            <Icon name="user" className="h-4 w-4 shrink-0 text-slate-400" />
                                            <span>Profil Akun</span>
                                        </Link>
                                    </div>
                                    <div className="p-1">
                                        <Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                            onClick={() => setUserMenuOpen(false)}
                                            role="menuitem"
                                            className="flex min-h-11 w-full items-center gap-2.5 rounded-md px-3 text-sm font-medium text-red-600 transition hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500"
                                        >
                                            <Icon name="logout" className="h-4 w-4 shrink-0" />
                                            <span>Keluar</span>
                                        </Link>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                <main className="py-8 px-4 sm:px-6 lg:px-8">
                    {children}
                </main>
            </div>

            <div className={`fixed inset-0 z-50 lg:hidden ${sidebarOpen ? 'block' : 'hidden'}`}>
                <div className="fixed inset-0 bg-slate-900/80" onClick={() => setSidebarOpen(false)} />
                <div className="fixed inset-y-0 left-0 w-64 bg-white">
                    <div className="flex h-16 items-center gap-3 px-6 border-b border-slate-200">
                        {siteLogo && (
                            <img src={siteLogo} alt={siteName} className="h-8 w-auto" />
                        )}
                        <span className="text-xl font-bold text-primary">{siteName}</span>
                    </div>
                    <nav className="space-y-1 p-4">
                        {navigationItems(fullNavigation, true)}
                        {!isStaff && (
                            <div>
                                <button
                                    onClick={() => setSettingsOpen(!settingsOpen)}
                                    className={`w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition ${
                                        isSettingsActive
                                            ? 'bg-primary text-white'
                                            : 'text-slate-700 hover:bg-slate-100'
                                    }`}
                                >
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Pengaturan
                                    <svg className={`w-4 h-4 ml-auto transition-transform ${settingsOpen ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                {settingsOpen && (
                                    <div className="ml-4 mt-1 space-y-1">
                                        {settingsMenu.map((item) => (
                                            <Link
                                                key={item.name}
                                                href={item.href}
                                                className={`block px-3 py-2 rounded-lg text-sm transition ${
                                                    url === item.href
                                                        ? 'bg-primary/20 text-primary font-medium'
                                                        : 'text-slate-600 hover:bg-slate-100'
                                                }`}
                                            >
                                                {item.name}
                                            </Link>
                                        ))}
                                    </div>
                                )}
                            </div>
                        )}
                    </nav>
                </div>
            </div>

            <div className="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
                <div className="flex grow flex-col gap-y-5 overflow-y-auto border-r border-slate-200 bg-white px-6 pb-4">
                    <div className="flex h-16 shrink-0 items-center gap-3">
                        {siteLogo && (
                            <img src={siteLogo} alt={siteName} className="h-8 w-auto" />
                        )}
                        <span className="text-xl font-bold text-primary">{siteName}</span>
                    </div>
                    <nav className="flex flex-1 flex-col">
                        <ul className="flex flex-col gap-y-1">
                            {navigationItems(fullNavigation)}
                            {!isStaff && (
                                <li>
                                    <button
                                        onClick={() => setSettingsOpen(!settingsOpen)}
                                        className={`w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition ${
                                            isSettingsActive
                                                ? 'bg-primary text-white'
                                                : 'text-slate-700 hover:bg-slate-100'
                                        }`}
                                    >
                                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Pengaturan
                                        <svg className={`w-4 h-4 ml-auto transition-transform ${settingsOpen ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                    {settingsOpen && (
                                        <div className="ml-4 mt-1 space-y-1">
                                            {settingsMenu.map((item) => (
                                                <Link
                                                    key={item.name}
                                                    href={item.href}
                                                    className={`block px-3 py-2 rounded-lg text-sm transition ${
                                                        url === item.href
                                                            ? 'bg-primary/20 text-primary font-medium'
                                                            : 'text-slate-600 hover:bg-slate-100'
                                                    }`}
                                                >
                                                    {item.name}
                                                </Link>
                                            ))}
                                        </div>
                                    )}
                                </li>
                            )}
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    );
}
