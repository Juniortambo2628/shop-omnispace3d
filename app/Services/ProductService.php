<?php

namespace App\Services;

use App\Models\AdminProduct;
use App\Support\AdminProductList;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;

class ProductService
{
    public function __construct(
        private readonly ImageUploadService $imageUploads
    ) {}

    /**
     * @return array{PRODUCTS: array<int, array<string, mixed>>, CATEGORIES: array<int, array<string, mixed>>}
     */
    public function getCatalog(): array
    {
        return require BASE_PATH . '/data/catalog.php';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMergedProducts(): array
    {
        $catalog = $this->getCatalog();

        return $this->mergeWithAdminProducts($catalog['PRODUCTS']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $catalogProducts
     * @return array<int, array<string, mixed>>
     */
    public function mergeWithAdminProducts(array $catalogProducts): array
    {
        $categories = $this->getCatalog()['CATEGORIES'];

        if (! $this->adminProductsTableExists()) {
            return $this->sortProductsForCatalog($catalogProducts, $categories);
        }

        try {
            $dbProducts = AdminProduct::query()->where('active', true)->get();
        } catch (QueryException $e) {
            if ($this->isMissingTableException($e)) {
                return $this->sortProductsForCatalog($catalogProducts, $categories);
            }

            throw $e;
        }

        $overridesByCatalogId = [];
        $adminOnlyProducts = [];

        foreach ($dbProducts as $product) {
            $normalized = $this->normalizeDbProduct($product);

            if (! empty($product->is_override) && ! empty($product->original_catalog_id)) {
                $overridesByCatalogId[$product->original_catalog_id] = $normalized;
            } else {
                $adminOnlyProducts[] = $normalized;
            }
        }

        $merged = [];

        foreach ($catalogProducts as $product) {
            if (isset($overridesByCatalogId[$product['id']])) {
                $merged[] = $overridesByCatalogId[$product['id']];
                unset($overridesByCatalogId[$product['id']]);
            } else {
                $merged[] = $product;
            }
        }

        foreach ($overridesByCatalogId as $product) {
            $merged[] = $product;
        }

        foreach ($adminOnlyProducts as $product) {
            $merged[] = $product;
        }

        return $this->sortProductsForCatalog($merged, $categories);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeDbProduct(AdminProduct $product): array
    {
        return $product->toLegacyArray();
    }

    /**
     * @param  array<int, array<string, mixed>>  $products
     * @param  array<int, array<string, mixed>>  $categories
     * @return array<int, array<string, mixed>>
     */
    private function sortProductsForCatalog(array $products, array $categories): array
    {
        $categoryOrder = [];
        foreach ($categories as $index => $category) {
            $categoryOrder[$category['id']] = $index;
        }

        usort($products, function (array $a, array $b) use ($categoryOrder): int {
            $catA = $categoryOrder[$a['category_id'] ?? ''] ?? PHP_INT_MAX;
            $catB = $categoryOrder[$b['category_id'] ?? ''] ?? PHP_INT_MAX;

            if ($catA !== $catB) {
                return $catA <=> $catB;
            }

            return strcasecmp((string) ($a['id'] ?? ''), (string) ($b['id'] ?? ''));
        });

        return $products;
    }

    /**
     * @return array<int, string>
     */
    public function getDbProductIds(): array
    {
        if (! $this->adminProductsTableExists()) {
            return [];
        }

        try {
            return AdminProduct::query()
                ->where('active', true)
                ->pluck('prod_id')
                ->all();
        } catch (QueryException $e) {
            if ($this->isMissingTableException($e)) {
                return [];
            }

            throw $e;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function buildProductsPageData(
        string $eventSlug,
        ?string $search,
        ?string $catFilter,
        int $page,
        bool $isSuperAdmin
    ): array {
        $catalog = $this->getCatalog();
        $categories = $catalog['CATEGORIES'];
        $merged = $this->getMergedProducts();
        $filtered = AdminProductList::filter($merged, $search, $catFilter);
        $paged = AdminProductList::paginate($filtered, $page);
        $catOptions = AdminProductList::categoryOptions($categories);
        $paginatedProducts = $paged['items'];
        $totalProducts = $paged['total'];
        $totalPages = $paged['total_pages'];
        $page = $paged['page'];
        $limit = $paged['limit'];

        return [
            'products' => $paginatedProducts,
            'categories' => $categories,
            'db_prod_ids' => $this->getDbProductIds(),
            'event_slug' => $eventSlug,
            'is_super_admin' => $isSuperAdmin,
            'active_page' => 'products',
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'total_records' => $totalProducts,
                'limit' => $limit,
            ],
            'header' => [
                'title' => 'Product Catalogue',
                'subtitle' => "{$totalProducts} products across " . count($categories) . ' categories',
                'actions' => $isSuperAdmin ? [
                    ['label' => '＋ Add Product', 'url' => '/admin/products/add', 'class' => 'btn-primary'],
                ] : [],
            ],
            'filters' => [
                'filter_action' => '/admin/products',
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
     * @param  array<string, array<string, string>>  $images
     */
    public function resolveProductThumbnailUrl(string $code, array $images): ?string
    {
        $code = strtoupper($code);

        if (empty($images[$code])) {
            return null;
        }

        $set = $images[$code];
        $file = $set['default'] ?? reset($set);

        if (! $file) {
            return null;
        }

        $stem = pathinfo($file, PATHINFO_FILENAME);
        $thumbFile = 'thumb_' . $stem . '.webp';
        $thumbPath = STATIC_PATH . '/images/products/' . $thumbFile;

        if (is_file($thumbPath)) {
            return $this->versionedProductImageUrl($thumbFile);
        }

        return $this->versionedProductImageUrl($file);
    }

    /**
     * @param  array<string, mixed>  $post
     */
    public function addProductFromPost(array $post, string $username): string
    {
        $code = strtoupper(trim($post['code'] ?? ''));
        $name = trim($post['name'] ?? '');
        $catId = $post['category_id'] ?? '';
        $isPoa = isset($post['is_poa']);
        $price = $isPoa ? 0 : (float) ($post['price'] ?? 0);
        $currency = $post['currency'] ?? 'USD';
        $dims = trim($post['dimensions'] ?? '');
        $desc = trim($post['description'] ?? '');
        $unit = trim($post['unit'] ?? 'per item');

        try {
            v::key('code', v::stringType()->notEmpty())
                ->key('name', v::stringType()->notEmpty())
                ->key('category_id', v::stringType()->notEmpty())
                ->assert($post);
        } catch (NestedValidationException $ex) {
            $errors = $ex->getMessages();
            throw new \Exception(reset($errors));
        }

        $colors = $this->colorsFromPost($post);
        $priceDisplay = $isPoa
            ? 'POA'
            : ($currency === 'USD' ? '$' . number_format($price) : $currency . ' ' . number_format($price));

        $catalog = $this->getMergedProducts();
        $existing = array_filter($catalog, fn ($p) => strtoupper($p['code'] ?? '') === $code);
        $prodId = count($existing) > 0
            ? $code . '-' . str_pad((string) (count($existing) + 1), 2, '0', STR_PAD_LEFT)
            : $code;

        AdminProduct::create([
            'prod_id' => $prodId,
            'code' => $code,
            'name' => $name,
            'category_id' => $catId,
            'colors' => $colors,
            'dimensions' => $dims,
            'price' => $price,
            'price_display' => $priceDisplay,
            'description' => $desc,
            'unit' => $unit,
            'is_poa' => $isPoa,
            'is_override' => false,
            'active' => true,
            'created_by' => $username,
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);

        return $prodId;
    }

    /**
     * @return array{prod: array<string, mixed>, is_override: bool}|null
     */
    public function findProductForEdit(string $prodId): ?array
    {
        $catalogData = $this->getCatalog();
        $record = AdminProduct::query()->where('prod_id', $prodId)->first();
        $isOverride = false;

        if ($record) {
            $prod = $record->toLegacyArray();
        } else {
            $prod = null;
            foreach ($catalogData['PRODUCTS'] as $catalogProduct) {
                if ($catalogProduct['id'] === $prodId) {
                    $prod = $catalogProduct;
                    $prod['is_override'] = true;
                    $isOverride = true;
                    break;
                }
            }
        }

        if (! $prod) {
            return null;
        }

        return ['prod' => $prod, 'is_override' => $isOverride];
    }

    /**
     * @param  array<string, mixed>  $post
     * @param  array<string, mixed>  $files  $_FILES-style upload array
     */
    public function updateProductFromPost(
        string $prodId,
        array $prod,
        bool $isOverride,
        array $post,
        bool $isSuperAdmin,
        string $username,
        array $files = []
    ): void {
        $name = trim($post['name'] ?? '');
        $dims = trim($post['dimensions'] ?? '');
        $desc = trim($post['description'] ?? '');
        $unit = trim($post['unit'] ?? 'per item');

        $fields = [
            'name' => $name,
            'dimensions' => $dims,
            'description' => $desc,
            'unit' => $unit,
        ];

        if ($isSuperAdmin) {
            $code = strtoupper(trim($post['code'] ?? $prod['code']));
            $catId = $post['category_id'] ?? $prod['category_id'];
            $isPoa = isset($post['is_poa']);
            $price = $isPoa ? 0 : (float) ($post['price'] ?? 0);
            $currency = $post['currency'] ?? 'USD';
            $priceDisplay = $isPoa
                ? 'POA'
                : ($currency === 'USD' ? '$' . number_format($price) : $currency . ' ' . number_format($price));

            $fields['code'] = $code;
            $fields['category_id'] = $catId;
            $fields['is_poa'] = $isPoa;
            $fields['price'] = $price;
            $fields['price_display'] = $priceDisplay;
        }

        $colors = $this->colorsFromPost($post, true);
        $fields['colors'] = $colors;

        if ($isOverride && ! AdminProduct::query()->where('prod_id', $prodId)->exists()) {
            AdminProduct::create(array_merge([
                'code' => $prod['code'] ?? '',
                'category_id' => $prod['category_id'] ?? '',
                'price' => (float) ($prod['price'] ?? 0),
                'price_display' => $prod['price_display'] ?? '',
                'is_poa' => ! empty($prod['is_poa']),
            ], $fields, [
                'prod_id' => $prodId,
                'is_override' => true,
                'original_catalog_id' => $prodId,
                'active' => true,
                'created_by' => $username,
                'created_at' => now()->format('Y-m-d H:i:s'),
            ]));
        } else {
            AdminProduct::query()
                ->where('prod_id', $prodId)
                ->update(array_merge($fields, [
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]));
        }

        $this->handleImageUploads($fields['code'] ?? $prod['code'], $colors, $files);
    }

    public function deleteProduct(string $prodId): void
    {
        AdminProduct::query()
            ->where('prod_id', $prodId)
            ->update(['active' => false]);
    }

    /**
     * @return array<string, string>
     */
    public function getProductImagesByCode(string $code): array
    {
        $all = $this->getProductImages();

        return $all[strtoupper($code)] ?? [];
    }

    public function placeholderImageUrl(): string
    {
        return '/static/images/omnispace-logo.jpg';
    }

    public function productDisplayImageUrl(string $filename, bool $preferThumb = true): string
    {
        $url = $this->resolvePublicImageUrl($filename, $preferThumb);

        return $url !== '' ? $url : $this->placeholderImageUrl();
    }

    public function resolvePublicImageUrl(string $filename, bool $preferThumb = true): string
    {
        $filename = trim($filename);

        if ($filename === '') {
            return '';
        }

        $imgDir = STATIC_PATH . '/images/products';

        if ($preferThumb) {
            $stem = pathinfo($filename, PATHINFO_FILENAME);
            $thumbFile = 'thumb_' . $stem . '.webp';

            if (is_file($imgDir . '/' . $thumbFile)) {
                return $this->versionedProductImageUrl($thumbFile);
            }
        }

        return $this->versionedProductImageUrl($filename);
    }

    public function versionedProductImageUrl(string $file): string
    {
        $file = ltrim(str_replace('\\', '/', $file), '/');
        $path = STATIC_PATH . '/images/products/' . basename($file);
        $url = '/static/images/products/' . basename($file);

        if (is_file($path)) {
            $url .= '?v=' . filemtime($path);
        }

        return $url;
    }

    /**
     * @return array<string, int> filename => filemtime (for client-side cache busting)
     */
    public function getProductImageVersionMap(): array
    {
        $dir = STATIC_PATH . '/images/products';
        $versions = [];

        if (! is_dir($dir)) {
            return $versions;
        }

        foreach (scandir($dir) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;

            if (! is_file($path)) {
                continue;
            }

            $versions[$file] = (int) filemtime($path);
        }

        return $versions;
    }

    /**
     * @param  array<int, array{id: string, name: string}>  $colors
     * @param  array<string, mixed>  $files
     */
    public function handleImageUploads(string $code, array $colors, array $files = []): void
    {
        $code = strtoupper($code);
        $uploads = $files ?: $_FILES;

        if (! empty($uploads['image_main']['name'])) {
            $error = $this->imageUploads->validateLegacyUpload($uploads['image_main']);
            if ($error) {
                throw new \InvalidArgumentException($error);
            }
            $this->processAndSaveImage(
                $uploads['image_main']['tmp_name'],
                $code,
                $uploads['image_main']['name'] ?? null
            );
        }

        foreach ($colors as $color) {
            $cid = $color['id'];
            $key = "image_$cid";
            if (! empty($uploads[$key]['name'])) {
                $error = $this->imageUploads->validateLegacyUpload($uploads[$key]);
                if ($error) {
                    throw new \InvalidArgumentException($error);
                }
                $this->processAndSaveImage(
                    $uploads[$key]['tmp_name'],
                    "$code-" . str_pad($cid, 2, '0', STR_PAD_LEFT),
                    $uploads[$key]['name'] ?? null
                );
            }
        }
    }

    public function processAndSaveImage(string $tmpPath, string $filename, ?string $originalName = null): bool
    {
        return $this->imageUploads->processProductImage($tmpPath, $filename, $originalName);
    }

    public function clearOldProductImages(string $stem): void
    {
        $this->imageUploads->clearProductImageVariants($stem);
    }

    /**
     * @return array{success: bool, error: string|null}
     */
    public function deleteProductImage(string $code, string $colorId = 'default'): array
    {
        $code = strtoupper(trim($code));

        if ($code === '') {
            return ['success' => false, 'error' => 'Product code is required.'];
        }

        $colorId = trim($colorId) ?: 'default';
        $stem = $code;

        if ($colorId !== 'default') {
            $stem .= '-' . str_pad($colorId, 2, '0', STR_PAD_LEFT);
        }

        $this->imageUploads->clearProductImageVariants(strtoupper($stem));

        return ['success' => true, 'error' => null];
    }

    /**
     * @param  array<string, mixed>  $post
     * @param  array<string, mixed>  $file  Single $_FILES-style entry
     * @return array{success: string|null, error: string|null}
     */
    public function uploadImageFromPost(array $post, array $file): array
    {
        $prodId = $post['product_id'] ?? '';
        $colorId = $post['color_id'] ?? '';
        $allProducts = $this->getMergedProducts();

        if (empty($file['name']) || ! $prodId) {
            return ['success' => null, 'error' => 'Please select a product and a file.'];
        }

        $validationError = $this->imageUploads->validateLegacyUpload($file);
        if ($validationError) {
            return ['success' => null, 'error' => $validationError];
        }

        $product = null;
        foreach ($allProducts as $p) {
            if ($p['id'] == $prodId) {
                $product = $p;
                break;
            }
        }

        if (! $product) {
            return ['success' => null, 'error' => 'Selected product not found.'];
        }

        $filename = $product['code'];
        if ($colorId && $colorId !== 'default') {
            $filename .= '-' . str_pad($colorId, 2, '0', STR_PAD_LEFT);
        }
        $filename = strtoupper($filename);

        try {
            if ($this->processAndSaveImage($file['tmp_name'], $filename, $file['name'])) {
                return ['success' => 'Image uploaded and optimized for ' . $product['name'], 'error' => null];
            }

            return ['success' => null, 'error' => 'Failed to process image.'];
        } catch (\Throwable $e) {
            \Log::error('Image upload error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ['success' => null, 'error' => 'Failed to process image: ' . $e->getMessage()];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function buildImagesPageData(
        string $eventSlug,
        ?string $error = null,
        ?string $success = null
    ): array {
        $catalog = $this->getCatalog();
        $allProducts = $this->getMergedProducts();
        $images = $this->getProductImages();

        $hasImgCount = 0;
        foreach ($allProducts as $p) {
            if (isset($images[$p['code']])) {
                $hasImgCount++;
            }
        }

        $totalCount = count($allProducts);
        $gridProducts = array_map(static function ($p) {
            return [
                'id' => $p['id'],
                'code' => $p['code'],
                'name' => $p['name'],
                'category_id' => $p['category_id'] ?? '',
                'colors' => $p['colors'] ?? [],
            ];
        }, $allProducts);

        return [
            'products' => $allProducts,
            'products_json' => json_encode($gridProducts),
            'images_json' => json_encode($images),
            'image_versions_json' => json_encode($this->getProductImageVersionMap()),
            'images' => $images,
            'has_img_count' => $hasImgCount,
            'total_count' => $totalCount,
            'placeholder_image' => $this->placeholderImageUrl(),
            'categories' => $catalog['CATEGORIES'],
            'error' => $error,
            'success' => $success,
            'event_slug' => $eventSlug,
            'active_page' => 'images',
            'header' => [
                'title' => '🖼️ Product Images',
                'subtitle' => "{$hasImgCount} / {$totalCount} products have images",
            ],
        ];
    }

    private function isMissingTableException(\Throwable $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'Base table or view not found')
            || $e->getCode() === '42S02';
    }

    private function adminProductsTableExists(): bool
    {
        return Schema::hasTable('admin_products');
    }

    /**
     * @param  array<string, mixed>  $post
     * @return array<int, array{id: string, name: string}>
     */
    private function colorsFromPost(array $post, bool $preserveIds = false): array
    {
        $colorNames = $post['color_name'] ?? [];
        $colorIds = $post['color_id'] ?? [];
        $colors = [];

        if ($preserveIds) {
            for ($i = 0; $i < count($colorNames); $i++) {
                $name = trim($colorNames[$i]);
                if ($name) {
                    $id = $colorIds[$i] ?? str_pad((string) (count($colors) + 1), 2, '0', STR_PAD_LEFT);
                    $colors[] = ['id' => $id, 'name' => $name];
                }
            }
        } else {
            $idx = 1;
            foreach ($colorNames as $name) {
                $name = trim($name);
                if ($name) {
                    $colors[] = ['id' => str_pad((string) $idx++, 2, '0', STR_PAD_LEFT), 'name' => $name];
                }
            }
        }

        if (empty($colors)) {
            $colors = [['id' => '01', 'name' => 'Standard']];
        }

        return $colors;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function resolveEvent(string $slug): ?array
    {
        return EVENTS[$slug] ?? null;
    }

    public function isCatalogAuthenticated(string $slug): bool
    {
        return isset($_SESSION['catalog_auth_' . $slug]);
    }

    public function authenticateCatalog(string $slug, string $password): bool
    {
        $event = $this->resolveEvent($slug);

        if (! $event) {
            return false;
        }

        if ($password === ($event['catalog_password'] ?? null)) {
            $_SESSION['catalog_auth_' . $slug] = true;

            return true;
        }

        global $CONFIG;

        $settingsKey = 'catalog_password_' . $slug;
        if (! empty($CONFIG[$settingsKey]) && $password === $CONFIG[$settingsKey]) {
            $_SESSION['catalog_auth_' . $slug] = true;

            return true;
        }

        return false;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getProductImages(): array
    {
        $imgDir = STATIC_PATH . '/images/products';

        if (! is_dir($imgDir)) {
            return [];
        }

        $images = [];
        $files = scandir($imgDir);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($files as $fname) {
            if ($fname === '.' || $fname === '..') {
                continue;
            }

            if (str_starts_with(strtolower($fname), 'thumb_')) {
                continue;
            }

            $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));

            if (! in_array($ext, $allowed, true)) {
                continue;
            }

            $stem = strtoupper(pathinfo($fname, PATHINFO_FILENAME));
            $lastHyphen = strrpos($stem, '-');

            if ($lastHyphen !== false) {
                $suffix = substr($stem, $lastHyphen + 1);

                if (is_numeric($suffix) && (strlen($suffix) === 1 || strlen($suffix) === 2)) {
                    $code = substr($stem, 0, $lastHyphen);
                    $colorId = str_pad($suffix, 2, '0', STR_PAD_LEFT);

                    if (! isset($images[$code])) {
                        $images[$code] = [];
                    }

                    $images[$code][$colorId] = $fname;
                    continue;
                }
            }

            if (! isset($images[$stem])) {
                $images[$stem] = [];
            }

            $images[$stem]['default'] = $fname;
        }

        return $images;
    }
}
