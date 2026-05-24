<?php

namespace App\Services;

class AdminPackingService
{
    public function __construct(
        private readonly ProductService $products
    ) {}

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function itemsByCategory(string $eventSlug): array
    {
        $categoryNames = $this->categoryNameMap();
        $productCategoryByCode = $this->productCategoryByCode();

        $orders = \DB::fetchAll(
            "SELECT * FROM orders WHERE event_slug = ? AND status != 'Cancelled'",
            [$eventSlug]
        );

        $itemsByCat = [];

        foreach ($orders as $order) {
            $items = \DB::fetchAll('SELECT * FROM order_items WHERE order_id = ?', [$order['id']]);

            foreach ($items as $item) {
                $cat = $this->resolveItemCategoryName($item, $categoryNames, $productCategoryByCode);

                if (! isset($itemsByCat[$cat])) {
                    $itemsByCat[$cat] = [];
                }

                $itemsByCat[$cat][] = [
                    'order_id' => $order['id'],
                    'company' => $order['company_name'],
                    'booth' => $order['booth_number'],
                    'product' => $item['product_name'],
                    'color' => $item['color_name'],
                    'qty' => $item['quantity'],
                ];
            }
        }

        return $this->sortCategories($itemsByCat, $categoryNames);
    }

    /**
     * @return array<int, array{order: array<string, mixed>, items: array<int, array<string, mixed>>}>
     */
    public function boothsByEvent(string $eventSlug): array
    {
        $orders = \DB::fetchAll(
            "SELECT * FROM orders WHERE event_slug = ? AND status != 'Cancelled' ORDER BY booth_number ASC",
            [$eventSlug]
        );

        $booths = [];

        foreach ($orders as $order) {
            $booths[] = [
                'order' => $order,
                'items' => \DB::fetchAll('SELECT * FROM order_items WHERE order_id = ?', [$order['id']]),
            ];
        }

        return $booths;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, string>  $categoryNames
     * @param  array<string, string>  $productCategoryByCode
     */
    public function resolveItemCategoryName(array $item, array $categoryNames, array $productCategoryByCode): string
    {
        $raw = trim((string) ($item['category'] ?? $item['category_name'] ?? ''));

        if ($raw !== '' && isset($categoryNames[$raw])) {
            return $categoryNames[$raw];
        }

        if ($raw !== '') {
            foreach ($categoryNames as $id => $name) {
                if (strcasecmp($raw, $name) === 0) {
                    return $name;
                }
            }
        }

        $code = strtoupper(trim((string) ($item['product_code'] ?? '')));
        $catId = $code !== '' ? ($productCategoryByCode[$code] ?? '') : '';

        if ($catId !== '' && isset($categoryNames[$catId])) {
            return $categoryNames[$catId];
        }

        if ($raw !== '') {
            return $raw;
        }

        return 'Uncategorized';
    }

    /**
     * @return array<string, string>
     */
    public function categoryNameMap(): array
    {
        $map = [];

        foreach ($this->products->getCatalog()['CATEGORIES'] as $cat) {
            $map[$cat['id']] = $cat['name'];
        }

        return $map;
    }

    /**
     * @return array<string, string>
     */
    public function productCategoryByCode(): array
    {
        $map = [];

        foreach ($this->products->getMergedProducts() as $product) {
            $code = strtoupper(trim((string) ($product['code'] ?? '')));

            if ($code === '') {
                continue;
            }

            $map[$code] = $product['category_id'] ?? '';
        }

        return $map;
    }

    /**
     * @param  array<string, array<int, array<string, mixed>>>  $itemsByCat
     * @param  array<string, string>  $categoryNames
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function sortCategories(array $itemsByCat, array $categoryNames): array
    {
        $order = array_values($categoryNames);
        $sorted = [];

        foreach ($order as $name) {
            if (isset($itemsByCat[$name])) {
                $sorted[$name] = $itemsByCat[$name];
            }
        }

        foreach ($itemsByCat as $cat => $items) {
            if (! isset($sorted[$cat])) {
                $sorted[$cat] = $items;
            }
        }

        if (isset($sorted['Uncategorized'])) {
            $uncategorized = $sorted['Uncategorized'];
            unset($sorted['Uncategorized']);
            $sorted['Uncategorized'] = $uncategorized;
        }

        return $sorted;
    }
}
