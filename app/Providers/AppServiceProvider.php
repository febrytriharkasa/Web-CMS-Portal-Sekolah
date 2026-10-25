<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Prefetch hanya di panel admin. Di halaman publik strategi ini mengunduh
        // seluruh chunk dinamis (termasuk editor blok) yang tidak dipakai pengunjung.
        if (request()->is('admin', 'admin/*')) {
            Vite::prefetch(concurrency: 3);
        }

        RateLimiter::for('chat', function (Request $request) {
            $session = $request->input('session_token');

            // Jaringan sekolah memakai satu IP publik, jadi batas per-IP dibuat
            // longgar dan yang ketat dipasang per sesi browser.
            return [
                Limit::perMinute(8)->by('chat-session:'.($session ?: $request->ip())),
                Limit::perDay(60)->by('chat-session-day:'.($session ?: $request->ip())),
                Limit::perMinute(20)->by('chat-ip:'.$request->ip()),
            ];
        });
    }
}
