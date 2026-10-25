<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class GalleryRouteTest extends TestCase
{
    use RefreshDatabase;

    private function galleryCategory(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'galeri'],
            ['name' => 'Galeri', 'preview_mode' => 'media'],
        );
    }

    private function createPost(User $user, Category $category, array $attributes = []): Post
    {
        return Post::create(array_merge([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Kegiatan Sekolah',
            'slug' => 'kegiatan-'.uniqid(),
            'content' => 'Dokumentasi kegiatan.',
            'status' => 'published',
            'published_at' => now(),
        ], $attributes));
    }

    public function test_gallery_page_renders_with_counts(): void
    {
        $user = User::factory()->create();
        $category = $this->galleryCategory();

        $this->createPost($user, $category, [
            'title' => 'Foto Upacara',
            'media_type' => 'image',
        ]);

        $this->createPost($user, $category, [
            'title' => 'Video Pentas Seni',
            'media_type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->createPost($user, $category, [
            'title' => 'Foto Tanpa Tipe',
            'media_type' => null,
        ]);

        $this->createPost($user, $category, [
            'title' => 'Draf Galeri',
            'status' => 'draft',
            'media_type' => 'image',
        ]);

        $response = $this->get('/galeri');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/Gallery')
            ->where('media', null)
            ->where('counts.all', 3)
            ->where('counts.image', 2)
            ->where('counts.video', 1)
            ->has('items.data', 3)
            ->where('items.per_page', 8)
            ->where('items.data.0.category.slug', 'galeri')
        );
    }

    public function test_gallery_filters_images_and_videos(): void
    {
        $user = User::factory()->create();
        $category = $this->galleryCategory();

        $this->createPost($user, $category, [
            'title' => 'Foto Kegiatan',
            'media_type' => 'image',
        ]);

        $this->createPost($user, $category, [
            'title' => 'Video Kegiatan',
            'media_type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->get('/galeri?media=video')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Gallery')
                ->where('media', 'video')
                ->where('items.total', 1)
                ->where('items.data.0.title', 'Video Kegiatan')
            );

        $this->get('/galeri?media=image')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('media', 'image')
                ->where('items.total', 1)
                ->where('items.data.0.title', 'Foto Kegiatan')
            );
    }

    public function test_gallery_ignores_unknown_media_filter(): void
    {
        $user = User::factory()->create();
        $category = $this->galleryCategory();

        $this->createPost($user, $category, ['media_type' => 'image']);

        $this->get('/galeri?media=audio')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('media', null)
                ->where('items.total', 1)
            );
    }

    public function test_other_categories_still_render_the_archive_template(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'prestasi'],
            ['name' => 'Prestasi'],
        );

        $this->get('/'.$category->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Public/Archive'));
    }
}
