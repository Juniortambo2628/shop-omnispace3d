<?php $page_title = 'Track Order - OmniShop'; ?>
<?php include __DIR__ . '/_head.php'; ?>
    <style>
        .container { max-width: 1100px; margin: 0 auto; padding: 30px 20px; display: grid; grid-template-columns: 1fr 380px; gap: 30px; }
        .section { background: #fff; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); padding: 24px; margin-bottom: 20px; }
        .section h2 { font-size: 18px; font-weight: 700; color: #1a1a1a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .lookup-form { max-width: 500px; margin: 0 auto; padding: 40px 20px; }
        .lookup-form h1 { font-size: 24px; font-weight: 700; color: #1a1a1a; text-align: center; margin-bottom: 8px; }
        .lookup-form .subtitle { font-size: 14px; color: #666; text-align: center; margin-bottom: 24px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 6px; }
        input { width: 100%; padding: 12px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: inherit; margin-bottom: 16px; transition: border 0.2s; }
        input:focus { outline: none; border-color: #0A9696; box-shadow: 0 0 0 3px rgba(10,150,150,0.1); }
        .submit-btn { width: 100%; padding: 14px; background: #0A9696; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 700; font-family: inherit; transition: background 0.2s; }
        .submit-btn:hover { background: #088080; }
        .submit-btn:disabled { background: #ccc; cursor: not-allowed; }

        .order-history-item { padding: 14px; border: 1px solid #eee; border-radius: 8px; margin-bottom: 10px; cursor: pointer; transition: all 0.2s; }
        .order-history-item:hover { border-color: #0A9696; background: #f9fffe; }
        .order-history-item.active { border-color: #0A9696; background: #D6F0EF; }
        .order-history-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .order-id { font-family: monospace; font-weight: 700; color: #0A9696; font-size: 13px; }
        .custom-order-id { font-family: monospace; font-size: 11px; color: #888; }
        .badge { padding: 3px 9px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
        .badge-Pending { background: #FEF3C7; color: #92400E; }
        .badge-Approved { background: #D1FAE5; color: #065F46; }
        .badge-Invoiced { background: #DBEAFE; color: #1E40AF; }
        .badge-Fulfilled { background: #D6F0EF; color: #0A9696; }
        .badge-Cancelled { background: #FEE2E2; color: #991B1B; }
        .order-history-meta { font-size: 12px; color: #888; }
        .order-history-total { font-size: 14px; font-weight: 700; color: #1a1a1a; }

        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; border-bottom: 1px solid #f5f5f5; }
        .detail-row .label { color: #888; }
        .item-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
        .item-name { flex: 1; font-weight: 500; }
        .item-color { font-size: 11px; color: #888; }
        .item-qty { color: #888; min-width: 50px; text-align: center; }
        .item-price { font-weight: 600; color: #0A9696; min-width: 80px; text-align: right; }
        .sum-line { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
        .sum-line.total { font-weight: 700; font-size: 18px; border-top: 2px solid #0A9696; padding-top: 12px; margin-top: 8px; }

        .empty-state { text-align: center; padding: 40px 20px; color: #999; }
        .empty-state a { color: #0A9696; font-weight: 600; text-decoration: none; }

        .summary-card { position: sticky; top: 20px; }
        .action-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; background: #0A9696; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; font-family: inherit; text-decoration: none; transition: background 0.2s; }
        .action-btn:hover { background: #088080; }
        .action-btn-outline { background: #fff; color: #0A9696; border: 1.5px solid #0A9696; }
        .action-btn-outline:hover { background: #D6F0EF; }
        .action-row { display: flex; gap: 10px; margin-top: 16px; flex-wrap: wrap; }

        .back-link { display: inline-block; margin-bottom: 20px; color: #0A9696; font-size: 14px; font-weight: 500; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
            .summary-card { position: static; }
        }
    </style>
</head>
<body>
<?php
$header_title = 'Track Your Order';
$header_right = '<a href="/' . htmlspecialchars($event_slug) . '">&#8592; Back to Catalog</a>';
include __DIR__ . '/_header.php';
?>

<?php if (empty($email)): ?>
<!-- LOOKUP FORM -->
<div class="container" style="grid-template-columns:1fr;">
    <div class="lookup-form section">
        <h1>&#128269; Track Your Order</h1>
        <p class="subtitle">Enter your email address to view your order history and track current orders.</p>
        <form method="GET" action="/order/track">
            <input type="hidden" name="event" value="<?php echo htmlspecialchars($event_slug); ?>">
            <label>Email Address <span style="color:#ef4444;">*</span></label>
            <input type="email" name="email" required placeholder="Enter the email used for your order" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
            <button type="submit" class="submit-btn">Track My Orders</button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ORDER HISTORY + DETAIL -->
<div class="container">
    <div>
        <a href="/order/track?event=<?php echo htmlspecialchars($event_slug); ?>" class="back-link">&#8592; Search Again</a>

        <?php if (empty($history)): ?>
        <div class="section">
            <div class="empty-state">
                <p style="font-size:48px;margin-bottom:12px;">&#128269;</p>
                <p>No orders found for <strong><?php echo htmlspecialchars($email); ?></strong></p>
                <p style="margin-top:8px;"><a href="/order/track?event=<?php echo htmlspecialchars($event_slug); ?>">Try another email</a></p>
            </div>
        </div>
        <?php else: ?>
        <div class="section">
            <h2>&#128203; Your Orders (<?php echo count($history); ?>)</h2>
            <?php foreach ($history as $entry): ?>
            <?php $ho = $entry['order']; ?>
            <div class="order-history-item <?php echo ($selected_order && $selected_order['id'] === $ho['id']) ? 'active' : ''; ?>"
                 onclick="window.location.href='/order/track?email=<?php echo urlencode($email); ?>&order=<?php echo urlencode($ho['id']); ?>&event=<?php echo htmlspecialchars($event_slug); ?>'">
                <div class="order-history-header">
                    <div>
                        <span class="order-id"><?php echo htmlspecialchars($ho['custom_order_id'] ?? $ho['id']); ?></span>
                        <?php if (!empty($ho['custom_order_id']) && $ho['custom_order_id'] !== $ho['id']): ?>
                        <span class="custom-order-id">(<?php echo htmlspecialchars($ho['id']); ?>)</span>
                        <?php endif; ?>
                    </div>
                    <span class="badge badge-<?php echo htmlspecialchars($ho['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($ho['status'] ?? 'Pending'); ?></span>
                </div>
                <div class="order-history-meta">
                    <?php echo htmlspecialchars($ho['company_name'] ?? ''); ?> &bull; Booth <?php echo htmlspecialchars($ho['booth_number'] ?? '—'); ?> &bull; <?php echo substr($ho['created_at'] ?? '', 0, 10); ?>
                </div>
                <div class="order-history-total">$<?php echo number_format($ho['total'] ?? 0, 2); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div>
        <?php if ($selected_order): ?>
        <?php $so = $selected_order; $si = $selected_items; ?>
        <div class="section summary-card">
            <h2>&#128196; Order Details</h2>

            <div class="detail-row"><span class="label">Order ID:</span><span class="order-id"><?php echo htmlspecialchars($so['custom_order_id'] ?? $so['id']); ?></span></div>
            <div class="detail-row"><span class="label">Company:</span><span><?php echo htmlspecialchars($so['company_name'] ?? ''); ?></span></div>
            <div class="detail-row"><span class="label">Contact:</span><span><?php echo htmlspecialchars($so['contact_name'] ?? ''); ?></span></div>
            <div class="detail-row"><span class="label">Booth:</span><span style="font-weight:600;"><?php echo htmlspecialchars($so['booth_number'] ?? '—'); ?></span></div>
            <div class="detail-row"><span class="label">Status:</span><span><span class="badge badge-<?php echo htmlspecialchars($so['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($so['status'] ?? 'Pending'); ?></span></span></div>
            <div class="detail-row"><span class="label">Date:</span><span><?php echo substr($so['created_at'] ?? '', 0, 10); ?></span></div>
            <div class="detail-row"><span class="label">Payment:</span><span><?php echo htmlspecialchars($so['payment_method'] ?? '—'); ?></span></div>

            <h2 style="margin-top:20px;">&#128722; Items</h2>
            <?php foreach ($si as $item): ?>
            <div class="item-row">
                <div class="item-name">
                    <?php echo htmlspecialchars($item['product_name'] ?? ''); ?>
                    <?php if (!empty($item['color_name'])): ?>
                    <br><span class="item-color"><?php echo htmlspecialchars($item['color_name']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="item-qty">x<?php echo (int)($item['quantity'] ?? 0); ?></div>
                <div class="item-price">$<?php echo number_format($item['total_price'] ?? 0, 2); ?></div>
            </div>
            <?php endforeach; ?>

            <div style="margin-top:12px;">
                <div class="sum-line"><span>Subtotal:</span><span>$<?php echo number_format($so['subtotal'] ?? 0, 2); ?></span></div>
                <div class="sum-line"><span>VAT (16%):</span><span>$<?php echo number_format($so['vat'] ?? 0, 2); ?></span></div>
                <div class="sum-line total"><span>Total:</span><span>$<?php echo number_format($so['total'] ?? 0, 2); ?></span></div>
            </div>

            <div class="action-row">
                <a href="/order/<?php echo urlencode($so['id']); ?>/invoice" class="action-btn action-btn-outline" target="_blank">&#128424; Download Invoice</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/_footer.php'; ?>
<?php include __DIR__ . '/_toast.php'; ?>
</body>
</html>
