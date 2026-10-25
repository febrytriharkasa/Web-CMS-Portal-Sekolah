<?php

namespace App\Support;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Menyimpan gambar unggahan sebagai WebP yang sudah diperkecil dan dibersihkan
 * dari metadata. SVG dan GIF dilewatkan apa adanya karena GD tidak bisa
 * mengoptimalkannya tanpa merusak (vektor / animasi).
 */
class ImageOptimizer
{
    private const QUALITY = 80;

    /**
     * Simpan gambar hasil optimasi dan kembalikan path relatif disk "public".
     */
    public static function storeOptimized(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1600,
    ): string {
        UploadSecurity::assertClean($file);

        $source = self::decode($file);

        if (! $source) {
            // Vektor, animasi, atau format yang tidak bisa dibaca GD: simpan asli.
            return self::storeRaw($file, $directory);
        }

        $source = self::autoOrient($source, $file->getPathname());

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width > $maxWidth) {
            $source = self::resize($source, $maxWidth, (int) round($height * ($maxWidth / $width)));
        }

        $encoded = self::encodeWebp($source);

        if ($encoded === null) {
            return self::storeRaw($file, $directory);
        }

        $path = $directory.'/'.bin2hex(random_bytes(20)).'.webp';
        Storage::disk('public')->put($path, $encoded);

        return $path;
    }

    /**
     * Simpan file apa adanya. SVG dibersihkan dulu supaya tidak ada script aktif.
     */
    private static function storeRaw(UploadedFile $file, string $directory): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'svg') {
            $path = $directory.'/'.bin2hex(random_bytes(20)).'.svg';
            Storage::disk('public')->put($path, UploadSecurity::sanitizeSvg((string) $file->get()));

            return $path;
        }

        return $file->store($directory, 'public');
    }

    /**
     * Sama seperti storeOptimized(), tapi langsung mengembalikan URL publik.
     */
    public static function storeAndUrl(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1600,
    ): string {
        return Storage::url(self::storeOptimized($file, $directory, $maxWidth));
    }

    /**
     * Perkecil gambar yang sudah tersimpan di disk dan kembalikan byte WebP-nya.
     * Dipakai route /img/{lebar}/{path} untuk membuat varian hero responsif.
     */
    public static function downscale(string $absolutePath, int $maxWidth): ?string
    {
        $source = match (strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION))) {
            'png' => @imagecreatefrompng($absolutePath),
            'webp' => @imagecreatefromwebp($absolutePath),
            default => @imagecreatefromjpeg($absolutePath),
        };

        if (! $source instanceof GdImage) {
            return null;
        }

        $width = imagesx($source);

        if ($width > $maxWidth) {
            $source = self::resize($source, $maxWidth, (int) round(imagesy($source) * ($maxWidth / $width)));
        }

        return self::encodeWebp($source);
    }

    private static function decode(UploadedFile $file): ?GdImage
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        if (in_array($extension, ['svg', 'gif'], true) || in_array($mime, ['image/svg+xml', 'image/gif'], true)) {
            return null;
        }

        $path = $file->getPathname();

        $image = match (true) {
            $extension === 'png', $mime === 'image/png' => @imagecreatefrompng($path),
            $extension === 'webp', $mime === 'image/webp' => @imagecreatefromwebp($path),
            $extension === 'bmp', $mime === 'image/bmp' => @imagecreatefrombmp($path),
            default => @imagecreatefromjpeg($path),
        };

        return $image instanceof GdImage ? $image : null;
    }

    /**
     * Putar gambar sesuai tag EXIF supaya foto ponsel tidak tampil miring.
     */
    private static function autoOrient(GdImage $image, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        return $rotated instanceof GdImage ? $rotated : $image;
    }

    private static function resize(GdImage $source, int $width, int $height): GdImage
    {
        $target = imagecreatetruecolor($width, $height);

        // Pertahankan transparansi PNG/WebP saat diperkecil.
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));

        imagecopyresampled(
            $target, $source,
            0, 0, 0, 0,
            $width, $height,
            imagesx($source), imagesy($source),
        );

        imagedestroy($source);

        return $target;
    }

    private static function encodeWebp(GdImage $image): ?string
    {
        ob_start();
        $ok = imagewebp($image, null, self::QUALITY);
        $encoded = ob_get_clean();

        imagedestroy($image);

        return $ok ? $encoded : null;
    }
}
