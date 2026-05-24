<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\StockLevel;
use App\Support\AdminProductList;

class AdminStockService
{
    public function __construct(
        private readonly ProductService $products
    ) {}

    /**
     * @param  array<string, mixed>  $post
     */
    public function saveLimitsFromPost(array $post): void
    {
        $catalog = $this->products->getCatalog();

        foreach ($post as $key => $value) {
            if (! str_starts_with($key, 'limit_')) {
                continue;
            }

            $code = substr($key, 6);
            $limit = ($value === '') ? null : (int) $value;

            $existing = StockLevel::query()->where('product_code', $code)->first();

            if ($existing) {
                $existing->update(['stock_limit' => $limit]);

                continue;
            }

            if ($limit === null) {
                continue;
            }

            StockLevel::create([
                'product_code' => $code,
                'product_name' => $this->resolveProductName($catalog['PRODUCTS'], $code),
                'stock_limit' => $limit,
            ]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getStockDataForEvent(string $eventSlug): array
    {
        $stockData = StockLevel::query()
            ->select([
                'stock_levels.product_code',
                'stock_levels.product_name',
                'stock_levels.stock_limit',
            ])
            ->selectRaw('(
                SELECT COALESCE(SUM(oi.quantity), 0)
                FROM order_items oi
                INNER JOIN orders o ON o.id = oi.order_id
                WHERE oi.product_code = stock_levels.product_code
                  AND o.event_slug = ?
                  AND o.status != ?
            ) AS total_ordered', [$eventSlug, 'Cancelled'])
            ->get()
            ->map(function (StockLevel $row) {
                $limit = $row->stock_limit;
                $ordered = (int) ($row->getAttributes()['total_ordered'] ?? 0);

                return [
                    'product_code' => $row->product_code,
                    'product_name' => $row->product_name,
                    'stock_limit' => $limit,
                    'total_ordered' => $ordered,
                    'pct' => ($limit > 0) ? (int) round(($ordered / $limit) * 100) : null,
                ];
            })
            ->all();

        return $stockData;
    }

    /**
     * @return array{stock_limit: int|null, total_ordered: int, pct: int|null}
     */
    public function getProductStockSummary(string $productCode, string $eventSlug): array
    {
        $productCode = strtoupper(trim($productCode));
        $row = StockLevel::query()->where('product_code', $productCode)->first();

        $ordered = (int) OrderItem::query()
            ->where('product_code', $productCode)
            ->whereHas('order', function ($query) use ($eventSlug) {
                $query->where('event_slug', $eventSlug)
                    ->where('status', '!=', 'Cancelled');
            })
            ->sum('quantity');

        $limit = $row?->stock_limit !== null ? (int) $row->stock_limit : null;
        $pct = ($limit !== null && $limit > 0) ? (int) round(($ordered / $limit) * 100) : null;

        return [
            'stock_limit' => $limit,
            'total_ordered' => $ordered,
            'pct' => $pct,
        ];
    }

    public function saveProductLimit(string $productCode, string $productName, mixed $limitValue): void
    {
        $productCode = strtoupper(trim($productCode));
        $limit = ($limitValue === '' || $limitValue === null) ? null : (int) $limitValue;

        $existing = StockLevel::query()->where('product_code', $productCode)->first();

        if ($existing) {
            $existing->update([
                'stock_limit' => $limit,
                'product_name' => $productName,
            ]);

            return;
        }

        if ($limit !== null) {
            StockLevel::create([
                'product_code' => $productCode,
                'product_name' => $productName,
                'stock_limit' => $limit,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function buildStockPageData(
        string $eventSlug,
        bool $saved = false,
        ?string $search = null,
        ?string $catFilter = null,
        int $page = 1
    ): array {
        $catalog = $this->products->getCatalog();
        $categories = $catalog['CATEGORIES'];
        $catOptions = AdminProductList::categoryOptions($categories);
        $merged = $this->products->getMergedProducts();
        $filtered = AdminProductList::filter($merged, $search, $catFilter);
        $paged = AdminProductList::paginate($filtered, $page, 20, true);
        $paginatedProducts = $paged['items'];
        $totalProducts = $paged['total'];
        $totalPages = $paged['total_pages'];
        $page = $paged['page'];
        $limit = $paged['limit'];

        return [
            'products' => $paginatedProducts,
            'categories' => $categories,
            'stock_data' => $this->getStockDataForEvent($eventSlug),
            'event_slug' => $eventSlug,
            'saved' => $saved,
            'active_page' => 'stock',
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'total_records' => $totalProducts,
                'limit' => $limit,
            ],
            'header' => [
                'title' => '📦 Stock Levels',
                'subtitle' => (EVENTS[$eventSlug]['name'] ?? 'Solar & Storage Live 2026')
                    . " — {$totalProducts} products",
                'actions' => [
                    [
                        'label' => '💾 Save Page Limits',
                        'type' => 'button',
                        'class' => 'btn-primary',
                        'onclick' => "document.getElementById('stockForm').submit()",
                    ],
                ],
            ],
            'filters' => [
                'filter_action' => '/admin/stock',
                'search_placeholder' => '🔍 Search by product code or name...',
                'search_query' => $search,
                'has_active_filters' => ($search || $catFilter),
                'filter_options' => [
                    [
                        'name' => 'cat',
                        'label' => 'All Categories',
                        'selected' => $catFilter,
                        'options' => $catOptions,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $catalogProducts
     */
    private function resolveProductName(array $catalogProducts, string $code): string
    {
        foreach ($catalogProducts as $product) {
            if (($product['code'] ?? '') == $code) {
                return $product['name'];
            }
        }

        return 'Unknown';
    }
}
