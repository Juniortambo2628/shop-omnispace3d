<?php

namespace App\Services;

use App\Models\Order;
use App\Support\CategoryResolver;
use Illuminate\Support\Facades\DB;

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

        $orders = Order::with('items')
            ->where('event_slug', $eventSlug)
            ->where('status', '!=', 'Cancelled')
            ->get();

        $itemsByCat = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $itemArr = $item->toArray();
                $cat = CategoryResolver::resolve($itemArr, $categoryNames, $productCategoryByCode);

                if (! isset($itemsByCat[$cat])) {
                    $itemsByCat[$cat] = [];
                }

                $itemsByCat[$cat][] = [
                    'order_id' => $order->id,
                    'company' => $order->company_name,
                    'booth' => $order->booth_number,
                    'product' => $item->product_name,
                    'color' => $item->color_name,
                    'qty' => $item->quantity,
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
        $orders = Order::with('items')
            ->where('event_slug', $eventSlug)
            ->where('status', '!=', 'Cancelled')
            ->orderBy('booth_number')
            ->get();

        $booths = [];

        foreach ($orders as $order) {
            $booths[] = [
                'order' => $order->toArray(),
                'items' => $order->items->map->toArray()->all(),
            ];
        }

        return $booths;
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
