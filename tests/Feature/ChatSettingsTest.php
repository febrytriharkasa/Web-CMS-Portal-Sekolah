<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ChatSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_chat_settings(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/settings/chat', [
            'chat_enabled' => true,
            'chat_assistant_name' => 'Asisten SMALISKA',
            'chat_greeting' => 'Assalamu alaikum! Saya {name} dari {site_name}.',
        ]);

        $response->assertRedirect();

        $this->assertSame('1', SiteSetting::where('key', 'chat_enabled')->value('value'));
        $this->assertSame('Asisten SMALISKA', SiteSetting::where('key', 'chat_assistant_name')->value('value'));
        $this->assertSame('Assalamu alaikum! Saya {name} dari {site_name}.', SiteSetting::where('key', 'chat_greeting')->value('value'));
    }

    public function test_disabled_chat_is_saved_as_zero(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/admin/settings/chat', [
            'chat_enabled' => false,
            'chat_assistant_name' => 'Asisten SMALISKA',
        ])->assertRedirect();

        $this->assertSame('0', SiteSetting::where('key', 'chat_enabled')->value('value'));
    }

    public function test_assistant_name_cannot_exceed_100_characters(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->post('/admin/settings/chat', [
            'chat_assistant_name' => str_repeat('a', 101),
        ])->assertSessionHasErrors('chat_assistant_name');
    }

    public function test_chat_settings_page_renders_for_admin(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->get('/admin/settings/chat')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/Chat')
                ->has('groupedSettings')
            );
    }

    public function test_guest_cannot_update_chat_settings(): void
    {
        $this->post('/admin/settings/chat', ['chat_enabled' => true])
            ->assertRedirect('/login');
    }

    public function test_public_pages_share_assistant_name_and_greeting(): void
    {
        SiteSetting::updateOrCreate(['key' => 'chat_assistant_name'], ['value' => 'Asisten SMALISKA']);
        SiteSetting::updateOrCreate(['key' => 'chat_greeting'], ['value' => 'Halo dari {name}']);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->where('settings.chat_assistant_name', 'Asisten SMALISKA')
            ->where('settings.chat_greeting', 'Halo dari {name}')
        );
    }
}
