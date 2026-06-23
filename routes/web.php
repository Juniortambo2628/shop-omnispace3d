<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public admin entry points
Route::any('/admin/login', [AdminController::class, 'login']);
Route::any('/admin/logout', [AdminController::class, 'logout']);

// Super-admin only
Route::middleware('legacy.super')->group(function () {
    Route::post('/admin/products/{id}/delete', [AdminController::class, 'deleteProduct']);
    Route::any('/admin/users', [AdminController::class, 'users']);
    Route::any('/admin/users/{id}/edit', [AdminController::class, 'editUser']);
    Route::post('/admin/users/{id}/toggle-active', [AdminController::class, 'toggleUserActive']);
    Route::any('/admin/settings', [AdminController::class, 'settings']);
    Route::get('/admin/invoice-preview', [AdminController::class, 'invoicePreview']);
});

// Admin (authenticated)
Route::middleware('legacy.orders')->group(function () {
    Route::any('/admin', [AdminController::class, 'index']);
    Route::any('/admin/orders', [AdminController::class, 'orders']);
    Route::get('/admin/export', [AdminController::class, 'exportOrders']);
    Route::post('/admin/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
    Route::get('/admin/orders/{id}/edit', [AdminController::class, 'showEditOrder']);
    Route::post('/admin/orders/{id}/edit', [AdminController::class, 'updateOrder']);
    Route::post('/admin/orders/{id}/payment-reference', [AdminController::class, 'updateOrderPaymentReference']);
    Route::get('/admin/orders/{id}/invoice', [AdminController::class, 'invoice']);
    Route::post('/admin/orders/{id}/send-invoice', [AdminController::class, 'sendInvoice']);
    Route::post('/admin/orders/{id}/verify-payment', [AdminController::class, 'verifyPayment']);
    Route::any('/admin/stock', [AdminController::class, 'stock']);
    Route::any('/admin/packing/category', [AdminController::class, 'packingList']);
    Route::any('/admin/packing/stand', [AdminController::class, 'packingByBooth']);
    Route::any('/admin/test-email', [AdminController::class, 'testEmail']);
});

Route::middleware('legacy.admin')->group(function () {
    Route::any('/admin/products', [AdminController::class, 'products']);
    Route::any('/admin/products/add', [AdminController::class, 'addProduct']);
    Route::any('/admin/products/{id}/edit', [AdminController::class, 'editProduct']);
    Route::any('/admin/images', [AdminController::class, 'images']);
    Route::post('/admin/images/delete', [AdminController::class, 'deleteImage']);
    Route::any('/admin/profile', [AdminController::class, 'profile']);
});

// Storefront
Route::any('/', [CatalogController::class, 'index']);

Route::middleware('throttle:api')->group(function () {
    Route::post('/api/orders', [OrderController::class, 'createOrder']);
});

Route::get('/order/{id}/confirmation', [OrderController::class, 'confirmation']);
Route::get('/order/{id}/invoice', [OrderController::class, 'downloadInvoice']);
Route::get('/order/{id}/pay', [OrderController::class, 'payForOrder']);
Route::post('/order/{id}/pay/submit-ref', [OrderController::class, 'submitPaymentReferenceFromPay']);
Route::get('/order/history', [OrderController::class, 'orderHistory']);
Route::get('/order/payments', [OrderController::class, 'paymentsOverview']);
Route::get('/order/payment-reference', [OrderController::class, 'paymentReferenceForm']);
Route::post('/order/payment-reference/submit', [OrderController::class, 'submitPaymentReference']);
Route::get('/order/track', [OrderController::class, 'trackingForm']);
Route::get('/order/track/lookup', [OrderController::class, 'trackingPortal']);
Route::any('/{slug}/login', [CatalogController::class, 'login']);
Route::any('/{slug}/checkout', [OrderController::class, 'checkout']);
Route::any('/{slug}', [CatalogController::class, 'catalog']);

Route::fallback(function () {
    abort(404, '404 Not Found');
});
