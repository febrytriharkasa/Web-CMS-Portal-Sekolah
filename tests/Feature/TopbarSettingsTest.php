<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopbarSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_topbar_links(): void
    {
        $admin = User::factory()->create();

        $links = [
            ['label' => 'Daftar PPDB', 'url' => 'https://ppdb.smaxyz.sch.id'],
            ['label' => 'Kalender Akademik', 'url' => '/kalender-akademik'],
        ];

        $response = $this->actingAs($admin)
            ->post('/admin/settings/topbar', [
                'topbar_links' => $links,
            ]);

        $response->assertRedirect();

        $saved = json_decode(SiteSetting::where('key', 'topbar_links')->value('value'), true);
        $this->assertCount(2, $saved);
        $this->assertEquals('Daftar PPDB', $saved[0]['label']);
        $this->assertEquals('https://ppdb.smaxyz.sch.id', $saved[0]['url']);
        $this->assertEquals('/kalender-akademik', $saved[1]['url']);
    }

    public function test_guest_cannot_update_topbar_links(): void
    {
        $response = $this->post('/admin/settings/topbar', [
            'topbar_links' => [
                ['label' => 'Test', 'url' => '/test'],
            ],
        ]);

        $response->assertRedirect('/login');
    }
}
