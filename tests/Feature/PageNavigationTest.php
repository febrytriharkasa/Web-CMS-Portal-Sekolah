<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PageNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_can_be_placed_as_top_level_nav_item(): void
    {
        $this->seedNav([['type' => 'single', 'label' => 'Beranda', 'url' => '/']]);

        $this->actingAs(User::factory()->create())->post('/admin/pages', [
            'title' => 'Visi, Misi dan Tujuan',
            'content' => 'Visi, Misi dan Tujuan sekolah.',
            'is_published' => true,
            'nav_placement' => 'top',
            'nav_label' => 'Visi, Misi dan Tujuan',
        ])->assertRedirect(route('admin.pages.index'));

        $items = $this->navItems();

        $this->assertCount(2, $items);
        $this->assertSame('Visi, Misi dan Tujuan', $items[1]['label']);
        $this->assertSame('/halaman-visi-misi-dan-tujuan', $items[1]['url']);
        $this->assertSame('single', $items[1]['type']);
    }

    public function test_page_label_falls_back_to_title(): void
    {
        $this->seedNav([]);

        $this->actingAs(User::factory()->create())->post('/admin/pages', [
            'title' => 'Visi, Misi dan Tujuan',
            'content' => 'Visi, Misi dan Tujuan sekolah.',
            'is_published' => true,
            'nav_placement' => 'top',
        ])->assertRedirect(route('admin.pages.index'));

        $this->assertSame('Visi, Misi dan Tujuan', $this->navItems()[0]['label']);
    }

    public function test_page_can_be_placed_inside_existing_dropdown(): void
    {
        $this->seedNav([
            ['type' => 'single', 'label' => 'Beranda', 'url' => '/'],
            [
                'type' => 'dropdown',
                'label' => 'Tentang Kami',
                'children' => [['label' => 'Sejarah', 'url' => '/sejarah']],
            ],
        ]);

        $this->actingAs(User::factory()->create())->post('/admin/pages', [
            'title' => 'Visi, Misi dan Tujuan',
            'content' => 'Visi, Misi dan Tujuan sekolah.',
            'is_published' => true,
            'nav_placement' => 'dropdown',
            'nav_parent_label' => 'Tentang Kami',
        ])->assertRedirect(route('admin.pages.index'));

        $items = $this->navItems();

        $this->assertCount(2, $items);
        $this->assertCount(2, $items[1]['children']);
        $this->assertSame('Sejarah', $items[1]['children'][0]['label']);
        $this->assertSame('Visi, Misi dan Tujuan', $items[1]['children'][1]['label']);
        $this->assertSame('/halaman-visi-misi-dan-tujuan', $items[1]['children'][1]['url']);
    }

    public function test_dropdown_placement_fails_when_dropdown_is_gone(): void
    {
        $this->seedNav([['type' => 'single', 'label' => 'Beranda', 'url' => '/']]);

        $this->actingAs(User::factory()->create())->post('/admin/pages', [
            'title' => 'Visi, Misi dan Tujuan',
            'content' => 'Visi, Misi dan Tujuan sekolah.',
            'is_published' => true,
            'nav_placement' => 'dropdown',
            'nav_parent_label' => 'Tentang Kami',
        ])->assertSessionHasErrors('nav_parent_label');

        $this->assertCount(1, $this->navItems());
    }

    public function test_edit_form_receives_dropdown_options_and_current_placement(): void
    {
        $this->seedNav([
            ['type' => 'single', 'label' => 'Beranda', 'url' => '/'],
            [
                'type' => 'dropdown',
                'label' => 'Tentang Kami',
                'children' => [['label' => 'Visi Sekolah', 'url' => '/visi-misi-tujuan']],
            ],
        ]);

        $page = $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan');

        $this->actingAs(User::factory()->create())
            ->get("/admin/pages/{$page->id}/edit")
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->component('Admin/Pages/Form')
                ->where('navPlacement.placement', 'dropdown')
                ->where('navPlacement.parent_label', 'Tentang Kami')
                ->where('navPlacement.label', 'Visi Sekolah')
                ->where('navDropdowns.0.label', 'Tentang Kami')
            );
    }

    public function test_edit_form_reports_page_that_is_not_in_nav(): void
    {
        $this->seedNav([['type' => 'single', 'label' => 'Beranda', 'url' => '/']]);

        $page = $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan');

        $this->actingAs(User::factory()->create())
            ->get("/admin/pages/{$page->id}/edit")
            ->assertInertia(fn (Assert $assert) => $assert
                ->component('Admin/Pages/Form')
                ->where('navPlacement.placement', 'none')
                ->has('navDropdowns', 0)
            );
    }

    public function test_show_page_receives_nav_placement(): void
    {
        $this->seedNav([
            ['type' => 'single', 'label' => 'Beranda', 'url' => '/'],
            [
                'type' => 'dropdown',
                'label' => 'Tentang Kami',
                'children' => [['label' => 'Visi Sekolah', 'url' => '/visi-misi-tujuan']],
            ],
        ]);

        $page = $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan');

        $this->actingAs(User::factory()->create())
            ->get("/admin/pages/{$page->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->component('Admin/Pages/Show')
                ->where('navPlacement.placement', 'dropdown')
                ->where('navPlacement.parent_label', 'Tentang Kami')
                ->where('navPlacement.label', 'Visi Sekolah')
            );
    }

    public function test_show_page_reports_page_that_is_not_in_nav(): void
    {
        $this->seedNav([['type' => 'single', 'label' => 'Beranda', 'url' => '/']]);

        $page = $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan');

        $this->actingAs(User::factory()->create())
            ->get("/admin/pages/{$page->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->component('Admin/Pages/Show')
                ->where('navPlacement.placement', 'none')
            );
    }

    public function test_index_marks_pages_already_in_navbar(): void
    {
        $this->seedNav([
            ['type' => 'single', 'label' => 'Beranda', 'url' => '/'],
            ['type' => 'single', 'label' => 'Fasilitas', 'url' => '/fasilitas'],
            [
                'type' => 'dropdown',
                'label' => 'Tentang Kami',
                'children' => [['label' => 'Sejarah', 'url' => '/sejarah']],
            ],
        ]);

        $this->page('Sejarah', 'sejarah');
        $this->page('Fasilitas', 'fasilitas');
        $this->page('Program Tahfidz', 'program-tahfidz');

        $this->actingAs(User::factory()->create())
            ->get('/admin/pages')
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->component('Admin/Pages/Index')
                ->where('pages.data', function ($pages) {
                    $bySlug = collect($pages)->keyBy('slug');

                    return $bySlug['sejarah']['in_nav'] === true
                        && $bySlug['fasilitas']['in_nav'] === true
                        && $bySlug['program-tahfidz']['in_nav'] === false;
                })
            );
    }

    public function test_moving_page_out_of_nav_removes_its_entry(): void
    {
        $this->seedNav([
            ['type' => 'single', 'label' => 'Beranda', 'url' => '/'],
            ['type' => 'single', 'label' => 'Visi, Misi dan Tujuan', 'url' => '/visi-misi-tujuan'],
        ]);

        $page = $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan');

        $this->actingAs(User::factory()->create())->put("/admin/pages/{$page->id}", [
            'title' => 'Visi, Misi dan Tujuan',
            'content' => 'Visi, Misi dan Tujuan sekolah.',
            'is_published' => true,
            'nav_placement' => 'none',
        ])->assertRedirect(route('admin.pages.index'));

        $items = $this->navItems();

        $this->assertCount(1, $items);
        $this->assertSame('Beranda', $items[0]['label']);
    }

    public function test_moving_page_between_menu_levels_keeps_one_entry(): void
    {
        $this->seedNav([
            ['type' => 'single', 'label' => 'Visi, Misi dan Tujuan', 'url' => '/visi-misi-tujuan'],
            [
                'type' => 'dropdown',
                'label' => 'Tentang Kami',
                'children' => [['label' => 'Sejarah', 'url' => '/sejarah']],
            ],
        ]);

        $page = $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan');

        $this->actingAs(User::factory()->create())->put("/admin/pages/{$page->id}", [
            'title' => 'Visi, Misi dan Tujuan',
            'content' => 'Visi, Misi dan Tujuan sekolah.',
            'is_published' => true,
            'nav_placement' => 'dropdown',
            'nav_parent_label' => 'Tentang Kami',
            'nav_label' => 'Visi, Misi dan Tujuan',
        ])->assertRedirect(route('admin.pages.index'));

        $items = $this->navItems();

        $this->assertCount(1, $items);
        $this->assertCount(2, $items[0]['children']);
        $this->assertSame('Visi, Misi dan Tujuan', $items[0]['children'][1]['label']);
        $this->assertSame('/visi-misi-tujuan', $items[0]['children'][1]['url']);
    }

    public function test_status_update_keeps_nav_entry(): void
    {
        $this->seedNav([['type' => 'single', 'label' => 'Visi, Misi dan Tujuan', 'url' => '/visi-misi-tujuan']]);

        $page = $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan');

        $this->actingAs(User::factory()->create())
            ->patch("/admin/pages/{$page->id}/status", ['is_published' => false])
            ->assertRedirect();

        $this->assertSame('/visi-misi-tujuan', $this->navItems()[0]['url']);
    }

    public function test_deleting_page_removes_its_nav_entry(): void
    {
        $this->seedNav([
            ['type' => 'single', 'label' => 'Beranda', 'url' => '/'],
            [
                'type' => 'dropdown',
                'label' => 'Tentang Kami',
                'children' => [
                    ['label' => 'Sejarah', 'url' => '/sejarah'],
                    ['label' => 'Visi, Misi dan Tujuan', 'url' => '/visi-misi-tujuan'],
                ],
            ],
        ]);

        $page = $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan');

        $this->actingAs(User::factory()->create())
            ->delete("/admin/pages/{$page->id}")
            ->assertRedirect(route('admin.pages.index'));

        $items = $this->navItems();

        $this->assertCount(2, $items);
        $this->assertCount(1, $items[1]['children']);
        $this->assertSame('Sejarah', $items[1]['children'][0]['label']);
    }

    public function test_draft_page_link_is_hidden_from_public_nav(): void
    {
        $this->seedNav([
            ['type' => 'single', 'label' => 'Beranda', 'url' => '/'],
            ['type' => 'single', 'label' => 'Visi, Misi dan Tujuan', 'url' => '/visi-misi-tujuan'],
            [
                'type' => 'dropdown',
                'label' => 'Tentang Kami',
                'children' => [['label' => 'Visi, Misi dan Tujuan', 'url' => '/visi-misi-tujuan']],
            ],
        ]);

        $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan', published: false);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->component('Welcome')
                ->has('navItems', 1)
                ->where('navItems.0.label', 'Beranda')
                ->where('draftPageSlugs', ['visi-misi-tujuan'])
            );
    }

    public function test_published_page_link_stays_in_public_nav(): void
    {
        $this->seedNav([
            ['type' => 'single', 'label' => 'Beranda', 'url' => '/'],
            ['type' => 'single', 'label' => 'Visi, Misi dan Tujuan', 'url' => '/visi-misi-tujuan'],
        ]);

        $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan');

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->has('navItems', 2)
                ->where('navItems.1.url', '/visi-misi-tujuan')
            );
    }

    public function test_draft_page_is_not_reachable_for_visitors(): void
    {
        $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan', published: false);

        $this->get('/visi-misi-tujuan')->assertNotFound();
        $this->get('/visi-misi-tujuan?preview=1')->assertNotFound();
    }

    public function test_admin_can_preview_draft_page(): void
    {
        $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan', published: false);

        $this->actingAs(User::factory()->create())
            ->get('/visi-misi-tujuan?preview=1')
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->component('Public/Page')
                ->where('draftPreview', true)
            );
    }

    public function test_admin_preview_flag_does_not_expose_draft_without_query(): void
    {
        $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan', published: false);

        $this->actingAs(User::factory()->create())
            ->get('/visi-misi-tujuan')
            ->assertNotFound();
    }

    public function test_public_page_has_no_preview_banner(): void
    {
        $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan');

        $this->get('/visi-misi-tujuan')
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->component('Public/Page')
                ->where('draftPreview', false)
            );
    }

    public function test_navigation_settings_marks_draft_pages(): void
    {
        $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan', published: false);
        $this->page('Sejarah', 'sejarah');

        $this->actingAs(User::factory()->create())
            ->get('/admin/settings/navigation')
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->component('Admin/Settings/Navigation')
                ->has('linkOptions')
                ->where('linkOptions', function ($options) {
                    $labels = collect($options)->pluck('label')->all();

                    return in_array('Laman: Visi, Misi dan Tujuan (draf)', $labels, true)
                        && in_array('Laman: Sejarah', $labels, true);
                })
            );
    }

    public function test_footer_settings_only_offer_published_pages(): void
    {
        $this->page('Visi, Misi dan Tujuan', 'visi-misi-tujuan', published: false);
        $this->page('Sejarah', 'sejarah');

        $this->actingAs(User::factory()->create())
            ->get('/admin/settings/footer')
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->where('linkOptions', function ($options) {
                    $labels = collect($options)->pluck('label')->all();

                    return in_array('Laman: Sejarah', $labels, true)
                        && ! in_array('Laman: Visi, Misi dan Tujuan (draf)', $labels, true);
                })
            );
    }

    public function test_navigation_settings_accept_dropdown_without_children(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/admin/settings/navigation', [
                'nav_items' => [
                    ['type' => 'dropdown', 'label' => 'Tentang Kami', 'children' => []],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame([], $this->navItems()[0]['children']);
    }

    public function test_half_finished_nav_entries_stay_out_of_public_nav(): void
    {
        $this->seedNav([
            ['type' => 'single', 'label' => 'Beranda', 'url' => '/'],
            ['type' => 'dropdown', 'label' => null, 'url' => null, 'children' => null],
            ['type' => 'dropdown', 'label' => 'Kosong', 'url' => null, 'children' => []],
            ['type' => 'single', 'label' => '', 'url' => '/tanpa-label'],
            [
                'type' => 'dropdown',
                'label' => 'Isi Bolong',
                'url' => null,
                'children' => [
                    ['label' => '', 'url' => '/tanpa-label'],
                    ['label' => 'Sejarah', 'url' => ''],
                ],
            ],
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->has('navItems', 1)
                ->where('navItems.0.label', 'Beranda')
                ->where('navConfigured', true)
            );
    }

    public function test_empty_menu_keeps_built_in_navbar(): void
    {
        $this->seedNav([]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->has('navItems', 0)
                ->where('navConfigured', false)
            );
    }

    public function test_page_form_ignores_unlabeled_dropdowns(): void
    {
        $this->seedNav([
            [
                'type' => 'dropdown',
                'label' => '',
                'children' => [['label' => 'Sejarah', 'url' => '/sejarah']],
            ],
            [
                'type' => 'dropdown',
                'label' => 'Tentang Kami',
                'children' => [['label' => 'Sejarah', 'url' => '/sejarah']],
            ],
        ]);

        $this->actingAs(User::factory()->create())
            ->get('/admin/pages/create')
            ->assertOk()
            ->assertInertia(fn (Assert $assert) => $assert
                ->component('Admin/Pages/Form')
                ->has('navDropdowns', 1)
                ->where('navDropdowns.0.label', 'Tentang Kami')
            );
    }

    public function test_page_form_handles_dropdown_with_null_children(): void
    {
        $this->seedNav([
            ['type' => 'dropdown', 'label' => 'Tentang Kami', 'url' => null, 'children' => null],
        ]);

        $this->actingAs(User::factory()->create())->post('/admin/pages', [
            'title' => 'Visi, Misi dan Tujuan',
            'content' => 'Visi, Misi dan Tujuan sekolah.',
            'is_published' => true,
            'nav_placement' => 'dropdown',
            'nav_parent_label' => 'Tentang Kami',
        ])->assertRedirect(route('admin.pages.index'));

        $this->assertSame(
            ['label' => 'Visi, Misi dan Tujuan', 'url' => '/halaman-visi-misi-dan-tujuan'],
            $this->navItems()[0]['children'][0],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function seedNav(array $items): void
    {
        SiteSetting::create([
            'key' => 'nav_items',
            'value' => json_encode($items),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function navItems(): array
    {
        return json_decode((string) SiteSetting::where('key', 'nav_items')->value('value'), true) ?? [];
    }

    private function page(string $title, string $slug, bool $published = true): Page
    {
        return Page::create([
            'title' => $title,
            'slug' => $slug,
            'content' => 'Isi '.$title,
            'is_published' => $published,
        ]);
    }
}
