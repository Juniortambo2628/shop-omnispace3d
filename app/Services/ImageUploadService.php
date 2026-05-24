<?php

namespace App\Services;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class ImageUploadService
{
    public const MAX_SOURCE_BYTES = 25 * 1024 * 1024;

    public const MAX_WIDTH = 1200;

    public const THUMB_WIDTH = 400;

    public const WEBP_QUALITY = 82;

    public const THUMB_QUALITY = 65;

    /** @var list<string> */
    public const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /** @var list<string> */
    public const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    /**
     * @param  array<string, mixed>  $file  $_FILES-style entry
     */
    public function validateLegacyUpload(array $file, bool $required = true): ?string
    {
        $name = (string) ($file['name'] ?? '');
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($name === '' || $error === UPLOAD_ERR_NO_FILE) {
            return $required ? 'Please choose an image file.' : null;
        }

        if ($error !== UPLOAD_ERR_OK) {
            return match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large for the server limit. The image was compressed in your browser — try again, or use a smaller photo.',
                UPLOAD_ERR_PARTIAL => 'Upload was interrupted. Please try again.',
                default => 'Upload failed (error ' . $error . '). Please try again.',
            };
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');

        if ($tmpPath === '' || ! is_readable($tmpPath)) {
            return 'Upload could not be read. Please try again.';
        }

        $size = (int) ($file['size'] ?? filesize($tmpPath));

        if ($size <= 0) {
            return 'Uploaded file is empty.';
        }

        if ($size > self::MAX_SOURCE_BYTES) {
            return 'File is too large (max ' . (int) (self::MAX_SOURCE_BYTES / 1024 / 1024) . ' MB).';
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return 'Invalid file type. Allowed: JPG, PNG, WebP, GIF.';
        }

        $mime = $this->detectMime($tmpPath);

        if ($mime !== null && ! in_array($mime, self::ALLOWED_MIMES, true)) {
            return 'Invalid image format. Allowed: JPG, PNG, WebP, GIF.';
        }

        return null;
    }

    public function processProductImage(string $tmpPath, string $stem, ?string $originalName = null): bool
    {
        $targetDir = STATIC_PATH . '/images/products/';

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $stem = strtoupper(trim($stem));

        if ($stem === '') {
            throw new \InvalidArgumentException('Invalid product image filename.');
        }

        if (! class_exists(ImageManager::class)) {
            $this->clearProductImageVariants($stem);

            return $this->saveOriginalFallback($tmpPath, $targetDir, $stem, $originalName);
        }

        $previousMemory = ini_get('memory_limit');
        ini_set('memory_limit', '256M');

        $tempBase = tempnam(sys_get_temp_dir(), 'omniimg_');
        if ($tempBase === false) {
            throw new \RuntimeException('Could not create temporary image file.');
        }

        $mainTemp = $tempBase . '_main.webp';
        $thumbTemp = $tempBase . '_thumb.webp';
        @unlink($tempBase);

        try {
            $driverClass = extension_loaded('imagick') ? ImagickDriver::class : GdDriver::class;
            $manager = new ImageManager($driverClass);

            $img = $manager->decodePath($tmpPath);
            $img->orient();
            $img->scaleDown(width: self::MAX_WIDTH);
            $img->save($mainTemp, quality: self::WEBP_QUALITY);

            $thumb = $manager->decodePath($tmpPath);
            $thumb->orient();
            $thumb->scaleDown(width: self::THUMB_WIDTH);
            $thumb->save($thumbTemp, quality: self::THUMB_QUALITY);

            if (! is_file($mainTemp) || ! is_file($thumbTemp)) {
                throw new \RuntimeException('Processed image files were not written.');
            }

            $this->clearProductImageVariants($stem);

            if (! rename($mainTemp, $targetDir . $stem . '.webp') || ! rename($thumbTemp, $targetDir . 'thumb_' . $stem . '.webp')) {
                throw new \RuntimeException('Failed to finalize optimized image files.');
            }
        } catch (\Throwable $e) {
            @unlink($mainTemp);
            @unlink($thumbTemp);

            \Log::error('Image processing failed, saving original file: ' . $e->getMessage(), [
                'stem' => $stem,
            ]);

            return $this->saveOriginalFallback($tmpPath, $targetDir, $stem, $originalName);
        } finally {
            @unlink($mainTemp);
            @unlink($thumbTemp);

            if ($previousMemory !== false) {
                ini_set('memory_limit', (string) $previousMemory);
            }
        }

        \Log::info("Image processed: {$stem}.webp + thumb_{$stem}.webp");

        return true;
    }

