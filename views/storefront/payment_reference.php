<?php $page_title = 'Submit Payment Reference - OmniShop'; ?>
<?php include __DIR__ . '/_head.php'; ?>
    <link rel="stylesheet" href="/static/css/components.css">
    <style>
        .container { max-width: 600px; }
        .section h2 { margin-bottom: 6px; }
        .section .subtitle { font-size: 13px; color: #666; margin-bottom: 20px; line-height: 1.6; }
        .order-card { background: #f9fffe; border: 1px solid var(--brand-teal-pale); border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .order-card .order-id { font-family: monospace; font-weight: 700; color: var(--brand-teal); font-size: 15px; }
        .order-card .order-meta { font-size: 12px; color: #888; margin-top: 4px; }
        .order-card .order-total { font-size: 16px; font-weight: 700; color: #1a1a1a; margin-top: 6px; }
        .help-text { font-size: 12px; color: #888; margin-top: -12px; margin-bottom: 18px; }
        .divider { border: none; border-top: 1px solid #eee; margin: 20px 0; }
        .success-box { background: #D1FAE5; border: 1px solid #10B981; border-radius: 8px; padding: 20px; text-align: center; }
        .success-box h3 { color: #065F46; font-size: 16px; margin-bottom: 8px; }
        .success-box p { color: #065F46; font-size: 13px; }
    </style>
</head>
<body class="storefront-portal">
<?php
$header_title = 'Submit Payment Reference';
include __DIR__ . '/_header.php';
?>

<div class="container">
    <?php if (!empty($success)): ?>
    <div class="section">
        <div class="success-box">
            <h3>Payment Reference Submitted</h3>
            <p>Your payment reference <strong><?php echo htmlspecialchars($submitted_ref); ?></strong> has been submitted for order <strong><?php echo htmlspecialchars($submitted_order_id); ?></strong>. Our team will verify it shortly.</p>
        </div>
        <div style="text-align:center;margin-top:16px;">
            <a href="/order/payment-reference?email=<?php echo urlencode($email); ?>" class="btn btn-outline" style="margin-right:8px;">Submit Another</a>
            <a href="/order/history?email=<?php echo urlencode($email); ?>" class="btn btn-outline">View My Orders</a>
        </div>
    </div>

    <?php elseif (!empty($orders)): ?>
    <div class="section">
        <h2>Select Order</h2>
        <p class="subtitle">Choose the order you made a payment for, then enter your payment reference below.</p>

        <?php foreach ($orders as $entry): ?>
        <?php $o = $entry['order']; ?>
        <div class="order-card" style="cursor:pointer;" onclick="selectOrder(<?php echo htmlspecialchars(json_encode($o['id'])); ?>, '<?php echo htmlspecialchars($o['custom_order_id'] ?? $o['id']); ?>')">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="order-id"><?php echo htmlspecialchars($o['custom_order_id'] ?? $o['id']); ?></span>
                <span class="badge badge-<?php echo htmlspecialchars($o['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($o['status'] ?? 'Pending'); ?></span>
            </div>
            <div class="order-meta"><?php echo htmlspecialchars($o['company_name'] ?? ''); ?> &bull; Booth <?php echo htmlspecialchars($o['booth_number'] ?? '—'); ?> &bull; <?php echo substr($o['created_at'] ?? '', 0, 10); ?></div>
            <div class="order-total">$<?php echo number_format($o['total'] ?? 0, 2); ?></div>
            <?php if (!empty($o['client_payment_reference'])): ?>
            <div style="font-size:12px;color:var(--brand-teal);margin-top:4px;">Ref submitted: <?php echo htmlspecialchars($o['client_payment_reference']); ?> (<?php echo htmlspecialchars(ucfirst($o['payment_verification_status'] ?? 'pending')); ?>)</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="section" id="ref-form-section" style="display:none;">
        <h2>Enter Payment Reference</h2>
        <p class="subtitle">Enter the transaction ID or reference number from your bank transfer or payment confirmation.</p>
        <form method="POST" action="/order/payment-reference/submit" id="ref-form">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="hidden" name="order_id" id="selected_order_id" value="">
            <div id="selected-order-display" style="margin-bottom:18px;"></div>
            <label>Payment Reference / Transaction ID <span style="color:#ef4444;">*</span></label>
            <input type="text" name="payment_reference" required placeholder="e.g. TXN-2026-001234 or MPESA-XXXXXXXX" id="payment_ref_input">
            <p class="help-text">This is the reference shown on your bank statement, M-Pesa confirmation, or PayPal receipt.</p>
            <button type="submit" class="submit-btn">Submit Payment Reference</button>
        </form>
    </div>

    <?php elseif (!empty($email)): ?>
    <div class="section">
        <div class="empty-state">
            <p style="font-size:48px;margin-bottom:12px;">&#128269;</p>
            <p>No orders found for <strong><?php echo htmlspecialchars($email); ?></strong></p>
            <p style="margin-top:12px;"><a href="/order/payment-reference" style="color:var(--brand-teal);font-weight:600;">Try another email</a></p>
        </div>
    </div>

    <?php else: ?>
        <?php 
        $lookup_title = '&#128179; Submit Payment Reference';
        $lookup_action = '/order/payment-reference';
        $lookup_subtitle_suffix = 'orders to submit reference for';
        $lookup_btn_text = 'Find My Orders';
        include __DIR__ . '/_lookup_form.php';
        ?>
    <?php endif; ?>
</div>

<script>
function selectOrder(orderId, displayId) {
    document.getElementById('selected_order_id').value = orderId;
    document.getElementById('selected-order-display').innerHTML = '<div class="order-card"><span class="order-id">' + displayId + '</span></div>';
    document.getElementById('ref-form-section').style.display = 'block';
    document.getElementById('ref-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
    document.getElementById('payment_ref_input').focus();
}
</script>

<?php include __DIR__ . '/_footer.php'; ?>
<?php include __DIR__ . '/_toast.php'; ?>
<script src="/static/js/storefront.js"></script>
</body>
</html>
