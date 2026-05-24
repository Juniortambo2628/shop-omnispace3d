<?php

namespace App\Support;

class AdminProductList
{
    /**
     * @param  array<int, array<string, mixed>>  $products
     * @return array<int, array<string, mixed>>
     */
    public static function filter(array $products, ?string $search, ?string $catFilter): array
    {
        $filtered = $products;

        if ($search) {
            $filtered = array_filter($filtered, function ($product) use ($search) {
                return FuzzySearch::matchesProductRecord($product, $search);
            });

            usort($filtered, function ($a, $b) use ($search) {
                return FuzzySearch::productMatchScore($b, $search) <=> FuzzySearch::productMatchScore($a, $search);
            });
        }

        if ($catFilter) {
            $filtered = array_filter($filtered, function ($product) use ($catFilter) {
                return ($product['category_id'] ?? '') == $catFilter;
            });
        }

        return array_values($filtered);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{items: array<int, array<string, mixed>>, page: int, total: int, total_pages: int, limit: int}
     */
    public static function paginate(array $items, int $page, int $limit = 20, bool $clampPage = false): array
    {
        $page = max(1, $page);
        $total = count($items);
        $totalPages = max(1, (int) ceil($total / $limit));

        if ($clampPage) {
            $page = min($page, $totalPages);
        }

        $offset = ($page - 1) * $limit;

        return [
            'items' => array_slice($items, $offset, $limit),
            'page' => $page,
            'total' => $total,
            'total_pages' => $totalPages,
            'limit' => $limit,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $categories
     * @return array<string, string>
     */
    public static function categoryOptions(array $categories): array
    {
        $options = [];

        foreach ($categories as $cat) {
            $options[$cat['id']] = $cat['name'];
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $product
     * @param  array<int, array<string, mixed>>  $categories
     */
    public static function categoryNameForProduct(array $product, array $categories): string
    {
        $catId = $product['category_id'] ?? '';

        foreach ($categories as $cat) {
            if (($cat['id'] ?? '') == $catId) {
                return (string) ($cat['name'] ?? $catId);
            }
        }

        return (string) $catId;
    }
}
