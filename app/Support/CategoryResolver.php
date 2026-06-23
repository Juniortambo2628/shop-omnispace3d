<?php

namespace App\Support;

class CategoryResolver
{
    /**
     * Resolve a category string from an order item to a human-readable category name.
     *
     * @param  array<string, mixed>  $item
     * @param  array<string, string>  $categoryNames       Map of category ID => name
     * @param  array<string, string>  $productCategoryByCode  Map of product code => category ID
     */
    public static function resolve(array $item, array $categoryNames, array $productCategoryByCode): string
    {
        $raw = trim((string) ($item['category'] ?? $item['category_name'] ?? ''));

        if ($raw !== '' && isset($categoryNames[$raw])) {
            return $categoryNames[$raw];
        }

        if ($raw !== '') {
            foreach ($categoryNames as $name) {
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
}
