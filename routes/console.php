<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Satu kali jalan setelah memasang cast "encrypted": enkripsi isi chat lama
// yang masih plaintext. Query langsung ke DB supaya cast tidak ikut mendekripsi.
Artisan::command('chat:encrypt-messages', function () {
    $rows = DB::table('chat_messages')
        ->whereNotNull('content')
        ->where('content', '<>', '')
        ->select('id', 'content')
        ->get();

    $encrypted = 0;
    $skipped = 0;

    foreach ($rows as $row) {
        $content = (string) $row->content;

        // Ciphertext Laravel selalu base64 JSON dan diawali "eyJ". Cek prefix
        // dulu supaya isi yang sudah terenkripsi tidak dienkripsi dua kali.
        if (Str::startsWith($content, 'eyJ')) {
            try {
                Crypt::decryptString($content);
            } catch (DecryptException) {
                $skipped++;
                $this->warn("Pesan #{$row->id} dilewati: terlihat terenkripsi tapi gagal didekripsi (APP_KEY berbeda?).");
            }

            continue;
        }

        DB::table('chat_messages')->where('id', $row->id)->update([
            'content' => Crypt::encryptString($content),
        ]);

        $encrypted++;
    }

    $this->info("{$encrypted} pesan chat dienkripsi.");

    if ($skipped > 0) {
        $this->warn("{$skipped} pesan dilewati.");
    }
})->purpose('Enkripsi isi chat_messages yang masih plaintext');

/*
 * Backup harian: dump database + storage/app/public ke disk "backups".
 * Butuh cron server: * * * * * php artisan schedule:run
 */
Schedule::command('backup:run')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('backup:clean')->dailyAt('01:30')->withoutOverlapping();
Schedule::command('backup:monitor')->dailyAt('02:00')->withoutOverlapping();
