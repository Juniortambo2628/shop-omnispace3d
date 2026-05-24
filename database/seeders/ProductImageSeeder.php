<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $catalogPath = base_path('data/catalog.json');
        $imagesDir = base_path('static/images/products');
        $seedDir = base_path('database/seed/product-images');

        if (! is_dir($imagesDir)) {
            mkdir($imagesDir, 0777, true);
        }

        if (is_dir($seedDir)) {
            $this->copySeedImages($seedDir, $imagesDir);
        }

        if (! file_exists($catalogPath)) {
            $this->command?->warn('catalog.json not found — skipping image verification.');

            return;
        }

        $catalog = json_decode((string) file_get_contents($catalogPath), true);
        $products = $catalog['PRODUCTS'] ?? [];
        $existing = $this->indexImages($imagesDir);

        $withImage = 0;
        $missing = 0;

        foreach ($products as $product) {
            $code = strtoupper((string) ($product['code'] ?? ''));

            if ($code === '') {
                continue;
            }

            if ($this->productHasImage($code, $existing)) {
                $withImage++;
                continue;
            }

            $missing++;
        }

        $this->command?->info("Product images: {$withImage} catalog products have images, {$missing} missing.");
    }

    /**
     * @return array<string, true>
     */
    private function indexImages(string $directory): array
    {
        $indexed = [];

        if (! is_dir($directory)) {
            return $indexed;
        }

        foreach (scandir($directory) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $stem = strtoupper(pathinfo($file, PATHINFO_FILENAME));

            if (str_starts_with($stem, 'THUMB_')) {
                $stem = substr($stem, 6);
            }

            $indexed[$stem] = true;
        }

        return $indexed;
    }

    /**
     * @param  array<string, true>  $existing
     */
    private function productHasImage(string $code, array $existing): bool
    {
        if (isset($existing[$code])) {
            return true;
        }

        foreach ($existing as $stem => $_) {
            if (str_starts_with($stem, $code . '-')) {
                return true;
            }
        }

        return false;
    }

    private function copySeedImages(string $from, string $to): void
    {
        $copied = 0;

        foreach (scandir($from) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $source = $from . DIRECTORY_SEPARATOR . $file;
            $target = $to . DIRECTORY_SEPARATOR . $file;

            if (! is_file($source) || file_exists($target)) {
                continue;
            }

            copy($source, $target);
            $copied++;
        }

        if ($copied > 0) {
            $this->command?->info("Copied {$copied} product images from database/seed/product-images.");
        }
    }
}
