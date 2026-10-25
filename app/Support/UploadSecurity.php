<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class UploadSecurity
{
    public static function assertClean(UploadedFile $file): void
    {
        if (! config('filesystems.upload_virus_scan', false)) {
            return;
        }

        $scanner = escapeshellcmd((string) config('filesystems.virus_scanner', 'clamscan'));
        $command = $scanner.' --no-summary '.escapeshellarg($file->getPathname());
        exec($command, $output, $exitCode);

        if ($exitCode === 0) {
            return;
        }

        if ($exitCode === 1) {
            throw new RuntimeException('Unggahan ditolak karena terdeteksi ancaman keamanan.');
        }

        throw new RuntimeException('Pemindaian keamanan unggahan gagal.');
    }

    public static function sanitizeSvg(string $contents): string
    {
        if (preg_match('/<\\s*(script|foreignObject|iframe|object|embed|audio|video)\\b/i', $contents)
            || preg_match('/\\bon[a-z]+\\s*=\\s*["\']/i', $contents)
            || preg_match('/\\b(?:href|xlink:href)\\s*=\\s*["\']\\s*(?:javascript:|data:)/i', $contents)) {
            throw new RuntimeException('SVG mengandung konten aktif yang tidak diizinkan.');
        }

        $contents = preg_replace('/<\\?xml[^>]*\\?>/i', '', $contents) ?? $contents;
        $contents = preg_replace('/<!DOCTYPE[^>]*>/i', '', $contents) ?? $contents;
        $contents = preg_replace('/\\s+xmlns:xlink\\s*=\\s*["\'][^"\']+["\']/i', '', $contents) ?? $contents;

        return trim($contents);
    }
}
