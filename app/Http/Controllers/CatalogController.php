<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersLegacyViews;
use App\Services\ProductService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    use RendersLegacyViews;

    public function __construct(
        private readonly ProductService $products
    ) {}

    public function index(): never
    {
        $this->redirect('/solarandstorage');
    }

    public function catalog(string $slug): never
    {
        if (! $this->products->resolveEvent($slug)) {
            $this->redirect('/solarandstorage');
        }

        if (! $this->products->isCatalogAuthenticated($slug)) {
            $this->redirect("/{$slug}/login");
        }

        $catalog = $this->products->getCatalog();
        $mergedProducts = $this->products->mergeWithAdminProducts($catalog['PRODUCTS']);

        global $CONFIG;

        $this->render('catalog', [
            'event' => EVENTS[$slug],
            'event_slug' => $slug,
            'products' => $mergedProducts,
            'products_count' => count($mergedProducts),
            'categories' => $catalog['CATEGORIES'],
            'products_json' => json_encode($mergedProducts),
            'product_images_json' => json_encode($this->products->getProductImages()),
            'product_image_versions_json' => json_encode($this->products->getProductImageVersionMap()),
            'product_placeholder_image' => $this->products->placeholderImageUrl(),
            'config' => $CONFIG,
        ]);
    }

    public function login(Request $request, string $slug): never
    {
        $event = $this->products->resolveEvent($slug);

        if (! $event) {
            $this->redirect('/solarandstorage');
        }

        $error = null;

        if ($request->isMethod('post')) {
            if ($this->products->authenticateCatalog($slug, $request->input('password', ''))) {
                $this->redirect("/{$slug}");
            }

            $error = 'Incorrect password. Please check your invitation.';
        }

        $this->render('catalog_login', [
            'event' => $event,
            'event_slug' => $slug,
            'error' => $error,
        ]);
    }
}
