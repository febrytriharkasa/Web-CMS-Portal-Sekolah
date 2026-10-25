<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Staff;
use App\Models\User;
use App\Support\PublicCache;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;

class HomeController extends Controller
{
    private const ITEM_LIMIT = 8;

    /**
     * Kolom yang benar-benar dipakai kartu-kartu di halaman depan.
     *
     * `content` sengaja tidak diambil: 90 post × isi lengkap membuat HTML awal
     * membengkak ~28 KB padahal halaman depan tidak pernah menampilkannya.
     */
    private const CARD_COLUMNS = [
        'id',
        'user_id',
        'category_id',
        'title',
        'slug',
        'featured_image',
        'media_type',
        'video_url',
        'metadata',
        'published_at',
    ];

    public function index()
    {
        $settings = SiteSetting::allCached()->pluck('value', 'key');

        $content = Cache::remember(
            PublicCache::HOME_CONTENT,
            now()->addMinutes(PublicCache::HOME_TTL_MINUTES),
            function (): array {
                $posts = $this->categoryPosts('berita', withAuthor: false, columns: self::CARD_COLUMNS);
                $homeSections = $this->homeSections();
                $galleries = $this->categoryPosts('galeri', withAuthor: false, columns: self::CARD_COLUMNS);

                $allPosts = $posts
                    ->merge($galleries)
                    ->merge($homeSections->flatMap(fn (array $section) => collect($section['posts'])));

                $this->attachAuthors($allPosts);

                return [
                    'posts' => $posts->toArray(),
                    'homeSections' => $homeSections->map(function ($section) {
                        // Ubah Eloquent Collection di dalam array menjadi murni array
                        $section['posts'] = $section['posts']->toArray();

                        return $section;
                    })->toArray(),
                    'galleries' => $galleries->toArray(),
                    'staff' => Staff::latest()->take(8)->get(['id', 'name', 'role', 'type', 'photo'])->toArray(),
                ];
            },
        );

        return Inertia::render('Welcome', [
            'settings' => $settings,
            ...$content,
        ]);
    }

