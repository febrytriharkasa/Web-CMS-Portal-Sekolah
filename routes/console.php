<?php

use App\Models\ChatMessage;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Satu kali jalan setelah memasang cast "encrypted": enkripsi isi chat lama
// yang masih plaintext. Bypass cast saat baca supaya tidak DecryptException.
Artisan::command('chat:encrypt-messages', function () {
    $rows = DB::table('chat_messages')->select('id', 'content')->get();
    $count = 0;

    foreach ($rows as $row) {
        $message = ChatMessage::find($row->id);
        $message->content = $row->content;
        $message->timestamps = false;
        $message->save();
        $count++;
    }

    $this->info("{$count} pesan chat dienkripsi.");
})->purpose('Enkripsi isi chat_messages yang masih plaintext');

/*
 * Backup harian: dump database + storage/app/public ke disk "backups".
 * Butuh cron server: * * * * * php artisan schedule:run
 */
Schedule::command('backup:run')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('backup:clean')->dailyAt('01:30')->withoutOverlapping();
Schedule::command('backup:monitor')->dailyAt('02:00')->withoutOverlapping();
