<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_exposes_metrics_and_widgets(): void
    {
        $admin = User::factory()->create();
        $category = Category::create(['name' => 'Berita', 'slug' => 'berita']);

        Post::create([
            'title' => 'Judul Terbit',
            'slug' => 'judul-terbit',
            'content' => 'Isi',
            'category_id' => $category->id,
            'user_id' => $admin->id,
            'status' => 'published',
            'published_at' => now(),
        ]);
        Post::create([
            'title' => 'Judul Draf',
            'slug' => 'judul-draf',
            'content' => 'Isi',
            'category_id' => $category->id,
            'user_id' => $admin->id,
            'status' => 'draft',
        ]);

        Page::create(['title' => 'Visi Misi', 'slug' => 'visi-misi', 'is_published' => true]);
        Staff::create(['name' => 'Guru Satu', 'role' => 'Guru Matematika', 'type' => 'guru']);
        Staff::create(['name' => 'Tendik Satu', 'role' => 'Staf TU', 'type' => 'tendik']);
        SiteSetting::create(['key' => 'site_name', 'value' => 'SMA XYZ']);

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('metrics.total_posts', 2)
                ->where('metrics.published_posts', 1)
                ->where('metrics.draft_posts', 1)
                ->where('metrics.total_pages', 1)
                ->where('metrics.published_pages', 1)
                ->where('metrics.total_staff', 2)
                ->where('metrics.total_guru', 1)
                ->where('metrics.total_tendik', 1)
                ->where('siteInfo.site_name', 'SMA XYZ')
                ->has('latestPosts', 2)
                ->has('latestPages', 1)
            );
    }
}
