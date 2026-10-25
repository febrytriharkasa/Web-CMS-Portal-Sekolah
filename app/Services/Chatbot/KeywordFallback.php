<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Str;

/**
 * Jawaban cadangan tanpa LLM. Deterministik: teksnya identik setiap kali,
 * jadi bisa dipakai sebagai patokan pengujian. Semua fakta diambil dari
 * site_settings lewat SchoolContext::profile() supaya tidak berbeda dengan
 * jawaban AI.
 */
class KeywordFallback
{
    public function respond(string $message): string
    {
        $msg = strtolower($message);
        $p = SchoolContext::profile();

        if (str_contains($msg, 'ppdb') || str_contains($msg, 'daftar') || str_contains($msg, 'biaya') || str_contains($msg, 'syarat')) {
            return "Pendaftaran Peserta Didik Baru (PPDB) {$p['name']} saat ini telah dibuka. Pendaftaran dapat diakses daring melalui {$p['ppdb']} atau datang langsung ke sekretariat pendaftaran di kampus sekolah setiap hari kerja.";
        }

        if (str_contains($msg, 'alamat') || str_contains($msg, 'lokasi') || str_contains($msg, 'dimana')) {
            return "{$p['name']} berlokasi di {$p['address']}.";
        }

        if (self::mentionsSocial($msg)) {
            if ($p['socials']) {
                $list = collect($p['socials'])->map(fn ($url, $label) => "{$label} {$url}")->join('; ');

                return "Media sosial resmi {$p['name']}: {$list}.";
            }

            return "Akun media sosial resmi {$p['name']} belum dicantumkan di website. Silakan hubungi sekretariat di {$p['phone']} pada jam operasional {$p['hours']}.";
        }

        if (str_contains($msg, 'kontak') || str_contains($msg, 'telepon') || str_contains($msg, 'email')) {
            return "Anda dapat menghubungi sekretariat kami di telepon {$p['phone']} atau email resmi {$p['email']} pada jam operasional {$p['hours']}.";
        }

        if (str_contains($msg, 'jurusan') || str_contains($msg, 'program') || str_contains($msg, 'kurikulum') || str_contains($msg, 'akademik')) {
            return "{$p['name']} menyelenggarakan Kurikulum Merdeka dengan peminatan MIPA, IPS, dan Keagamaan, didukung program unggulan Tahfidzul Quran, Digital Innovation Class, dan Bahasa Asing (Arab & Inggris).";
        }

        if (str_contains($msg, 'berita') || str_contains($msg, 'terbaru') || str_contains($msg, 'kegiatan')) {
            $posts = SchoolContext::latestPosts(3);

            if ($posts->isNotEmpty()) {
                $list = $posts->map(fn ($post) => sprintf(
                    '%s (%s) %s',
                    $post->title,
                    $post->published_at?->translatedFormat('d F Y') ?? 'tanpa tanggal',
                    SchoolContext::postUrl($post)
                ))->join('; ');

                return "Berita terbaru {$p['name']}: {$list}.";
            }
        }

        return "Terima kasih telah menghubungi {$p['name']} AI Assistant. Ada yang bisa kami bantu terkait PPDB, program akademik, fasilitas, atau informasi kontak sekolah?";
    }

    /**
     * "ig", "fb", "wa" dicek dengan batas kata supaya tidak cocok dengan
     * kata lain yang memuat dua huruf itu (mis. "tinggi", "jawab").
     */
    private static function mentionsSocial(string $msg): bool
    {
        $keywords = ['media sosial', 'medsos', 'sosmed', 'instagram', 'facebook', 'youtube', 'tiktok', 'twitter', 'linkedin', 'whatsapp'];

        if (Str::contains($msg, $keywords)) {
            return true;
        }

        return (bool) preg_match('/\b(ig|fb|wa)\b/', $msg);
    }
}
