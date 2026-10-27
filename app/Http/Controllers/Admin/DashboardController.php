<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ChatSession;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Staff;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'total_posts' => Post::count(),
            'published_posts' => Post::where('status', 'published')->count(),
            'draft_posts' => Post::where('status', 'draft')->count(),
            'total_pages' => Page::count(),
            'published_pages' => Page::where('is_published', true)->count(),
            'total_staff' => Staff::count(),
            'total_guru' => Staff::where('type', 'guru')->count(),
            'total_tendik' => Staff::whereIn('type', ['tendik', 'gtk'])->count(),
            'total_categories' => Category::count(),
            'total_chat_sessions' => ChatSession::count(),
            'total_galleries' => Post::whereHas('category', fn ($q) => $q->where('slug', 'galeri'))->count(),
        ];

        $latestPosts = Post::with(['category', 'user'])
            ->latest('updated_at')
            ->take(6)
            ->get(['id', 'title', 'slug', 'featured_image', 'status', 'published_at', 'updated_at', 'category_id', 'user_id']);

        $latestPages = Page::latest('updated_at')
            ->take(5)
            ->get(['id', 'title', 'slug', 'is_published', 'updated_at']);

        $settings = SiteSetting::allCached()->pluck('value', 'key');

        $siteInfo = [
            'site_name' => $settings->get('site_name') ?: 'SMA XYZ',
            'site_logo' => $settings->get('site_logo'),
        ];

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'latestPosts' => $latestPosts,
            'latestPages' => $latestPages,
            'siteInfo' => $siteInfo,
            'chatEnabled' => $settings->get('chat_enabled', '1') !== '0',
        ]);
    }

    public function toggleChat()
    {
        $current = SiteSetting::getVal('chat_enabled', '1');
        $new = $current === '0' ? '1' : '0';

        SiteSetting::updateOrCreate(
            ['key' => 'chat_enabled'],
            ['value' => $new, 'type' => 'boolean', 'group_name' => 'features', 'updated_at' => now()]
        );

        return back()->with('success', $new === '1' ? 'Fitur AI Chatbot berhasil diaktifkan.' : 'Fitur AI Chatbot berhasil dinonaktifkan.');
    }
}
