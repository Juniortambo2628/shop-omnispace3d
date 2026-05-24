<?php

namespace App\Console\Commands;

use App\Services\ImageUploadService;
use Illuminate\Console\Command;

class OptimizeProductImages extends Command
{
    protected $signature = 'images:optimize-products
                            {--force : Re-process images that already have WebP + thumbnail variants}
                            {--dry-run : Report what would be processed without changing files}';

    protected $description = 'Compress existing product images to WebP (+ thumbnails) in static/images/products';

    public function handle(ImageUploadService $images): int
    {
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run — no files will be modified.');
        }

        $this->info('Scanning product image library…');

        $stats = $images->optimizeProductImageLibrary($force, $dryRun, function (string $message) {
            $this->line($message);
        });

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed', (string) $stats['processed']],
                ['Skipped', (string) $stats['skipped']],
                ['Failed', (string) $stats['failed']],
                ['Legacy files removed', (string) $stats['removed']],
                ['Bytes saved (approx.)', number_format($stats['saved_bytes'])],
            ]
        );

        if ($stats['failed'] > 0) {
            $this->warn('Some images failed — check storage/logs/laravel.log for details.');

            return self::FAILURE;
        }

        if (! $dryRun && $stats['processed'] > 0) {
            $this->info('Done. Browsers will pick up new files via ?v= cache-busting on image URLs.');
        }

        return self::SUCCESS;
    }
}
