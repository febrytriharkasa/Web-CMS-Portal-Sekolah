<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SidebarCtaSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_sidebar_cta_settings(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/settings/sidebar-cta', [
            'sidebar_cta_enabled' => true,
            'sidebar_cta_title' => 'PPDB 2027 / 2028',
            'sidebar_cta_heading' => 'Bergabung Bersama Generasi Unggul SMA XYZ',
            'sidebar_cta_description' => 'Pendaftaran Gelombang 1 dibuka sampai 30 Juni.',
            'sidebar_cta_button_text' => 'Daftar Sekarang',
            'sidebar_cta_button_url' => 'https://ppdb.smaxyz.sch.id',
        ]);

        $response->assertRedirect();

        $this->assertSame('1', SiteSetting::where('key', 'sidebar_cta_enabled')->value('value'));
        $this->assertSame('PPDB 2027 / 2028', SiteSetting::where('key', 'sidebar_cta_title')->value('value'));
        $this->assertSame('https://ppdb.smaxyz.sch.id', SiteSetting::where('key', 'sidebar_cta_button_url')->value('value'));
    }

    public function test_disabled_sidebar_cta_is_saved_as_zero(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/admin/settings/sidebar-cta', [
            'sidebar_cta_enabled' => false,
            'sidebar_cta_heading' => 'Bergabung Bersama Generasi Unggul SMA XYZ',
        ])->assertRedirect();

        $this->assertSame('0', SiteSetting::where('key', 'sidebar_cta_enabled')->value('value'));
    }

    public function test_sidebar_cta_page_renders_for_admin(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get('/admin/settings/sidebar-cta')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/SidebarCta')
                ->has('linkOptions')
            );
    }

    public function test_guest_cannot_update_sidebar_cta_settings(): void
    {
        $this->post('/admin/settings/sidebar-cta', ['sidebar_cta_enabled' => true])
            ->assertRedirect('/login');
    }

    public function test_post_detail_receives_sidebar_cta_values(): void
    {
        $user = User::factory()->create();
        $category = Category::firstOrCreate(['slug' => 'berita'], ['name' => 'Berita']);

        Post::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Judul Berita',
            'slug' => 'judul-berita',
            'content' => 'Isi berita.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        SiteSetting::updateOrCreate(['key' => 'sidebar_cta_heading'], ['value' => 'Bergabung Bersama Generasi Unggul SMA XYZ']);
        SiteSetting::updateOrCreate(['key' => 'sidebar_cta_button_url'], ['value' => 'https://ppdb.smaxyz.sch.id']);

        $this->get('/berita/judul-berita')->assertInertia(fn (Assert $page) => $page
            ->where('settings.sidebar_cta_heading', 'Bergabung Bersama Generasi Unggul SMA XYZ')
            ->where('settings.sidebar_cta_button_url', 'https://ppdb.smaxyz.sch.id')
        );
    }
}
