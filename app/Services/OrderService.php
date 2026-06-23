<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Support\CategoryResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderService
{
    public function __construct(
        private readonly ProductService $products
    ) {}

    /**
     * Create an order with line items, generate invoice, and send notification emails.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $config
     * @return string The new order ID
     *
     * @throws Throwable
     */
    public function create(array $data, array $config): string
    {
        $orderId = $this->generateOrderId();
        $customOrderId = $this->generateCustomOrderId($data['event_slug']);
        $totals = $this->calculateTotals($data, $config);
        $now = now()->format('Y-m-d H:i:s');

        DB::transaction(function () use ($data, $config, $orderId, $customOrderId, $totals, $now) {
            Order::create([
                'id' => $orderId,
                'event_slug' => $data['event_slug'],
                'company_name' => $data['company_name'],
                'contact_name' => $data['contact_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? '',
                'address' => $data['address'] ?? '',
                'tax_id' => $data['tax_id'] ?? '',
                'booth_number' => $data['booth_number'],
                'special_instructions' => $data['special_instructions'] ?? '',
                'payment_method' => $data['payment_method'],
                'subtotal' => $totals['subtotal'],
                'vat' => $totals['vat'],
                'total' => $totals['total'],
                'status' => 'Pending',
                'custom_order_id' => $customOrderId,
                'payment_verification_status' => 'unverified',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($data['items'] as $item) {
                $categoryNames = [];
                foreach ($this->products->getCatalog()['CATEGORIES'] as $cat) {
                    $categoryNames[$cat['id']] = $cat['name'];
                }
                $productCategoryByCode = [];
                foreach ($this->products->getMergedProducts() as $product) {
                    $code = strtoupper(trim((string) ($product['code'] ?? '')));
                    if ($code !== '') {
                        $productCategoryByCode[$code] = $product['category_id'] ?? '';
                    }
                }

                OrderItem::create([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_code' => $item['product_code'],
                    'category' => CategoryResolver::resolve($item, $categoryNames, $productCategoryByCode),
                    'color_id' => $item['color_id'] ?? '',
                    'color_name' => $item['color_name'] ?? '',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }
        });

        $order = Order::with('items')->findOrFail($orderId);

        try {
            $this->sendOrderNotifications($order, $config);
        } catch (Throwable $e) {
            Log::error('Order saved but notification failed: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'exception' => get_class($e),
            ]);
        }

        return $orderId;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $config
     * @return array{subtotal: float, vat: float, total: float}
     */
    public function calculateTotals(array $data, array $config): array
    {
        $subtotal = $data['subtotal'] ?? 0;

        if ($subtotal == 0) {
            foreach ($data['items'] as $item) {
                $subtotal += $item['total_price'];
            }
        }

        $vatRate = $config['vat_rate'] ?? 16;
        $vat = $data['vat'] ?? ($subtotal * $vatRate / 100);
        $total = $data['total'] ?? ($subtotal + $vat);

        return [
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $total,
        ];
    }

    public function generateOrderId(): string
    {
        return strtoupper(bin2hex(random_bytes(4)));
    }

    public function generateCustomOrderId(string $eventSlug): string
    {
        $eventShortCode = $this->getEventShortCode($eventSlug);
        $prefix = "OMN-{$eventShortCode}-";

        $lastOrder = \App\Models\Order::where('event_slug', $eventSlug)
            ->where('custom_order_id', 'like', $prefix . '%')
            ->orderByDesc('custom_order_id')
            ->first();

        $nextNumber = 1;
        if ($lastOrder && $lastOrder->custom_order_id) {
            $lastNumber = (int) substr($lastOrder->custom_order_id, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    private function getEventShortCode(string $eventSlug): string
    {
        $events = defined('EVENTS') ? EVENTS : [];
        $event = $events[$eventSlug] ?? null;

        if ($event) {
            if (isset($event['short_code'])) {
                return strtoupper($event['short_code']);
            }
            if (isset($event['short_name'])) {
                return strtoupper(preg_replace('/[^A-Z0-9]/', '', $event['short_name']));
            }
        }

        return strtoupper(substr($eventSlug, 0, 6));
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function sendOrderNotifications(Order $order, array $config): void
    {
        require_once base_path('core/Mailer.php');

        $orderRow = $order->toArray();
        $items = $order->items->map(fn (OrderItem $item) => $item->toArray())->all();
        $event = EVENTS[$order->event_slug] ?? ['name' => 'Solar & Storage Live 2026'];

        \Mailer::sendNewOrderEmail($orderRow, $items, $config, $event);

        $adminEmail = $config['admin_notification_email']
            ?? ($config['contact_email'] ?? 'admin@omnispace3d.com');

        $customId = $order->custom_order_id ?? $order->id;
        $adminSubject = 'New Order Received — ' . $customId;
        $adminBody = \Mailer::buildBaseHtml(
            'New Order Notification',
            'A new order has been placed on OmniShop.<br><br><strong>Order ID:</strong> ' . htmlspecialchars($customId) . '<br><strong>Company:</strong> ' . htmlspecialchars($order->company_name) . '<br><strong>Contact:</strong> ' . htmlspecialchars($order->contact_name) . '<br><strong>Total:</strong> $' . number_format($order->total, 2),
            $event['name']
        );

        \Mailer::send($adminEmail, $adminSubject, $adminBody);
    }
}
