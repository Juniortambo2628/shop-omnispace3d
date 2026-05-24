<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalogPath = base_path('data/catalog.json');

        if (! file_exists($catalogPath)) {
            $this->command?->warn('catalog.json not found — skipping product stock seed.');

            return;
        }

        $catalog = json_decode((string) file_get_contents($catalogPath), true);

        if (! is_array($catalog) || empty($catalog['PRODUCTS'])) {
            $this->command?->warn('catalog.json has no PRODUCTS — skipping.');

            return;
        }

        foreach ($catalog['PRODUCTS'] as $product) {
            $code = $product['code'] ?? null;
            $name = $product['name'] ?? null;

            if (! $code || ! $name) {
                continue;
            }

            DB::table('stock_levels')->updateOrInsert(
                ['product_code' => $code],
                ['product_name' => $name]
            );
        }

        $this->command?->info('Seeded stock_levels for ' . count($catalog['PRODUCTS']) . ' catalog products.');
    }
}
