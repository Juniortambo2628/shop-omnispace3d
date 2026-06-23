<?php

namespace App\Services;

use App\Models\Order;
use App\Support\FuzzySearch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminOrderService
{
    /**
     * @return array<int, array{order: array<string, mixed>, items: array<int, array<string, mixed>>}>
     */
    public function listOrdersWithItems(string $eventSlug, ?string $statusFilter, ?string $search): array
    {
        $query = Order::query()->where('event_slug', $eventSlug);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $orderModels = $query->orderByDesc('created_at')->get();

        if ($search) {
            $orderModels = $orderModels->filter(function (Order $order) use ($search) {
                return FuzzySearch::matchesRecord($order->toArray(), $search, [
                    'company_name',
                    'contact_name',
                    'id',
                    'booth_number',
                    'email',
                ]);
            })->values();
        }

        $orders = [];

        foreach ($orderModels as $order) {
            $orders[] = [
                'order' => $order->toArray(),
                'items' => $order->items()->get()->map->toArray()->all(),
            ];
        }

        return $orders;
    }

    /**
     * @return array{total_orders: int|float, total_revenue: float|int, by_status: array<string, int|float>}
     */
    public function getStats(string $eventSlug): array
    {
        $statusRows = DB::table('orders')
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->where('event_slug', $eventSlug)
            ->groupBy('status')
            ->get();

        $byStatus = [];

        foreach ($statusRows as $row) {
            $byStatus[$row->status] = $row->cnt;
        }

        return [
            'total_orders' => DB::table('orders')->where('event_slug', $eventSlug)->count(),
            'total_revenue' => DB::table('orders')->where('event_slug', $eventSlug)->sum('total') ?: 0,
            'by_status' => $byStatus,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTopStockUsage(int $limit = 5): array
    {
        $stockData = DB::select("
            SELECT sl.product_code, sl.product_name, sl.stock_limit,
                   COALESCE(SUM(oi.quantity), 0) as total_ordered
            FROM stock_levels sl
            LEFT JOIN order_items oi ON sl.product_code = oi.product_code
            GROUP BY sl.product_code
            HAVING sl.stock_limit > 0
            ORDER BY (COALESCE(SUM(oi.quantity), 0) * 100.0 / sl.stock_limit) DESC
            LIMIT ?
        ", [$limit]);

        $result = array_map(fn ($row) => (array) $row, $stockData);

        foreach ($result as &$stock) {
            $stock['pct'] = ($stock['stock_limit'] > 0)
                ? round(($stock['total_ordered'] / $stock['stock_limit']) * 100)
                : 0;
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildOrdersPageData(string $eventSlug, ?string $statusFilter, ?string $search): array
    {
        $statuses = Order::STATUSES;

        return [
            'orders' => $this->listOrdersWithItems($eventSlug, $statusFilter, $search),
            'stats' => $this->getStats($eventSlug),
            'event_slug' => $eventSlug,
            'stock_data' => $this->getTopStockUsage(5),
            'active_page' => 'orders',
            'header' => [
                'title' => 'Dashboard',
                'subtitle' => EVENTS[$eventSlug]['name'] ?? $eventSlug,
                'actions' => [
                    ['label' => '📥 Export CSV', 'url' => '/admin/export?event=' . urlencode($eventSlug), 'hx_boost' => false],
                    ['label' => '📋 Packing List', 'url' => '/admin/packing/category'],
                ],
            ],
            'filters' => [
                'filter_action' => '/admin/orders',
                'search_placeholder' => '🔍 Search company, name, order ID, booth...',
                'search_query' => $search,
                'has_active_filters' => ($search || $statusFilter),
                'filter_options' => [
                    [
                        'name' => 'status',
                        'label' => 'All Statuses',
                        'selected' => $statusFilter,
                        'options' => array_combine($statuses, $statuses),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{order: array<string, mixed>, items: array<int, array<string, mixed>>}|null
     */
    public function findOrderWithItems(string $orderId): ?array
    {
        $order = Order::with('items')->find($orderId);

        if (! $order) {
            $order = Order::with('items')->where('custom_order_id', $orderId)->first();
        }

        if (! $order) {
            return null;
        }

        return [
            'order' => $order->toArray(),
            'items' => $order->items->map->toArray()->all(),
        ];
    }

    public function updateStatus(string $orderId, string $status, string $updatedBy): void
    {
        $allowed = Order::STATUSES;

        if (! in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid status');
        }

        Order::where('id', $orderId)->update([
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Log::info('Order status updated', [
            'order_id' => $orderId,
            'status' => $status,
            'by' => $updatedBy,
        ]);

        if ($status === 'Invoiced') {
            $this->sendAvailabilityConfirmedEmail($orderId);
        } elseif ($status === 'Fulfilled') {
            $this->sendPaymentConfirmedEmail($orderId);
        }
    }

    public function updatePaymentReference(string $orderId, string $paymentReference): void
    {
        Order::where('id', $orderId)->update([
            'payment_reference' => $paymentReference,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateClientPaymentReference(string $orderId, string $clientPaymentReference): void
    {
        Order::where('id', $orderId)->update([
            'client_payment_reference' => $clientPaymentReference,
            'payment_verification_status' => 'pending',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function verifyPayment(string $orderId, string $status, string $verifiedBy, ?string $clientPaymentReference = null): void
    {
        $allowed = ['verified', 'rejected', 'pending'];

        if (! in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid verification status');
        }

        $updateData = [
            'payment_verification_status' => $status,
            'payment_verified_at' => date('Y-m-d H:i:s'),
            'payment_verified_by' => $verifiedBy,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($clientPaymentReference !== null) {
            $updateData['client_payment_reference'] = $clientPaymentReference;
        }

        if ($status === 'verified') {
            $updateData['status'] = 'Fulfilled';
        }

        Order::where('id', $orderId)->update($updateData);

        Log::info('Payment verification updated', [
            'order_id' => $orderId,
            'verification_status' => $status,
            'verified_by' => $verifiedBy,
        ]);

        if ($status === 'verified') {
            $this->sendPaymentConfirmedEmail($orderId);
        }
    }

    public function getClientOrderHistory(string $email): array
    {
        return Order::where('email', $email)
            ->orderByDesc('created_at')
            ->with('items')
            ->get()
            ->map(function ($order) {
                return [
                    'order' => $order->toArray(),
                    'items' => $order->items->map->toArray()->all(),
                ];
            })
            ->all();
    }

    public function listAllOrdersPaginated(?string $search, ?string $statusFilter, int $page = 1, int $perPage = 20, ?string $email = null): array
    {
        $query = Order::query();

        if ($email) {
            $query->where('email', $email);
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $query->orderByDesc('created_at');

        $orderModels = $query->get();

        if ($search) {
            $orderModels = $orderModels->filter(function (Order $order) use ($search) {
                return FuzzySearch::matchesRecord($order->toArray(), $search, [
                    'company_name',
                    'contact_name',
                    'id',
                    'custom_order_id',
                    'booth_number',
                    'email',
                    'payment_reference',
                    'client_payment_reference',
                ]);
            })->values();
        }

        $total = $orderModels->count();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        $paginated = $orderModels->slice($offset, $perPage);

        $orders = [];
        foreach ($paginated as $order) {
            $orders[] = [
                'order' => $order->toArray(),
                'items' => $order->items()->get()->map->toArray()->all(),
            ];
        }

        return [
            'orders' => $orders,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    public function sendInvoiceEmail(
        string $orderId,
        string $subjectPrefix = 'Tax Invoice',
        string $message = 'As requested, here is the tax invoice for your order.'
    ): bool {
        return $this->sendOrderNotification($orderId, 'sendAvailabilityConfirmedEmail');
    }

    public function sendAvailabilityConfirmedEmail(string $orderId): bool
    {
        return $this->sendOrderNotification($orderId, 'sendAvailabilityConfirmedEmail');
    }

    public function sendPaymentConfirmedEmail(string $orderId): bool
    {
        return $this->sendOrderNotification($orderId, 'sendPaymentProcessedEmail');
    }

    public function generateInvoicePdf(string $orderId): ?string
    {
        $data = $this->findOrderWithItems($orderId);

        if (! $data) {
            return null;
        }

        $order = $data['order'];
        $items = $data['items'];
        $event = $this->resolveEvent($order['event_slug'] ?? DEFAULT_EVENT);

        $this->loadLegacyDependencies();

        return \Invoice::generate($order, $items, $event, $GLOBALS['CONFIG']);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildEditOrderPageData(string $orderId): ?array
    {
        $data = $this->findOrderWithItems($orderId);

        if (! $data) {
            return null;
        }

        $order = $data['order'];
        $eventSlug = $order['event_slug'] ?? DEFAULT_EVENT;

        return [
            'order' => $order,
            'items' => $data['items'],
            'event_slug' => $eventSlug,
            'statuses' => Order::STATUSES,
            'active_page' => 'orders',
            'header' => [
                'title' => '✏️ Edit Order',
                'subtitle' => $orderId . ' · ' . ($order['company_name'] ?? ''),
                'actions' => [
                    ['label' => '← Back to Orders', 'url' => '/admin/orders?event=' . urlencode($eventSlug)],
                    ['label' => '📄 Invoice', 'url' => '/admin/orders/' . $orderId . '/invoice', 'hx_boost' => false],
                ],
            ],
            'hero_title' => $order['company_name'] ?? $orderId,
            'hero_meta' => $orderId . ' · ' . ($order['contact_name'] ?? '') . ' · Booth ' . ($order['booth_number'] ?? '—'),
            'hero_badge' => $order['status'] ?? 'Pending',
            'hero_initials' => strtoupper(substr(trim($order['company_name'] ?? 'O'), 0, 1)),
        ];
    }

    /**
     * @param  array<string, mixed>  $post
     */
    public function updateOrderFromPost(string $orderId, array $post): void
    {
        $order = Order::find($orderId);

        if (! $order) {
            throw new \InvalidArgumentException('Order not found.');
        }

        $allowedStatuses = Order::STATUSES;
        $status = $post['status'] ?? $order->status;

        if (! in_array($status, $allowedStatuses, true)) {
            throw new \InvalidArgumentException('Invalid status.');
        }

        $order->update([
            'company_name' => trim($post['company_name'] ?? $order->company_name),
            'contact_name' => trim($post['contact_name'] ?? $order->contact_name),
            'email' => trim($post['email'] ?? $order->email),
            'phone' => trim($post['phone'] ?? '') ?: null,
            'address' => trim($post['address'] ?? '') ?: null,
            'tax_id' => trim($post['tax_id'] ?? '') ?: null,
            'booth_number' => trim($post['booth_number'] ?? $order->booth_number),
            'special_instructions' => trim($post['special_instructions'] ?? '') ?: null,
            'payment_reference' => trim($post['payment_reference'] ?? '') ?: null,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function exportCsv(string $eventSlug, ?string $statusFilter = null, ?string $search = null): string
    {
        $orders = $this->listOrdersWithItems($eventSlug, $statusFilter, $search);
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [
            'Order ID', 'Custom Order ID', 'Date', 'Status', 'Company', 'Contact', 'Email', 'Phone',
            'Booth', 'Payment Method', 'Payment Reference', 'Client Payment Reference', 'Payment Verification',
            'Product Code', 'Product Name', 'Color', 'Qty', 'Unit Price (USD)', 'Line Total (USD)',
            'Subtotal (USD)', 'VAT (USD)', 'Order Total (USD)',
        ]);

        foreach ($orders as $entry) {
            $o = $entry['order'];
            $items = $entry['items'];

            $phone = $o['phone'] ?? '';
            if ($phone !== '' && str_starts_with($phone, '+')) {
                $phone = "'" . $phone;
            }

            if ($items === []) {
                fputcsv($handle, [
                    $o['id'],
                    $o['custom_order_id'] ?? '',
                    substr($o['created_at'] ?? '', 0, 10),
                    $o['status'] ?? '',
                    $o['company_name'] ?? '',
                    $o['contact_name'] ?? '',
                    $o['email'] ?? '',
                    $phone,
                    $o['booth_number'] ?? '',
                    $o['payment_method'] ?? '',
                    $o['payment_reference'] ?? '',
                    $o['client_payment_reference'] ?? '',
                    $o['payment_verification_status'] ?? '',
                    '', '', '', '', '', '',
                    $o['subtotal'] ?? 0,
                    $o['vat'] ?? 0,
                    $o['total'] ?? 0,
                ]);
                continue;
            }

            foreach ($items as $index => $item) {
                fputcsv($handle, [
                    $o['id'],
                    $index === 0 ? ($o['custom_order_id'] ?? '') : '',
                    $index === 0 ? substr($o['created_at'] ?? '', 0, 10) : '',
                    $index === 0 ? ($o['status'] ?? '') : '',
                    $index === 0 ? ($o['company_name'] ?? '') : '',
                    $index === 0 ? ($o['contact_name'] ?? '') : '',
                    $index === 0 ? ($o['email'] ?? '') : '',
                    $index === 0 ? $phone : '',
                    $index === 0 ? ($o['booth_number'] ?? '') : '',
                    $index === 0 ? ($o['payment_method'] ?? '') : '',
                    $index === 0 ? ($o['payment_reference'] ?? '') : '',
                    $index === 0 ? ($o['client_payment_reference'] ?? '') : '',
                    $index === 0 ? ($o['payment_verification_status'] ?? '') : '',
                    $item['product_code'] ?? '',
                    $item['product_name'] ?? '',
                    $item['color_name'] ?? '',
                    $item['quantity'] ?? 0,
                    $item['unit_price'] ?? 0,
                    $item['total_price'] ?? 0,
                    $index === 0 ? ($o['subtotal'] ?? 0) : '',
                    $index === 0 ? ($o['vat'] ?? 0) : '',
                    $index === 0 ? ($o['total'] ?? 0) : '',
                ]);
            }
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv ?: '';
    }

    /**
     * @return array{name: string, venue?: string, [string]: mixed}
     */
    private function resolveEvent(string $eventSlug): array
    {
        return EVENTS[$eventSlug] ?? [
            'name' => 'Solar & Storage Live Kenya 2026',
            'venue' => 'Nairobi, Kenya',
        ];
    }

    private function sendOrderNotification(string $orderId, string $mailerMethod): bool
    {
        $data = $this->findOrderWithItems($orderId);

        if (! $data) {
            return false;
        }

        $order = $data['order'];
        $items = $data['items'];
        $event = $this->resolveEvent($order['event_slug'] ?? DEFAULT_EVENT);

        $this->loadLegacyDependencies();

        $invoicePdf = \Invoice::generate($order, $items, $event, $GLOBALS['CONFIG']);

        return \Mailer::$mailerMethod($order, $items, $GLOBALS['CONFIG'], $event, $invoicePdf);
    }

    private function loadLegacyDependencies(): void
    {
        global $CONFIG;

        require_once BASE_PATH . '/core/Invoice.php';
        require_once BASE_PATH . '/core/Mailer.php';
    }
}
