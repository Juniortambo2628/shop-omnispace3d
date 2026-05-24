<?php
/**
 * OmniShop Complete Product Catalog
 * Loads from JSON converted from Python
 */

$jsonPath = __DIR__ . '/catalog.json';
if (!file_exists($jsonPath)) {
    return ['CATEGORIES' => [], 'PRODUCTS' => []];
}

$data = json_decode(file_get_contents($jsonPath), true);
return $data;
