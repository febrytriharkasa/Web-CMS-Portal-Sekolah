<?php

namespace Tests\Feature;

use App\Http\Middleware\ForceHttps;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Support\UploadSecurity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_message_content_is_encrypted_at_rest(): void
    {
        $session = ChatSession::create(['session_token' => (string) \Illuminate\Support\Str::uuid(), 'created_at' => now()]);
        $message = ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'user',
            'content' => 'Data sensitif saya',
            'created_at' => now(),
        ]);

        $raw = DB::table('chat_messages')->where('id', $message->id)->value('content');

        $this->assertNotSame('Data sensitif saya', $raw);
        $this->assertSame('Data sensitif saya', $message->fresh()->content);
    }

    public function test_force_https_middleware_redirects_in_production(): void
    {
        $middleware = new ForceHttps;

        $localRequest = Request::create('http://example.test/berita');
        $response = $middleware->handle($localRequest, fn () => response('ok'));
        $this->assertSame(200, $response->getStatusCode());

        $this->app->detectEnvironment(fn () => 'production');

        $prodRequest = Request::create('http://example.test/berita');
        $prodResponse = $middleware->handle($prodRequest, fn () => response('ok'));
        $this->assertSame(301, $prodResponse->getStatusCode());
        $this->assertStringStartsWith('https://', $prodResponse->headers->get('Location'));
    }

    public function test_svg_sanitizer_removes_dangerous_elements(): void
    {
        $clean = UploadSecurity::sanitizeSvg('<svg xmlns="http://www.w3.org/2000/svg"><circle r="5"/></svg>');
        $this->assertStringContainsString('<circle', $clean);

        $this->expectException(RuntimeException::class);
        UploadSecurity::sanitizeSvg('<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
    }

    public function test_svg_sanitizer_blocks_event_handlers(): void
    {
        $this->expectException(RuntimeException::class);
        UploadSecurity::sanitizeSvg('<svg onload="alert(1)" xmlns="http://www.w3.org/2000/svg"></svg>');
    }

    public function test_svg_sanitizer_blocks_javascript_urls(): void
    {
        $this->expectException(RuntimeException::class);
        UploadSecurity::sanitizeSvg('<svg xmlns="http://www.w3.org/2000/svg"><a href="javascript:alert(1)"><text>x</text></a></svg>');
    }
}