    /**
     * Re-optimize an existing on-disk product image (legacy PNG/JPG or WebP).
     */
    public function reprocessProductImageAtPath(string $sourcePath): bool
    {
        $sourcePath = realpath($sourcePath) ?: $sourcePath;

        if (! is_file($sourcePath)) {
            return false;
        }

        $basename = basename($sourcePath);

        if (str_starts_with(strtolower($basename), 'thumb_')) {
            return false;
        }

        $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));

        if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return false;
        }

        $stem = strtoupper(pathinfo($basename, PATHINFO_FILENAME));
        $targetWebp = STATIC_PATH . '/images/products/' . $stem . '.webp';
        $tempCopy = tempnam(sys_get_temp_dir(), 'omniimg_');

        if ($tempCopy === false || ! copy($sourcePath, $tempCopy)) {
            return false;
        }

        try {
            $ok = $this->processProductImage($tempCopy, $stem, $basename);

            if ($ok && realpath($sourcePath) !== realpath($targetWebp)) {
                @unlink($sourcePath);
            }

            return $ok;
        } finally {
            @unlink($tempCopy);
        }
    }

    /**
     * Batch-compress all legacy product images to WebP + thumbnails.
     *
     * @param  callable(string): void|null  $log
     * @return array{processed: int, skipped: int, failed: int, removed: int, saved_bytes: int}
     */
    public function optimizeProductImageLibrary(bool $force = false, bool $dryRun = false, ?callable $log = null): array
    {
        $stats = [
            'processed' => 0,
            'skipped' => 0,
            'failed' => 0,
            'removed' => 0,
            'saved_bytes' => 0,
        ];

        $dir = STATIC_PATH . '/images/products';

        if (! is_dir($dir)) {
            return $stats;
        }

        $files = scandir($dir) ?: [];
        sort($files);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.htaccess') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (! is_file($path)) {
                continue;
            }

            if (str_starts_with(strtolower($file), 'thumb_')) {
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $stem = strtoupper(pathinfo($file, PATHINFO_FILENAME));
            $webpPath = $dir . DIRECTORY_SEPARATOR . $stem . '.webp';
            $thumbPath = $dir . DIRECTORY_SEPARATOR . 'thumb_' . $stem . '.webp';
            $originalSize = filesize($path) ?: 0;

            if ($ext !== 'webp' && is_file($webpPath)) {
                if ($dryRun) {
                    $stats['removed']++;
                    $stats['saved_bytes'] += $originalSize;
                    $this->logImageOptimize($log, "[dry-run] Would remove orphan legacy file: {$file}");
                } else {
                    if (@unlink($path)) {
                        $stats['removed']++;
                        $stats['saved_bytes'] += $originalSize;
                        $this->logImageOptimize($log, "Removed orphan legacy file: {$file}");
                    }
                }

                continue;
            }

            if (! $force && $ext === 'webp' && is_file($thumbPath)) {
                $stats['skipped']++;
                continue;
            }

            if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                $stats['skipped']++;
                continue;
            }

            if ($dryRun) {
                $stats['processed']++;
                $this->logImageOptimize($log, "[dry-run] Would optimize: {$file}");
                continue;
            }

            $beforeBytes = $this->directoryVariantBytes($dir, $stem);

            if ($this->reprocessProductImageAtPath($path)) {
                $afterBytes = $this->directoryVariantBytes($dir, $stem);
                $stats['processed']++;
                $stats['saved_bytes'] += max(0, $beforeBytes - $afterBytes);
                $this->logImageOptimize($log, "Optimized: {$stem}");
            } else {
                $stats['failed']++;
                $this->logImageOptimize($log, "Failed: {$file}");
            }
        }

        return $stats;
    }

    /**
     * Total bytes used by a product image stem (main + thumb, any extension).
     */
    private function directoryVariantBytes(string $dir, string $stem): int
    {
        $bytes = 0;

        foreach (glob("{$dir}/{$stem}.*") ?: [] as $file) {
            $bytes += filesize($file) ?: 0;
        }

        foreach (glob("{$dir}/thumb_{$stem}.*") ?: [] as $file) {
            $bytes += filesize($file) ?: 0;
        }

        return $bytes;
    }

    /**
     * @param  callable(string): void|null  $log
     */
    private function logImageOptimize(?callable $log, string $message): void
    {
        if ($log !== null) {
            $log($message);
        }
    }

    public function clearProductImageVariants(string $stem): void
    {
        $imgDir = STATIC_PATH . '/images/products';
        $stem = strtoupper(trim($stem));
        $allowed = ['webp', 'jpg', 'jpeg', 'png', 'gif'];

        foreach (glob("{$imgDir}/{$stem}.*") ?: [] as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (! in_array($ext, $allowed, true)) {
                continue;
            }

            @unlink($file);
        }

        foreach (glob("{$imgDir}/thumb_{$stem}.*") ?: [] as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (! in_array($ext, $allowed, true)) {
                continue;
            }

            @unlink($file);
        }
    }

    private function saveOriginalFallback(string $tmpPath, string $targetDir, string $stem, ?string $originalName): bool
    {
        $ext = strtolower(pathinfo($originalName ?? 'image.jpg', PATHINFO_EXTENSION)) ?: 'jpg';

        if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $ext = 'jpg';
        }

        copy($tmpPath, $targetDir . $stem . '.' . $ext);
        \Log::info("Image saved without processing: {$stem}.{$ext}");

        return true;
    }

    private function detectMime(string $path): ?string
    {
        if (! function_exists('finfo_open')) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            return null;
        }

        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);

        return is_string($mime) ? $mime : null;
    }
}
