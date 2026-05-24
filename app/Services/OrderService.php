<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
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
        $totals = $this->calculateTotals($data, $config);
        $now = now()->format('Y-m-d H:i:s');

        DB::transaction(function () use ($data, $config, $orderId, $totals, $now) {
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
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($data['items'] as $item) {
                OrderItem::create([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_code' => $item['product_code'],
                    'category' => $this->resolveStoredCategory($item),
                    'color_id' => $item['color_id'] ?? '',
                    'color_name' => $item['color_name'] ?? '',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }
        });

        $order = Order::with('items')->findOrFail($orderId);
        $this->sendOrderNotifications($order, $config);

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

    /**
     * @param  array<string, mixed>  $config
     */
    protected function sendOrderNotifications(Order $order, array $config): void
    {
        require_once base_path('core/Invoice.php');
        require_once base_path('core/Mailer.php');

        $orderRow = $order->toArray();
        $items = $order->items->map(fn (OrderItem $item) => $item->toArray())->all();
        $event = EVENTS[$order->event_slug] ?? ['name' => 'Solar & Storage Live 2026'];

        $invoicePdf = \Invoice::generate($orderRow, $items, $event);

        $customerSubject = 'Order Confirmation — ' . $order->id;
        $customerBody = \Mailer::buildBaseHtml(
            'Order Received',
            'Dear ' . $order->contact_name . ',<br><br>Thank you for your order. We have received it and our team will review it shortly.',
            $event['name']
        );
        $customerBody .= \Mailer::buildItemsTable($items);
        $customerBody .= \Mailer::buildTotalsBlock($orderRow);

        \Mailer::send($order->email, $customerSubject, $customerBody, [
            'Invoice-' . $order->id . '.pdf' => $invoicePdf,
        ]);

        $adminEmail = $config['admin_notification_email']
            ?? ($config['contact_email'] ?? 'admin@omnispace3d.com');

        $adminSubject = 'New Order Received — ' . $order->id;
        $adminBody = \Mailer::buildBaseHtml(
            'New Order Notification',
            'A new order has been placed on OmniShop.',
            $event['name']
        );
        $adminBody .= \Mailer::buildItemsTable($items);
        $adminBody .= \Mailer::buildTotalsBlock($orderRow);

        \Mailer::send($adminEmail, $adminSubject, $adminBody);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function resolveStoredCategory(array $item): string
    {
        $categoryNames = [];

        foreach ($this->products->getCatalog()['CATEGORIES'] as $cat) {
            $categoryNames[$cat['id']] = $cat['name'];
        }

        $raw = trim((string) ($item['category'] ?? ''));

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

        if ($code !== '') {
            foreach ($this->products->getMergedProducts() as $product) {
                if (strtoupper((string) ($product['code'] ?? '')) === $code) {
                    $catId = $product['category_id'] ?? '';

                    if ($catId !== '' && isset($categoryNames[$catId])) {
                        return $categoryNames[$catId];
                    }
                }
            }
        }

        return $raw;
    }
}
