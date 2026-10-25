<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomeContentController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ChatSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageVariantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

// Varian hero responsif: /img/{lebar}/{path}. Didaftarkan lebih dulu supaya tidak
// tertelan route catch-all /{slug} di bawah.
Route::get('/img/{width}/{path}', [ImageVariantController::class, '__invoke'])
    ->whereNumber('width')
    ->where('path', '.*')
    ->name('image.variant');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/berita', [HomeController::class, 'news'])->name('public.news');
Route::get('/cari', [HomeController::class, 'search'])->name('public.search');
Route::get('/statistik', [HomeController::class, 'statistics'])->name('public.statistics');
Route::get('/guru-tendik', [HomeController::class, 'staffArchive'])->name('public.staff.archive');
Route::get('/guru-tendik/{staff}', [HomeController::class, 'staffDetail'])
    ->whereNumber('staff')
    ->name('public.staff.detail');
Route::post('/api/chat', [ChatBotController::class, 'send'])->middleware('throttle:chat')->name('api.chat');
Route::get('/api/chat/history', [ChatBotController::class, 'history'])
    ->middleware('throttle:60,1')
    ->name('api.chat.history');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $sitemap = rtrim((string) config('app.url'), '/').'/sitemap.xml';

    return response(
        "User-agent: *\nAllow: /\n\nDisallow: /admin\nDisallow: /cari\nDisallow: /login\n\nSitemap: {$sitemap}\n",
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8'],
    );
})->name('robots');

Route::get('/{slug}', [HomeController::class, 'pageBySlug'])
    ->where('slug', '^(?!admin|api|login|logout|register|password|email|dashboard|profile|sanctum|_debugbar|berita|cari|statistik|guru-tendik|verify-email|confirm-password|forgot-password|reset-password)[a-zA-Z0-9_-]+$')
    ->name('public.page');

Route::get('/{category}/{slug}', [HomeController::class, 'showPost'])
    ->where('category', '^(?!admin|api|login|logout|register|password|email|dashboard|profile|sanctum|_debugbar|reset-password|verify-email).*$')
    ->name('public.post');

Route::middleware(['auth', 'verified', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/toggle-chat', [DashboardController::class, 'toggleChat'])->middleware('admin')->name('dashboard.toggle-chat');
    Route::resource('posts', PostController::class)->middleware('admin');
    Route::patch('/posts/{post}/status', [PostController::class, 'updateStatus'])->middleware('admin')->name('posts.status');
    Route::resource('pages', PageController::class)->middleware('admin');
    Route::patch('/pages/{page}/status', [PageController::class, 'updateStatus'])->middleware('admin')->name('pages.status');
    Route::resource('categories', CategoryController::class)->middleware('admin');
    Route::get('/home-content', [HomeContentController::class, 'index'])->middleware('admin')->name('home-content.index');
    Route::put('/home-content', [HomeContentController::class, 'update'])->middleware('admin')->name('home-content.update');
    Route::get('/galleries', fn () => redirect()->route('admin.posts.index', [
        'category_id' => Category::where('slug', 'galeri')->value('id'),
    ]))->middleware('admin')->name('galleries.index');
    Route::get('/galleries/create', fn () => redirect()->route('admin.posts.create', [
        'category_id' => Category::where('slug', 'galeri')->value('id'),
    ]))->middleware('admin')->name('galleries.create');
    Route::resource('staff', StaffController::class)->parameters(['staff' => 'staff'])->middleware('admin');
    Route::redirect('/content', '/admin/staff');

    Route::get('/settings/hero', [SiteSettingController::class, 'hero'])->middleware('admin')->name('settings.hero');
    Route::post('/settings/hero', [SiteSettingController::class, 'updateHero'])->middleware('admin')->name('settings.hero.update');
    Route::get('/settings/contact', [SiteSettingController::class, 'contact'])->middleware('admin')->name('settings.contact');
    Route::post('/settings/contact', [SiteSettingController::class, 'updateContact'])->middleware('admin')->name('settings.contact.update');
    Route::get('/settings/statistics', [SiteSettingController::class, 'statistics'])->middleware('admin')->name('settings.statistics');
    Route::post('/settings/statistics', [SiteSettingController::class, 'updateStatistics'])->middleware('admin')->name('settings.statistics.update');
    Route::get('/settings/quick-links', [SiteSettingController::class, 'quickLinks'])->middleware('admin')->name('settings.quick-links');
    Route::post('/settings/quick-links', [SiteSettingController::class, 'updateQuickLinks'])->middleware('admin')->name('settings.quick-links.update');
    Route::get('/settings/social-media', [SiteSettingController::class, 'socialMedia'])->middleware('admin')->name('settings.social-media');
    Route::post('/settings/social-media', [SiteSettingController::class, 'updateSocialMedia'])->middleware('admin')->name('settings.social-media.update');
    Route::get('/settings/footer', [SiteSettingController::class, 'footer'])->middleware('admin')->name('settings.footer');
    Route::post('/settings/footer', [SiteSettingController::class, 'updateFooter'])->middleware('admin')->name('settings.footer.update');
    Route::get('/settings/navigation', [SiteSettingController::class, 'navigation'])->middleware('admin')->name('settings.navigation');
    Route::post('/settings/navigation', [SiteSettingController::class, 'updateNavigation'])->middleware('admin')->name('settings.navigation.update');
    Route::get('/settings/topbar', [SiteSettingController::class, 'topbar'])->middleware('admin')->name('settings.topbar');
    Route::post('/settings/topbar', [SiteSettingController::class, 'updateTopbar'])->middleware('admin')->name('settings.topbar.update');
    Route::get('/settings/sidebar-cta', [SiteSettingController::class, 'sidebarCta'])->middleware('admin')->name('settings.sidebar-cta');
    Route::post('/settings/sidebar-cta', [SiteSettingController::class, 'updateSidebarCta'])->middleware('admin')->name('settings.sidebar-cta.update');
    Route::get('/settings/chat', [ChatSettingController::class, 'index'])->middleware('admin')->name('settings.chat');
    Route::post('/settings/chat', [ChatSettingController::class, 'update'])->middleware('admin')->name('settings.chat.update');

    Route::get('/users', [UserController::class, 'index'])->middleware('superadmin')->name('users.index');
    Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('superadmin')->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('superadmin')->name('users.destroy');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->middleware('superadmin')->name('users.reset-password');
});

Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Unknown GET URLs stay inside the web middleware group, so the 404 page keeps the site chrome (nav, logo, footer).
Route::get('/{any}', fn () => abort(404))->where('any', '.*');
