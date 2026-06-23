<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MapsLegacyUploads;
use App\Http\Controllers\Concerns\RendersLegacyViews;
use App\Services\AdminOrderService;
use App\Services\AdminPackingService;
use App\Services\AdminStockService;
use App\Services\AdminUserService;
use App\Services\AuthService;
use App\Services\ProductService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    use MapsLegacyUploads;
    use RendersLegacyViews;

    public function __construct(
        private readonly AdminOrderService $adminOrders,
        private readonly AuthService $auth,
        private readonly ProductService $products,
        private readonly AdminStockService $stock,
        private readonly AdminPackingService $packing,
        private readonly AdminUserService $users,
        private readonly SettingsService $settings,
    ) {}

    public function index(): never
    {
        $this->redirect($this->auth->defaultLandingPath());
    }

    public function login(Request $request): never
    {
        if ($this->auth->check()) {
            $this->redirect($this->auth->defaultLandingPath());
        }

        $error = null;

        if ($request->isMethod('POST')) {
            if ($this->auth->attemptLogin(
                $request->input('username', ''),
                $request->input('password', ''),
            )) {
                $this->redirect($this->auth->defaultLandingPath());
            }

            $error = 'Incorrect email or password. Please try again.';
        }

        $this->render('admin/login', ['error' => $error]);
    }

    public function logout(): never
    {
        $this->auth->logout();
        $this->redirect('/admin/login');
    }

    public function orders(Request $request): never
    {
        $this->auth->requireAdmin();

        $eventSlug = $request->query('event', DEFAULT_EVENT);
        $statusFilter = $request->query('status');
        $search = $request->query('search');

        $this->render('admin/orders', array_merge(
            $this->adminOrders->buildOrdersPageData($eventSlug, $statusFilter, $search),
            $this->auth->viewContext()
        ));
    }

    public function updateOrderStatus(Request $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin();

        $status = $request->json('status');

        try {
            $this->adminOrders->updateStatus($id, $status, $this->auth->user()['username'] ?? 'unknown');

            return response()->json(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => 'Invalid status'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateOrderPaymentReference(Request $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin();

        $paymentReference = $request->json('payment_reference');

        if ($paymentReference === null) {
            return response()->json(['error' => 'No fields to update'], 400);
        }

        try {
            $this->adminOrders->updatePaymentReference($id, $paymentReference);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function showEditOrder(Request $request, string $id): never
    {
        $this->auth->requireAdmin();

        $pageData = $this->adminOrders->buildEditOrderPageData($id);

        if (! $pageData) {
            $this->redirect('/admin/orders?error=Order not found');
        }

        $this->render('admin/edit_order', array_merge($pageData, $this->auth->viewContext()));
    }

    public function updateOrder(Request $request, string $id): never
    {
        $this->auth->requireAdmin();

        $pageData = $this->adminOrders->buildEditOrderPageData($id);

        if (! $pageData) {
            $this->redirect('/admin/orders?error=Order not found');
        }

        $eventSlug = $pageData['event_slug'];
        $error = null;

        if ($request->isMethod('POST')) {
            try {
                $this->adminOrders->updateOrderFromPost($id, $request->post());
                $this->redirect('/admin/orders/' . $id . '/edit?saved=1&event=' . urlencode($eventSlug));
            } catch (\InvalidArgumentException $e) {
                $error = $e->getMessage();
            } catch (\Exception $e) {
                $error = 'Failed to update order.';
            }
        }

        $this->render('admin/edit_order', array_merge(
            $pageData,
            ['error' => $error],
            $this->auth->viewContext()
        ));
    }

    public function exportOrders(Request $request): never
    {
        $eventSlug = $request->query('event', DEFAULT_EVENT);
        $statusFilter = $request->query('status');
        $search = $request->query('search');
        $csv = $this->adminOrders->exportCsv($eventSlug, $statusFilter, $search);
        $filename = 'orders-' . $eventSlug . '-' . date('Y-m-d') . '.csv';

        $this->sendCsvResponse($csv, $filename);
    }

    public function sendInvoice(string $id): JsonResponse
    {
        $this->auth->requireAdmin();

        $data = $this->adminOrders->findOrderWithItems($id);

        if (! $data) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($this->adminOrders->sendInvoiceEmail($id)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Failed to send email'], 500);
    }

    public function invoice(string $id): never
    {
        $pdf = $this->adminOrders->generateInvoicePdf($id);

        if (! $pdf) {
            die('Order not found');
        }

        $this->sendPdfResponse($pdf, 'Invoice-' . $id . '.pdf');
    }

    public function products(Request $request): never
    {
        $this->auth->requireAdmin();

        $eventSlug = $request->query('event', DEFAULT_EVENT);
        $page = (int) $request->query('page', 1);

        $this->render('admin/products', array_merge(
            $this->products->buildProductsPageData(
                $eventSlug,
                $request->query('search'),
                $request->query('cat'),
                $page,
                $this->auth->isSuperAdmin()
            ),
            $this->auth->viewContext()
        ));
    }

    public function addProduct(Request $request): never
    {
        $this->auth->requireAdmin();

        $eventSlug = $request->query('event', DEFAULT_EVENT);
        $error = null;
        $catalog = $this->products->getCatalog();

        if ($request->isMethod('POST')) {
            try {
                $prodId = $this->products->addProductFromPost(
                    $request->post(),
                    $this->auth->user()['username']
                );
                $this->redirect("/admin/products?added={$prodId}");
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $this->render('admin/add_product', array_merge([
            'categories' => $catalog['CATEGORIES'],
            'event_slug' => $eventSlug,
            'error' => $error,
            'active_page' => 'products',
            'header' => [
                'title' => '＋ Add Product',
                'subtitle' => 'Create a new catalogue item',
                'actions' => [
                    ['label' => '← Back to Products', 'url' => '/admin/products?event=' . urlencode($eventSlug)],
                ],
            ],
        ], $this->auth->viewContext()));
    }

    public function editProduct(Request $request, string $id): never
    {
        $this->auth->requireAdmin();

        $eventSlug = $request->query('event', DEFAULT_EVENT);
        $error = null;
        $found = $this->products->findProductForEdit($id);

        if (! $found) {
            $this->redirect('/admin/products?error=Product not found');
        }

        $prod = $found['prod'];
        $isOverride = $found['is_override'];
        $catalog = $this->products->getCatalog();

        if ($request->isMethod('POST')) {
            try {
                $this->products->updateProductFromPost(
                    $id,
                    $prod,
                    $isOverride,
                    $request->post(),
                    $this->auth->isSuperAdmin(),
                    $this->auth->user()['username'],
                    $this->legacyFilesFromRequest($request)
                );

                if ($request->has('stock_limit')) {
                    $this->stock->saveProductLimit(
                        $prod['code'],
                        trim($request->input('name', $prod['name'] ?? '')),
                        $request->input('stock_limit')
                    );
                }

                $this->redirect("/admin/products?saved={$id}");
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $stockSummary = $this->stock->getProductStockSummary($prod['code'], $eventSlug);
        $categoryName = $prod['category_id'] ?? '';

        foreach ($catalog['CATEGORIES'] as $cat) {
            if (($cat['id'] ?? '') === ($prod['category_id'] ?? '')) {
                $categoryName = $cat['name'];
                break;
            }
        }

        $productImages = $this->products->getProductImagesByCode($prod['code']);
        $imageUrls = [];

        foreach ($productImages as $colorId => $filename) {
            $imageUrls[$colorId] = $this->products->resolvePublicImageUrl($filename);
        }

        $heroImage = ! empty($imageUrls['default'])
            ? $imageUrls['default']
            : $this->products->placeholderImageUrl();
        $priceBadge = ! empty($prod['is_poa'])
            ? 'POA'
            : ($prod['price_display'] ?? '');

        $this->render('admin/edit_product', array_merge([
            'prod' => $prod,
            'categories' => $catalog['CATEGORIES'],
            'category_name' => $categoryName,
            'event_slug' => $eventSlug,
            'error' => $error,
            'is_super_admin' => $this->auth->isSuperAdmin(),
            'images' => $productImages,
            'image_urls' => $imageUrls,
            'placeholder_image' => $this->products->placeholderImageUrl(),
            'stock_summary' => $stockSummary,
            'active_page' => 'products',
            'header' => [
                'title' => '✏️ Edit Product',
                'subtitle' => ($prod['code'] ?? '') . ' · ' . $categoryName,
                'actions' => [
                    ['label' => '← Back to Products', 'url' => '/admin/products?event=' . urlencode($eventSlug)],
                ],
            ],
            'hero_title' => $prod['name'] ?? '',
            'hero_meta' => ($prod['code'] ?? '') . ' · ' . $categoryName,
            'hero_badge' => $priceBadge,
            'hero_image' => $heroImage,
            'can_delete_product' => $this->auth->isSuperAdmin()
                && in_array($prod['id'] ?? '', $this->products->getDbProductIds(), true),
        ], $this->auth->viewContext()));
    }

    public function deleteProduct(Request $request, string $id): never
    {
        $this->auth->requireSuperAdmin();

        $this->products->deleteProduct($id);
        $this->redirect('/admin/products?deleted=1');
    }

    public function stock(Request $request): never
    {
        $this->auth->requireAdmin();

        $eventSlug = $request->query('event', DEFAULT_EVENT);
        $page = (int) $request->query('page', 1);

        if ($request->isMethod('POST')) {
            $this->stock->saveLimitsFromPost($request->post());
            $this->redirect('/admin/stock?saved=1');
        }

        $this->render('admin/stock', array_merge(
            $this->stock->buildStockPageData(
                $eventSlug,
                $request->query('saved') !== null,
                $request->query('search'),
                $request->query('cat'),
                $page
            ),
            $this->auth->viewContext()
        ));
    }

    public function packingList(Request $request): never
    {
        $this->auth->requireAdmin();

        $slug = $request->query('event', DEFAULT_EVENT);

        $this->render('admin/packing_list', array_merge([
            'items_by_cat' => $this->packing->itemsByCategory($slug),
            'event_slug' => $slug,
            'active_page' => 'packing_category',
            'header' => [
                'title' => '📋 Packing List (Category)',
                'subtitle' => (EVENTS[$slug]['name'] ?? $slug) . ' — grouped by product category',
                'actions' => [
                    ['label' => '← Back to Orders', 'url' => '/admin/orders?event=' . urlencode($slug)],
                    ['label' => '🖨 Print', 'type' => 'button', 'class' => 'btn-primary', 'onclick' => 'window.print()'],
                ],
            ],
        ], $this->auth->viewContext()));
    }

    public function packingByBooth(Request $request): never
    {
        $this->auth->requireAdmin();

        $slug = $request->query('event', DEFAULT_EVENT);

        $this->render('admin/packing_by_booth', array_merge([
            'booths' => $this->packing->boothsByEvent($slug),
            'event_slug' => $slug,
            'active_page' => 'packing_stand',
            'header' => [
                'title' => '📋 Packing List (Stand)',
                'subtitle' => (EVENTS[$slug]['name'] ?? $slug) . ' — grouped by booth number',
                'actions' => [
                    ['label' => '← Back to Orders', 'url' => '/admin/orders?event=' . urlencode($slug)],
                    ['label' => '🖨 Print', 'type' => 'button', 'class' => 'btn-primary', 'onclick' => 'window.print()'],
                ],
            ],
        ], $this->auth->viewContext()));
    }

    public function images(Request $request): never
    {
        $this->auth->requireAdmin();

        $eventSlug = $request->query('event', DEFAULT_EVENT);
        $error = $request->query('error');
        $success = $request->query('deleted') ? 'Image removed successfully.' : null;

        if ($request->isMethod('POST')) {
            $uploadFile = $request->file('image');
            $legacyFile = [];

            if ($uploadFile) {
                $legacyFile = [
                    'name' => $uploadFile->getClientOriginalName(),
                    'type' => $uploadFile->getClientMimeType(),
                    'tmp_name' => $uploadFile->getRealPath(),
                    'error' => $uploadFile->getError(),
                    'size' => $uploadFile->getSize(),
                ];
            }

            $result = $this->products->uploadImageFromPost($request->post(), $legacyFile);
            $error = $result['error'];
            $success = $result['success'];
        }

        $this->render('admin/images', array_merge(
            $this->products->buildImagesPageData($eventSlug, $error, $success),
            $this->auth->viewContext()
        ));
    }

    public function deleteImage(Request $request)
    {
        $this->auth->requireAdmin();

        $code = trim((string) $request->input('product_code', ''));
        $colorId = trim((string) $request->input('color_id', 'default')) ?: 'default';

        $result = $this->products->deleteProductImage($code, $colorId);

        if ($request->expectsJson()) {
            if (! $result['success']) {
                return response()->json($result, 422);
            }

            return response()->json($result);
        }

        if (! $result['success']) {
            $this->redirect('/admin/images?error=' . urlencode($result['error'] ?? 'Delete failed'));
        }

        $this->redirect('/admin/images?deleted=1');
    }

    public function users(Request $request): never
    {
        $this->auth->requireSuperAdmin();

        $error = null;
        $eventSlug = $request->query('event', DEFAULT_EVENT);

        if ($request->isMethod('POST')) {
            $displayName = trim($request->input('display_name', ''));
            $username = trim($request->input('username', ''));
            $password = $request->input('password', '');
            $role = $request->input('role', 'order_manager');

            if ($displayName && $username && $password) {
                try {
                    $this->users->createUser($displayName, $username, $password, $role);
                    $this->redirect('/admin/users?added=1');
                } catch (\Exception $e) {
                    $error = 'User already exists or database error.';
                }
            } else {
                $error = 'All fields are required.';
            }
        }

        $userList = $this->users->listUsers();

        $this->render('admin/users', array_merge([
            'users' => $userList,
            'error' => $error,
            'event_slug' => $eventSlug,
            'active_page' => 'users',
            'current_user_id' => $this->resolveCurrentUserId(),
            'header' => [
                'title' => '👥 Admin Users',
                'subtitle' => count($userList) . ' user account' . (count($userList) === 1 ? '' : 's'),
            ],
        ], $this->auth->viewContext()));
    }

    public function editUser(Request $request, int $id): never
    {
        $this->auth->requireSuperAdmin();

        $user = $this->users->findById($id);

        if (! $user) {
            $this->redirect('/admin/users?error=User not found');
        }

        $eventSlug = $request->query('event', DEFAULT_EVENT);
        $error = null;
        $currentUserId = $this->resolveCurrentUserId();

        if ($request->isMethod('POST')) {
            try {
                $active = $request->has('active');

                if ($id === $currentUserId && ! $active) {
                    throw new \InvalidArgumentException('You cannot deactivate your own account.');
                }

                $this->users->updateUser(
                    $id,
                    $request->input('display_name', ''),
                    $request->input('email', ''),
                    $request->input('role', 'order_manager'),
                    $active
                );

                $newPassword = trim($request->input('new_password', ''));

                if ($newPassword !== '') {
                    $this->users->adminSetPassword($id, $newPassword);
                }

                $this->redirect('/admin/users/' . $id . '/edit?saved=1');
            } catch (\InvalidArgumentException $e) {
                $error = $e->getMessage();
            } catch (\Exception $e) {
                $error = 'Failed to update user.';
            }

            $user = $this->users->findById($id) ?? $user;
        }

        $email = $user['email'] ?? $user['username'] ?? '';
        $roleLabel = ucwords(str_replace('_', ' ', $user['role'] ?? 'order_manager'));

        $this->render('admin/edit_user', array_merge([
            'user' => $user,
            'error' => $error,
            'event_slug' => $eventSlug,
            'active_page' => 'users',
            'is_self' => $id === $currentUserId,
            'header' => [
                'title' => '✏️ Edit User',
                'subtitle' => $user['display_name'] ?? $email,
                'actions' => [
                    ['label' => '← Back to Users', 'url' => '/admin/users'],
                ],
            ],
            'hero_title' => $user['display_name'] ?? $email,
            'hero_meta' => $email,
            'hero_badge' => $roleLabel,
            'hero_initials' => AdminUserService::initialsFromIdentity($email ?: ($user['display_name'] ?? '')),
        ], $this->auth->viewContext()));
    }

    public function toggleUserActive(Request $request, int $id): never
    {
        $this->auth->requireSuperAdmin();

        $currentUserId = $this->resolveCurrentUserId();

        if ($id === $currentUserId) {
            $this->redirect('/admin/users?error=You cannot deactivate your own account.');
        }

        $user = $this->users->findById($id);

        if (! $user) {
            $this->redirect('/admin/users?error=User not found');
        }

        try {
            $this->users->setActive($id, ! (bool) ($user['active'] ?? 0));
            $this->redirect('/admin/users?saved=1');
        } catch (\Exception $e) {
            $this->redirect('/admin/users?error=' . urlencode($e->getMessage()));
        }
    }

    public function settings(Request $request): never
    {
        $this->auth->requireSuperAdmin();

        if ($request->isMethod('POST')) {
            $this->settings->saveFromPost($request->post());
            $this->redirect('/admin/settings?saved=1');
        }

        $eventSlug = $request->query('event', DEFAULT_EVENT);

        $this->render('admin/settings', array_merge([
            'settings' => $this->settings->getAll(),
            'event_slug' => $eventSlug,
            'active_page' => 'settings',
            'header' => [
                'title' => '⚙️ Settings',
                'subtitle' => 'System configuration and branding',
            ],
        ], $this->auth->viewContext()));
    }

    public function invoicePreview(): never
    {
        require_once BASE_PATH . '/core/Invoice.php';

        global $CONFIG;
        $config = $CONFIG;

        $sampleOrder = [
            'id' => '12345',
            'custom_order_id' => 'OMN-SSL26-001',
            'created_at' => date('Y-m-d H:i:s'),
            'company_name' => $config['company_name'] ?? 'Sample Company Ltd',
            'contact_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+254 700 000 000',
            'address' => 'P.O. Box 12345, Nairobi, Kenya',
            'booth_number' => 'A-12',
            'status' => 'Approved',
            'subtotal' => 1500.00,
            'vat' => 240.00,
            'total' => 1740.00,
            'payment_method' => 'Bank Transfer',
            'payment_reference' => 'TXN-2026-001',
        ];

        $sampleItems = [
            [
                'product_name' => 'Solar Panel 400W (Sample)',
                'product_code' => 'SP-400-BLK',
                'color_name' => 'Black',
                'quantity' => 5,
                'unit_price' => 300.00,
                'total_price' => 1500.00,
            ],
        ];

        $event = [
            'name' => 'Solar + Storage Live 2026',
            'venue' => 'Kenyatta International Convention Centre',
        ];

        $pdf = \Invoice::generate($sampleOrder, $sampleItems, $event, $config);

        $this->sendPdfResponse($pdf, 'invoice-preview.pdf', true);
    }

    public function verifyPayment(Request $request, string $id): JsonResponse
    {
        $this->auth->requireAdmin();

        $status = $request->json('status');
        $clientPaymentReference = $request->json('client_payment_reference');

        try {
            $this->adminOrders->verifyPayment(
                $id,
                $status,
                $this->auth->user()['username'] ?? 'unknown',
                $clientPaymentReference
            );

            return response()->json(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => 'Invalid verification status'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function profile(Request $request): never
    {
        $this->auth->requireAdmin();

        $identity = $this->auth->user()['username'] ?? '';
        $dbUser = $this->users->findByIdentity($identity);
        $profileError = null;

        if ($request->isMethod('POST') && $dbUser) {
            $action = $request->input('action', 'profile');

            try {
                if ($action === 'password') {
                    $newPassword = $request->input('new_password', '');
                    $confirmPassword = $request->input('confirm_password', '');

                    if ($newPassword !== $confirmPassword) {
                        throw new \InvalidArgumentException('New password and confirmation do not match.');
                    }

                    $this->users->updatePassword(
                        (int) $dbUser['id'],
                        $request->input('current_password', ''),
                        $newPassword
                    );

                    $this->redirect('/admin/profile?password_saved=1');
                }

                $email = trim($request->input('email', ''));
                $this->users->updateProfile(
                    (int) $dbUser['id'],
                    $request->input('display_name', ''),
                    $email
                );

                if ($email !== '' && $email !== $identity) {
                    $this->auth->updateSessionIdentity($email);
                }

                $this->redirect('/admin/profile?saved=1');
            } catch (\InvalidArgumentException $e) {
                $profileError = $e->getMessage();
            }
        }

        $profile = $dbUser
            ? $this->users->profileViewData($dbUser)
            : [
                'display_name' => $identity,
                'email' => $identity,
                'username' => $identity,
                'role' => $this->auth->user()['role'] ?? 'admin',
                'created_at' => null,
            ];

        $this->render('admin/profile', array_merge([
            'profile' => $profile,
            'profile_initials' => AdminUserService::initialsFromIdentity($profile['email'] ?: $profile['display_name']),
            'profile_readonly' => $dbUser === null,
            'profile_error' => $profileError,
            'active_page' => 'profile',
        ], $this->auth->viewContext()));
    }

    public function testEmail(): never
    {
        $this->auth->requireAdmin();

        global $CONFIG;

        try {
            $this->settings->sendTestEmail($CONFIG);
            $this->redirect('/admin/settings?tested=1');
        } catch (\Exception $e) {
            $this->render('admin/settings', array_merge([
                'settings' => $this->settings->getAll(),
                'test_err' => $e->getMessage(),
                'active_page' => 'settings',
                'header' => [
                    'title' => '⚙️ Settings',
                    'subtitle' => 'System configuration and branding',
                ],
            ], $this->auth->viewContext()));
        }
    }

    private function resolveCurrentUserId(): int
    {
        $id = (int) ($this->auth->user()['id'] ?? 0);

        if ($id > 0) {
            return $id;
        }

        $dbUser = $this->users->findByIdentity($this->auth->user()['username'] ?? '');

        return (int) ($dbUser['id'] ?? 0);
    }
}
