<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatSession;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Chatbot\SchoolContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatBotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SiteSetting::updateOrCreate(['key' => 'chat_enabled'], ['value' => '1']);
    }

    public function test_chat_disabled_returns_403(): void
    {
        SiteSetting::where('key', 'chat_enabled')->update(['value' => '0']);

        $response = $this->postJson('/api/chat', ['message' => 'Halo']);

        $response->assertForbidden();
    }

    public function test_message_is_validated(): void
    {
        $this->postJson('/api/chat', [])->assertUnprocessable();
        $this->postJson('/api/chat', ['message' => str_repeat('a', 1001)])->assertUnprocessable();
        $this->postJson('/api/chat', ['session_token' => 'bukan-uuid', 'message' => 'Halo'])->assertUnprocessable();
    }

    public function test_ai_reply_is_saved_and_returned(): void
    {
        config(['chatbot.driver' => 'auto']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'Jawaban dari AI.']]],
            ], 200),
        ]);

        $response = $this->postJson('/api/chat', ['message' => 'Kapan PPDB dibuka?']);

        $response->assertOk()->assertJson(['reply' => 'Jawaban dari AI.']);

        $session = ChatSession::where('session_token', $response->json('session_token'))->first();
        $this->assertNotNull($session);
        $this->assertCount(2, $session->messages);

        Http::assertSent(function ($request) {
            $messages = $request->data()['messages'] ?? [];

            return collect($messages)->contains(fn ($m) => $m['role'] === 'system'
                && str_contains($m['content'], 'SMA'));
        });
    }

    public function test_provider_failure_falls_back_to_keyword_answer(): void
    {
        config(['chatbot.driver' => 'auto']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([], 500)]);

        $response = $this->postJson('/api/chat', ['message' => 'Informasi PPDB']);

        $response->assertOk();
        $this->assertStringContainsString('PPDB', $response->json('reply'));
    }

    public function test_missing_api_key_uses_fallback(): void
    {
        config([
            'chatbot.driver' => 'auto',
            'chatbot.providers' => [
                'gemini' => ['base_url' => 'https://example.test', 'model' => 'x', 'api_key' => null],
                'groq' => ['base_url' => 'https://example.test', 'model' => 'y', 'api_key' => ''],
            ],
        ]);

        $response = $this->postJson('/api/chat', ['message' => 'Lokasi sekolah']);

        $response->assertOk();
        $this->assertStringContainsString('Krian', $response->json('reply'));

        Http::assertSentCount(0);
    }

    public function test_driver_off_disables_llm_calls(): void
    {
        config(['chatbot.driver' => 'off']);

        $response = $this->postJson('/api/chat', ['message' => 'Info kontak']);

        $response->assertOk();
        $this->assertStringContainsString('sekretariat', $response->json('reply'));

        Http::assertSentCount(0);
    }

    public function test_rate_limit_blocks_flood(): void
    {
        config(['chatbot.driver' => 'off']);

        for ($i = 0; $i < 8; $i++) {
            $this->postJson('/api/chat', ['message' => 'Halo']);
        }

        $this->postJson('/api/chat', ['message' => 'Halo'])->assertTooManyRequests();
    }

    public function test_history_is_limited_to_config(): void
    {
        config(['chatbot.driver' => 'auto']);
        config(['chatbot.history_limit' => 8]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'ok']]],
            ], 200),
        ]);

        $token = (string) Str::uuid();
        $session = ChatSession::create(['session_token' => $token, 'created_at' => now()]);

        for ($i = 0; $i < 12; $i++) {
            $session->messages()->create(['role' => 'user', 'content' => "Pesan {$i}", 'created_at' => now()]);
            $session->messages()->create(['role' => 'assistant', 'content' => "Balasan {$i}", 'created_at' => now()]);
        }

        $this->postJson('/api/chat', ['session_token' => $token, 'message' => 'Halo']);

        Http::assertSent(function ($request) {
            $nonSystem = collect($request->data()['messages'] ?? [])
                ->reject(fn ($m) => $m['role'] === 'system');

            return $nonSystem->count() === 9; // 8 history + pesan baru
        });
    }

    public function test_daily_budget_exhausted_uses_fallback(): void
    {
        config(['chatbot.driver' => 'auto']);
        config(['chatbot.daily_limit' => 0]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'ok']]],
            ], 200),
        ]);

        $response = $this->postJson('/api/chat', ['message' => 'Halo']);

        $response->assertOk();

        Http::assertSentCount(0);
    }

    public function test_fallback_response_is_saved_when_all_providers_fail(): void
    {
        config(['chatbot.driver' => 'auto']);
        Http::fake(['*' => Http::response([], 500)]);

        $response = $this->postJson('/api/chat', ['message' => 'aduhai']);

        $response->assertOk();

        $session = ChatSession::where('session_token', $response->json('session_token'))->first();
        $this->assertCount(2, $session->messages);
    }

    public function test_latest_news_fallback_lists_newest_post_first_with_url(): void
    {
        config(['chatbot.driver' => 'off']);

        $user = User::factory()->create();
        $category = Category::firstOrCreate(['slug' => 'berita'], ['name' => 'Berita']);

        $older = $this->createBerita($user, $category, 'Kunjungan Edukasi ke ITS', '2026-10-18');
        $newer = $this->createBerita($user, $category, 'Pelaksanaan ANBK 2026', '2026-10-20');

        $response = $this->postJson('/api/chat', ['message' => 'Berita paling terbaru?']);

        $response->assertOk();
        $reply = $response->json('reply');

        $this->assertStringContainsString($newer->title, $reply);
        $this->assertStringContainsString('/berita/pelaksanaan-anbk-2026', $reply);
        $this->assertLessThan(
            strpos($reply, $older->title),
            strpos($reply, $newer->title),
            'Berita terbaru harus disebut lebih dulu.'
        );
    }

    public function test_latest_news_retrieval_orders_posts_by_published_date(): void
    {
        $user = User::factory()->create();
        $category = Category::firstOrCreate(['slug' => 'berita'], ['name' => 'Berita']);

        $older = $this->createBerita($user, $category, 'Kunjungan Edukasi ke ITS', '2026-10-18');
        $newer = $this->createBerita($user, $category, 'Pelaksanaan ANBK 2026', '2026-10-20');

        $prompt = SchoolContext::for('berita terbaru');

        $this->assertStringContainsString('[2026-10-20] '.$newer->title, $prompt);
        $this->assertStringContainsString(SchoolContext::postUrl($newer), $prompt);
        $this->assertLessThan(
            strpos($prompt, $older->title),
            strpos($prompt, $newer->title),
            'Konteks harus menaruh berita terbaru di depan.'
        );
    }

    public function test_social_media_question_lists_configured_links(): void
    {
        config(['chatbot.driver' => 'off']);

        SiteSetting::updateOrCreate(['key' => 'social_instagram'], ['value' => 'https://instagram.com/smaliska']);
        SiteSetting::updateOrCreate(['key' => 'social_whatsapp'], ['value' => 'https://wa.me/628123456789']);

        $response = $this->postJson('/api/chat', ['message' => 'Apa Instagram dan WA sekolah?']);

        $response->assertOk();
        $reply = $response->json('reply');

        $this->assertStringContainsString('https://instagram.com/smaliska', $reply);
        $this->assertStringContainsString('https://wa.me/628123456789', $reply);
    }

    public function test_social_media_question_without_links_is_honest(): void
    {
        config(['chatbot.driver' => 'off']);

        $response = $this->postJson('/api/chat', ['message' => 'Nomor WA sekolah berapa?']);

        $response->assertOk();
        $reply = $response->json('reply');

        $this->assertStringContainsString('belum dicantumkan', $reply);
        $this->assertStringNotContainsString('instagram.com', $reply);
    }

    public function test_social_word_boundary_does_not_hijack_other_questions(): void
    {
        config(['chatbot.driver' => 'off']);

        $response = $this->postJson('/api/chat', ['message' => 'Apa kewajiban siswa di sekolah?']);

        $response->assertOk();

        $this->assertStringNotContainsString('Media sosial resmi', $response->json('reply'));
    }

    public function test_system_prompt_includes_social_media_links(): void
    {
        SiteSetting::updateOrCreate(['key' => 'social_instagram'], ['value' => 'https://instagram.com/smaliska']);

        $prompt = SchoolContext::for('apa instagram sekolah');

        $this->assertStringContainsString('https://instagram.com/smaliska', $prompt);
    }

    private function createBerita(User $user, Category $category, string $title, string $date): Post
    {
        return Post::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => 'Isi berita.',
            'status' => 'published',
            'published_at' => $date.' 08:00:00',
        ]);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
