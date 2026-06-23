<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersLegacyViews;
use App\Services\AdminOrderService;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    use RendersLegacyViews;

    public function __construct(
        private readonly OrderService $orders,
        private readonly AdminOrderService $adminOrders,
        private readonly ProductService $products
    ) {}

    public function checkout(string $slug): never
    {
        if (! $this->products->resolveEvent($slug)) {
            $this->redirect('/');
        }

        global $CONFIG;

        $this->render('checkout', [
            'event_slug' => $slug,
            'event' => EVENTS[$slug],
            'config' => $CONFIG,
        ]);
    }

    public function trackingForm(): never
    {
        $eventSlug = request()->query('event', DEFAULT_EVENT);

        $this->render('storefront/tracking', [
            'event_slug' => $eventSlug,
            'email' => null,
            'history' => [],
            'selected_order' => null,
            'selected_items' => [],
        ]);
    }

    public function trackingPortal(): never
    {
        $eventSlug = request()->query('event', DEFAULT_EVENT);
        $email = request()->query('email', '');
        $orderId = request()->query('order');

        $history = $this->adminOrders->getClientOrderHistory($email);
        $selectedOrder = null;
        $selectedItems = [];

        if ($orderId) {
            foreach ($history as $entry) {
                if ($entry['order']['id'] === $orderId) {
                    $selectedOrder = $entry['order'];
                    $selectedItems = $entry['items'];
                    break;
                }
            }
        } elseif (!empty($history)) {
            $selectedOrder = $history[0]['order'];
            $selectedItems = $history[0]['items'];
        }

        $this->render('storefront/tracking', [
            'event_slug' => $eventSlug,
            'email' => $email,
            'history' => $history,
            'selected_order' => $selectedOrder,
            'selected_items' => $selectedItems,
        ]);
    }

    public function orderHistory(): never
    {
        $email = request()->query('email', '');
        $search = request()->query('search', '');
        $invoiceQuery = request()->query('q', '');
        $orderId = request()->query('order');

        $orders = [];
        $total = 0;

        if ($invoiceQuery && ! $email) {
            $found = $this->adminOrders->findOrderWithItems($invoiceQuery);
            if ($found) {
                $orders = [$found];
                $total = 1;
                $email = $found['order']['email'] ?? '';
            }
        } elseif ($email) {
            $result = $this->adminOrders->listAllOrdersPaginated($search, '', 1, 200, $email);
            $orders = $result['orders'];
            $total = $result['total'];
        }

        $selectedOrder = null;
        $selectedItems = [];

        if ($orderId) {
            $data = $this->adminOrders->findOrderWithItems($orderId);
            if ($data) {
                $selectedOrder = $data['order'];
                $selectedItems = $data['items'];
            }
        }

        $this->render('storefront/history', [
            'email' => $email,
            'orders' => $orders,
            'total' => $total,
            'search' => $search === '1' ? '' : $search,
            'search_mode' => $search,
            'invoice_query' => $invoiceQuery,
            'selected_order' => $selectedOrder,
            'selected_items' => $selectedItems,
        ]);
    }

    public function paymentsOverview(): never
    {
        $email = request()->query('email', '');
        $filter = request()->query('filter', '');

        $payments = $email ? $this->adminOrders->getClientOrderHistory($email) : [];

        if ($filter && !empty($payments)) {
            $payments = array_filter($payments, function ($entry) use ($filter) {
                $verification = $entry['order']['payment_verification_status'] ?? 'unverified';
                return $verification === $filter;
            });
            $payments = array_values($payments);
        }

        $stats = [
            'total' => count($payments),
            'total_amount' => 0,
            'verified' => 0,
        ];

        foreach ($payments as $entry) {
            $stats['total_amount'] += (float) ($entry['order']['total'] ?? 0);
            if (($entry['order']['payment_verification_status'] ?? '') === 'verified') {
                $stats['verified']++;
            }
        }

        $this->render('storefront/payments', [
            'email' => $email,
            'payments' => $payments,
            'filter' => $filter,
            'stats' => $stats,
        ]);
    }

    public function paymentReferenceForm(): never
    {
        $email = request()->query('email', '');
        $orders = [];

        if ($email) {
            try {
                $orders = $this->adminOrders->getClientOrderHistory($email);
            } catch (\Throwable $e) {
                Log::error('paymentReferenceForm failed', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->render('storefront/payment_reference', [
            'email' => $email,
            'orders' => $orders,
            'success' => false,
            'submitted_ref' => '',
            'submitted_order_id' => '',
        ]);
    }

    public function payForOrder(string $id): never
    {
        global $CONFIG;

        $data = $this->adminOrders->findOrderWithItems($id);

        if (! $data) {
            $this->redirect('/order/history');
        }

        $order = $data['order'];
        $items = $data['items'];
        $event = EVENTS[$order['event_slug']] ?? null;

        $bankDetails = $CONFIG['bank_transfer_details'] ?? '';
        $paypalLink = $CONFIG['paypal_payment_link'] ?? '#';
        $portalUrl = $CONFIG['payment_portal_url'] ?? '#';
        $paymentNote = $CONFIG['invoice_payment_note'] ?? 'Please make payment within 14 days quoting your Invoice Number.';
        $bankWarning = $CONFIG['bank_charge_warning_text'] ?? 'ALL BANK CHARGES TO BE BORNE BY SENDER';
        $contactEmail = $CONFIG['contact_email'] ?? 'solarandstoragelive@omnispace3d.com';
        $contactPhone = $CONFIG['contact_phone'] ?? '+254 731 001 723';

        $this->render('storefront/pay', [
            'order' => $order,
            'items' => $items,
            'event' => $event,
            'bank_details' => $bankDetails,
            'paypal_link' => $paypalLink,
            'portal_url' => $portalUrl,
            'payment_note' => $paymentNote,
            'bank_warning' => $bankWarning,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
        ]);
    }

    public function submitPaymentReferenceFromPay(string $id, Request $request): never
    {
        $email = $request->input('email', '');
        $paymentRef = trim($request->input('payment_reference', ''));

        if (! $email || ! $paymentRef) {
            $this->redirect("/order/{$id}/pay?error=missing");
        }

        $data = $this->adminOrders->findOrderWithItems($id);

        if (! $data || $data['order']['email'] !== $email) {
            $this->redirect("/order/{$id}/pay?error=unauthorized");
        }

        $this->adminOrders->updateClientPaymentReference($id, $paymentRef);

        $this->redirect("/order/{$id}/pay?submitted=1");
    }

    public function submitPaymentReference(Request $request): never
    {
        $email = $request->input('email', '');
        $orderId = $request->input('order_id', '');
        $paymentRef = trim($request->input('payment_reference', ''));

        if (!$email || !$orderId || !$paymentRef) {
            $this->redirect('/order/payment-reference?email=' . urlencode($email));
        }

        $data = $this->adminOrders->findOrderWithItems($orderId);

        if (!$data || $data['order']['email'] !== $email) {
            $this->redirect('/order/payment-reference?email=' . urlencode($email));
        }

        $this->adminOrders->updateClientPaymentReference($orderId, $paymentRef);

        $this->render('storefront/payment_reference', [
            'email' => $email,
            'orders' => [],
            'success' => true,
            'submitted_ref' => $paymentRef,
            'submitted_order_id' => $data['order']['custom_order_id'] ?? $orderId,
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        ini_set('html_errors', '0');

        $data = json_decode($request->getContent(), true);

        if (! $data) {
            Log::error('Order creation failed: Invalid JSON input', [
                'input' => $request->getContent(),
            ]);

            return response()->json(['error' => 'Invalid data'], 400);
        }

        Log::info('Attempting to create order', [
            'event' => $data['event_slug'] ?? 'unknown',
            'email' => $data['email'] ?? 'unknown',
        ]);

        global $CONFIG;

        try {
            $orderId = $this->orders->create($data, $CONFIG);

            Log::info('Order created successfully', ['order_id' => $orderId]);

            return response()->json(['success' => true, 'order_id' => $orderId]);
        } catch (Throwable $e) {
            Log::error('Order creation failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function confirmation(string $id): never
    {
        $data = $this->adminOrders->findOrderWithItems($id);

        if (! $data) {
            $this->redirect('/');
        }

        $event = EVENTS[$data['order']['event_slug']] ?? null;

        $this->render('confirmation', [
            'order' => $data['order'],
            'items' => $data['items'],
            'event' => $event,
        ]);
    }

    public function downloadInvoice(string $id): never
    {
        $pdf = $this->adminOrders->generateInvoicePdf($id);

        if (! $pdf) {
            die('Order not found');
        }

        $this->sendPdfResponse($pdf, 'Invoice-' . $id . '.pdf');
    }
}
