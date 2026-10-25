<?php

use App\Models\ChatMessage;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

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
