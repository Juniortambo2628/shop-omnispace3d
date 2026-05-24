<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersLegacyViews;
use App\Services\AdminOrderService;
use App\Services\OrderService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function createOrder(Request $request): JsonResponse
    {
        ini_set('html_errors', '0');

        $data = json_decode($request->getContent(), true);

        if (! $data) {
            \Log::error('Order creation failed: Invalid JSON input', [
                'input' => $request->getContent(),
            ]);

            return response()->json(['error' => 'Invalid data'], 400);
        }

        \Log::info('Attempting to create order', [
            'event' => $data['event_slug'] ?? 'unknown',
            'email' => $data['email'] ?? 'unknown',
        ]);

        global $CONFIG;

        try {
            $orderId = $this->orders->create($data, $CONFIG);

            \Log::info('Order created successfully', ['order_id' => $orderId]);

            return response()->json(['success' => true, 'order_id' => $orderId]);
        } catch (Throwable $e) {
            \Log::error('Order creation failed: ' . $e->getMessage(), [
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

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Invoice-' . $id . '.pdf"');
        echo $pdf;
        exit;
    }
}
