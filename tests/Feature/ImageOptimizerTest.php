<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_jpeg_is_stored_as_webp_and_downscaled(): void
    {
        Storage::fake('public');

        $path = ImageOptimizer::storeOptimized(
            UploadedFile::fake()->image('foto.jpg', 2400, 1200),
            'posts',
            maxWidth: 1600,
        );

        Storage::disk('public')->assertExists($path);
        $this->assertStringEndsWith('.webp', $path);

        [$width, $height] = getimagesize(Storage::disk('public')->path($path));

        $this->assertSame(1600, $width);
        $this->assertSame(800, $height);
    }

    public function test_small_image_keeps_its_original_dimensions(): void
    {
        Storage::fake('public');

        $path = ImageOptimizer::storeOptimized(
            UploadedFile::fake()->image('kecil.png', 320, 240),
            'pages',
        );

        [$width, $height] = getimagesize(Storage::disk('public')->path($path));

        $this->assertSame(320, $width);
        $this->assertSame(240, $height);
    }

    public function test_svg_is_stored_as_is(): void
    {
        Storage::fake('public');

        $path = ImageOptimizer::storeOptimized(
            UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            'settings',
        );

        $this->assertStringEndsWith('.svg', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_undecodable_image_falls_back_to_raw_upload(): void
    {
        Storage::fake('public');

        $path = ImageOptimizer::storeOptimized(
            UploadedFile::fake()->createWithContent('rusak.jpg', 'bukan gambar'),
            'posts',
        );

        $this->assertStringEndsWith('.jpg', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_post_featured_image_upload_is_converted_to_webp(): void
    {
        Storage::fake('public');
        $category = Category::firstOrCreate(['slug' => 'berita'], ['name' => 'Berita']);

        $this->actingAs(User::factory()->create())->post('/admin/posts', [
            'title' => 'Berita Dengan Foto',
            'category_id' => $category->id,
            'content' => '<p>Isi berita</p>',
            'status' => 'published',
            'media_type' => 'image',
            'featured_image' => UploadedFile::fake()->image('foto.jpg', 2000, 1000),
        ])->assertRedirect();

        $featured = Post::where('title', 'Berita Dengan Foto')->value('featured_image');

        $this->assertStringEndsWith('.webp', $featured);
    }

    public function test_site_logo_upload_is_converted_to_webp(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create())->post('/admin/settings/footer', [
            'site_name' => 'SMA XYZ',
            'site_logo_file' => UploadedFile::fake()->image('logo.png', 300, 300),
        ])->assertRedirect();

        $logo = SiteSetting::where('key', 'site_logo')->value('value');

        $this->assertStringEndsWith('.webp', $logo);
    }
}
