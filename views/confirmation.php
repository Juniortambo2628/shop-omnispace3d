<?php
$page_title = 'Order Confirmed - OmniShop';
$body_class = '';
$header_title = 'Order Confirmation';

ob_start();
?>

<div class="container">
    <div class="success-icon"><div class="circle">&#10003;</div></div>

    <div class="section" style="text-align:center;">
        <h2 style="color:#10B981;">Order Submitted Successfully!</h2>
        <div class="order-ref">
            Your order reference number is:
            <strong><?php echo htmlspecialchars($order["custom_order_id"] ?? $order["id"]); ?></strong>
        </div>
    </div>

    <div class="section">
        <h2>Order Details</h2>
        <div class="detail-row"><span class="label">Company:</span><span><?php echo htmlspecialchars($order["company_name"]); ?></span></div>
        <div class="detail-row"><span class="label">Contact:</span><span><?php echo htmlspecialchars($order["contact_name"]); ?></span></div>
        <div class="detail-row"><span class="label">Email:</span><span><?php echo htmlspecialchars($order["email"]); ?></span></div>
        <div class="detail-row"><span class="label">Booth:</span><span><?php echo htmlspecialchars($order["booth_number"]); ?></span></div>
        <?php if ($order["phone"]): ?>
        <div class="detail-row"><span class="label">Phone:</span><span><?php echo htmlspecialchars($order["phone"]); ?></span></div>
        <?php endif; ?>
        <div class="detail-row"><span class="label">Date:</span><span><?php echo htmlspecialchars(substr($order["created_at"], 0, 10)); ?></span></div>
        <div class="detail-row"><span class="label">Status:</span><span style="color:#F59E0B;font-weight:600;"><?php echo htmlspecialchars($order["status"]); ?></span></div>
    </div>

    <div class="section">
        <h2>Items Ordered</h2>
        <?php foreach ($items as $item): ?>
        <div class="item-row">
            <div class="item-name">
                <?php echo htmlspecialchars($item["product_name"]); ?>
                <?php if ($item["color_name"]): ?>
                <br><span class="item-color"><?php echo htmlspecialchars($item["color_name"]); ?></span>
                <?php endif; ?>
            </div>
            <div class="item-qty">x<?php echo htmlspecialchars($item["quantity"]); ?></div>
            <div class="item-price">$<?php echo number_format($item["total_price"], 2); ?></div>
        </div>
        <?php endforeach; ?>
        <div style="margin-top:16px;">
            <div class="sum-line"><span>Subtotal:</span><span>$<?php echo number_format($order["subtotal"], 2); ?></span></div>
            <div class="sum-line"><span>VAT (16%):</span><span>$<?php echo number_format($order["vat"], 2); ?></span></div>
            <div class="sum-line total"><span>Total:</span><span>$<?php echo number_format($order["total"], 2); ?></span></div>
        </div>
    </div>

    <div class="email-note">
        &#9993; A confirmation has been logged for <strong><?php echo htmlspecialchars($order["email"]); ?></strong>.<br>
        Our team will review your order and follow up within 24 hours.
    </div>

    <?php global $CONFIG; ?>
    <div class="contact-box">
        <p>Questions about your order? Contact us:</p>
        <p style="margin-top:8px;"><strong><?php echo htmlspecialchars($CONFIG["contact_email"]); ?></strong> | <?php echo htmlspecialchars($CONFIG["contact_phone"]); ?></p>
    </div>

    <div class="btn-row">
        <a href="/<?php echo htmlspecialchars($order['event_slug']); ?>" class="btn btn-primary">&#8592; Back to Catalog</a>
        <a href="/order/<?php echo htmlspecialchars($order['id']); ?>/invoice" class="btn btn-outline">&#128424; Download PDF Invoice</a>
        <a href="/order/history?email=<?php echo urlencode($order['email']); ?>" class="btn btn-outline">&#128203; View My Orders</a>
    </div>
</div>
<?php
$page_content = ob_get_clean();

$page_css = '<style>
    .container { max-width: 700px; padding: 40px 20px; }
    .success-icon { text-align: center; margin-bottom: 24px; }
    .success-icon .circle { display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; background: #D1FAE5; border-radius: 50%; font-size: 40px; animation: pop 0.5s ease; }
    @keyframes pop { 0% { transform: scale(0); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
    .order-ref { text-align: center; font-size: 14px; color: #666; margin-bottom: 8px; }
    .order-ref strong { font-size: 22px; color: var(--brand-teal); display: block; margin-top: 4px; }
    .email-note { background: var(--brand-teal-pale); border-radius: 8px; padding: 16px; text-align: center; font-size: 13px; color: var(--brand-teal); margin: 24px 0; }
    .contact-box { background: #f9f9f9; border-radius: 8px; padding: 16px; text-align: center; font-size: 13px; color: #666; }
    .btn-row { display: flex; gap: 12px; justify-content: center; margin-top: 24px; }
</style>';

include __DIR__ . '/storefront/_layout.php';