    /**
     * Categories the editor chose to show on the home page, in their set order.
     *
     * @return Collection<int, array{id: int, name: string, slug: string, posts: Collection<int, Post>}>
     */
    private function homeSections(int $limit = self::ITEM_LIMIT)
    {
        $categories = Category::shownOnHome()->get();

        $postsByCategory = Post::with('category')
            ->select(self::CARD_COLUMNS)
            ->where('status', 'published')
            ->whereIn('category_id', $categories->pluck('id'))
            ->latest('published_at')
            ->get()
            ->groupBy('category_id');

        return $categories->map(fn (Category $category) => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'preview_mode' => $category->preview_mode,
            'posts' => $postsByCategory->get($category->id, collect())->take($limit)->values(),
        ])->filter(fn ($section) => $section['posts']->isNotEmpty());
    }

    public function pageBySlug(Request $request, $slug)
    {
        $previewing = $request->boolean('preview') && (bool) $request->user()?->canManageContent();

        $page = Page::where('slug', $slug)
            ->when(! $previewing, fn ($query) => $query->where('is_published', true))
            ->first();

        if ($page) {
            return Inertia::render('Public/Page', [
                'page' => $page,
                'draftPreview' => $previewing && ! $page->is_published,
            ]);
        }

        $category = Category::where('slug', $slug)->firstOrFail();

        if ($category->slug === 'galeri') {
            return $this->gallery($request, $category);
        }

        return Inertia::render('Public/Archive', [
            'title' => $category->name,
            'type' => $category->slug,
            'previewMode' => $category->preview_mode,
            'items' => $this->categoryPosts($category->slug, null, true),
        ]);
    }

    public function news()
    {
        $posts = $this->categoryPosts('berita', null, true);

        return Inertia::render('Public/Archive', ['title' => 'Berita', 'type' => 'berita', 'previewMode' => 'standard', 'items' => $posts]);
    }

    public function staffArchive(Request $request)
    {
        $type = in_array($request->query('type'), ['guru', 'tendik', 'gtk'], true)
            ? $request->query('type') : null;

        $items = Staff::query()
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderBy('name')
            ->paginate(8)
            ->withQueryString();

        $counts = Staff::selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return Inertia::render('Public/StaffArchive', [
            'title' => 'Guru dan Tenaga Kependidikan',
            'type' => $type,
            'counts' => [
                'all' => (int) $counts->sum(),
                'guru' => (int) ($counts['guru'] ?? 0),
                'tendik' => (int) ($counts['tendik'] ?? 0),
                'gtk' => (int) ($counts['gtk'] ?? 0),
            ],
            'items' => $items,
        ]);
    }

    public function staffDetail(Staff $staff)
    {
        return Inertia::render('Public/StaffDetail', [
            'title' => $staff->name,
            'staffMember' => $staff,
        ]);
    }

    public function search(Request $request)
    {
        $keyword = trim((string) $request->query('q', ''));
        $term = $this->likeTerm($keyword);

        $posts = Post::with(['category', 'user:id,name'])
            ->where('status', 'published')
            ->when($term, fn ($query) => $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('content', 'like', $term)
                    ->orWhere('metadata', 'like', $term)
                    ->orWhereHas('category', fn ($category) => $category
                        ->where('name', 'like', $term)
                        ->orWhere('slug', 'like', $term));
            }))
            ->latest('published_at')
            ->paginate(8)
            ->withQueryString();

        $pages = collect();
        $staff = collect();
        $counts = ['posts' => $posts->total(), 'pages' => 0, 'staff' => 0];

        if ($term) {
            $pageQuery = Page::where('is_published', true)
                ->where(fn ($q) => $q->where('title', 'like', $term)->orWhere('content', 'like', $term));

            $counts['pages'] = (clone $pageQuery)->count();

            $pages = $pageQuery->orderBy('title')
                ->limit(6)
                ->get(['title', 'slug', 'content'])
                ->map(fn (Page $page) => [
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'excerpt' => Str::limit(trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $page->content))), 160),
                ]);

            $staffQuery = Staff::where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('role', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });

            $counts['staff'] = (clone $staffQuery)->count();

            $staff = $staffQuery->orderBy('name')
                ->limit(8)
                ->get(['id', 'name', 'role', 'type', 'photo']);
        }

        return Inertia::render('Public/Search', [
            'keyword' => $keyword,
            'items' => $posts,
            'pages' => $pages,
            'staff' => $staff,
            'counts' => $counts,
        ]);
    }

    public function statistics()
    {
        return Inertia::render('Public/Statistics', [
            'statSettings' => SiteSetting::allCached()->where('group_name', 'statistics')->pluck('value', 'key'),
            'counts' => [
                'posts' => Post::where('status', 'published')->whereHas('category', fn ($q) => $q->where('slug', 'berita'))->count(),
                'achievements' => Post::where('status', 'published')->whereHas('category', fn ($q) => $q->where('slug', 'prestasi'))->count(),
                'agendas' => Post::where('status', 'published')->whereHas('category', fn ($q) => $q->where('slug', 'agenda'))->count(),
                'galleries' => Post::where('status', 'published')->whereHas('category', fn ($q) => $q->where('slug', 'galeri'))->count(),
                'teachers' => Staff::where('type', 'guru')->count(),
                'staff' => Staff::whereIn('type', ['tendik', 'gtk'])->count(),
            ],
        ]);
    }

    public function showPost($category, $slug)
    {
        $post = Post::with(['category', 'user:id,name'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereHas('category', fn ($c) => $c->where('slug', $category))
            ->firstOrFail();

        $relatedPosts = Post::where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->take(3)
            ->get();

        $recentPosts = Post::with('category:id,name,slug')
            ->where('id', '!=', $post->id)
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $prevPost = Post::where('category_id', $post->category_id)
            ->where('status', 'published')
            ->where(function ($query) use ($post) {
                $query->where('published_at', '<', $post->published_at)
                    ->orWhere(function ($q) use ($post) {
                        $q->where('published_at', $post->published_at)
                            ->where('id', '<', $post->id);
                    });
            })
            ->orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $nextPost = Post::where('category_id', $post->category_id)
            ->where('status', 'published')
            ->where(function ($query) use ($post) {
                $query->where('published_at', '>', $post->published_at)
                    ->orWhere(function ($q) use ($post) {
                        $q->where('published_at', $post->published_at)
                            ->where('id', '>', $post->id);
                    });
            })
            ->orderBy('published_at', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        $category = $post->category;

        $relatedPosts->each(fn (Post $relatedPost) => $relatedPost->setRelation('category', $category));
        $prevPost?->setRelation('category', $category);
        $nextPost?->setRelation('category', $category);

        return Inertia::render('Public/PostDetail', compact('post', 'relatedPosts', 'recentPosts', 'prevPost', 'nextPost'));
    }

    private function categoryPosts(
        string $slug,
        ?int $limit = self::ITEM_LIMIT,
        bool $paginate = false,
        bool $withAuthor = true,
        ?array $columns = null,
    ) {
        $query = Post::with($withAuthor ? ['category', 'user:id,name'] : ['category'])
            ->when($columns !== null, fn ($q) => $q->select($columns))
            ->where('status', 'published')
            ->whereHas('category', fn ($q) => $q->where('slug', $slug))
            ->latest('published_at');

        if ($paginate) {
            return $query->paginate(8);
        }

        return $limit ? $query->take($limit)->get() : $query->get();
    }

    private function gallery(Request $request, Category $category)
    {
        $media = $request->query('media');
        $media = in_array($media, ['image', 'video'], true) ? $media : null;

        $base = Post::where('status', 'published')->where('category_id', $category->id);

        $counts = (clone $base)
            ->selectRaw("COALESCE(media_type, 'image') as kind, COUNT(*) as total")
            ->groupBy('kind')
            ->pluck('total', 'kind');

        $items = $base
            ->with('category:id,slug')
            ->when($media === 'video', fn ($query) => $query->where('media_type', 'video'))
            ->when($media === 'image', fn ($query) => $query->where(
                fn ($inner) => $inner->where('media_type', 'image')->orWhereNull('media_type')
            ))
            ->latest('published_at')
            ->paginate(8)
            ->withQueryString();

        return Inertia::render('Public/Gallery', [
            'title' => $category->name,
            'media' => $media,
            'counts' => [
                'all' => (int) $counts->sum(),
                'image' => (int) ($counts['image'] ?? 0),
                'video' => (int) ($counts['video'] ?? 0),
            ],
            'items' => $items,
        ]);
    }

    private function likeTerm(string $keyword): ?string
    {
        $cleaned = trim((string) preg_replace('/\s+/u', ' ', str_replace(['%', '_'], ' ', $keyword)));

        return $cleaned === '' ? null : '%'.$cleaned.'%';
    }

    /**
     * @param  Collection<int, Post>  $posts
     */
    private function attachAuthors(Collection $posts): void
    {
        $authors = User::whereIn('id', $posts->pluck('user_id')->unique()->filter())
            ->get(['id', 'name'])
            ->keyBy('id');

        $posts->each(fn (Post $post) => $post->setRelation('user', $authors->get($post->user_id)));
    }
}
