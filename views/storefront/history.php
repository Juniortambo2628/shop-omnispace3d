<?php $page_title = 'Order History - OmniShop'; ?>
<?php include __DIR__ . '/_head.php'; ?>
    <link rel="stylesheet" href="/static/css/components.css">
    <style>
        .action-btn { padding: 6px 12px; font-size: 11px; }
        .order-detail { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .filter-bar { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 16px; }
        .search-input { flex: 1; min-width: 200px; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; font-family: inherit; }
        .search-input:focus { outline: none; border-color: var(--brand-teal); box-shadow: 0 0 0 3px rgba(10,150,150,0.1); }
        .filter-select { padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; font-family: inherit; background: #fff; cursor: pointer; }
        .filter-select:focus { outline: none; border-color: var(--brand-teal); }
        .filter-btn { padding: 10px 18px; background: var(--brand-teal); color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; font-family: inherit; cursor: pointer; transition: background 0.2s; }
        .filter-btn:hover { background: var(--brand-teal-dark); }
        .clear-btn { padding: 10px 14px; background: #fff; color: #888; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; font-family: inherit; cursor: pointer; text-decoration: none; }
        .clear-btn:hover { background: #f5f5f5; }
        .orders-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .orders-table th { background: var(--brand-teal); color: #fff; padding: 10px 14px; text-align: left; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        .orders-table td { padding: 12px 14px; border-bottom: 1px solid #f0f0f0; }
        .orders-table tr:hover td { background: #f9fffe; }
        .orders-table tr.active td { background: var(--brand-teal-pale); }
        .orders-table a { color: var(--brand-teal); text-decoration: none; font-weight: 600; }
        .orders-table a:hover { text-decoration: underline; }
        .results-count { font-size: 12px; color: #888; text-align: center; margin-top: 12px; }
        .lookup-tabs { display: flex; gap: 0; margin-bottom: 20px; border-bottom: 2px solid #eee; }
        .lookup-tab { padding: 10px 20px; font-size: 13px; font-weight: 600; color: #888; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s; text-decoration: none; }
        .lookup-tab:hover { color: var(--brand-teal); }
        .lookup-tab.active { color: var(--brand-teal); border-bottom-color: var(--brand-teal); }
        @media (max-width: 768px) { .order-detail { grid-template-columns: 1fr; } .filter-bar { flex-direction: column; } .search-input { min-width: 100%; } }
    </style>
</head>
<body class="storefront-portal">
<?php
$header_title = 'Order History';
include __DIR__ . '/_header.php';
?>

<div class="container">
    <?php if (!$email): ?>
        <?php 
        $lookup_title = '&#128203; Order History';
        $lookup_action = '/order/history';
        $lookup_subtitle_suffix = 'orders';
        $lookup_btn_text = 'Look Up Orders';
        include __DIR__ . '/_lookup_form.php';
        ?>
    <?php else: ?>
    <div class="section">

        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:16px;">
            <div>
                <?php if ($email): ?>
                <span style="font-size:13px;color:#666;">Showing orders for <strong><?php echo htmlspecialchars($email); ?></strong></span>
                <?php elseif ($search && $search !== '1'): ?>
                <span style="font-size:13px;color:#666;">Search results for <strong><?php echo htmlspecialchars($search); ?></strong></span>
                <?php endif; ?>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="/order/history" class="clear-btn">Search by Email</a>
                <a href="/order/history?search=1" class="clear-btn">Search by Invoice</a>
            </div>
        </div>

        <?php if ($email): ?>
        <form method="GET" action="/order/history" class="filter-bar">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="text" name="search" class="search-input" placeholder="Filter by invoice ID, company, contact..." value="<?php echo htmlspecialchars($search === '1' ? '' : $search); ?>">
            <button type="submit" class="filter-btn">Filter</button>
            <?php if ($search && $search !== '1'): ?>
            <a href="/order/history?email=<?php echo urlencode($email); ?>" class="clear-btn">Clear</a>
            <?php endif; ?>
        </form>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
        <div class="empty-state">
            <p style="font-size:48px;margin-bottom:12px;">&#128269;</p>
            <p>No orders found<?php echo $email ? ' for ' . htmlspecialchars($email) : ''; ?><?php echo $search && $search !== '1' ? ' matching your search' : ''; ?></p>
            <p style="margin-top:12px;"><a href="/order/history">Try another search</a></p>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Date</th>
                    <th>Company</th>
                    <th>Contact</th>
                    <th>Booth</th>
                    <th>Total (USD)</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $entry): ?>
            <?php $ho = $entry['order']; ?>
            <tr class="<?php echo ($selected_order && $selected_order['id'] === $ho['id']) ? 'active' : ''; ?>">
                <td><a href="/order/history?email=<?php echo urlencode($email ?: $ho['email'] ?? ''); ?>&order=<?php echo urlencode($ho['id']); ?>"><?php echo htmlspecialchars($ho['custom_order_id'] ?? $ho['id']); ?></a></td>
                <td><?php echo substr($ho['created_at'] ?? '', 0, 10); ?></td>
                <td><?php echo htmlspecialchars($ho['company_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($ho['contact_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($ho['booth_number'] ?? '—'); ?></td>
                <td style="font-weight:700;">$<?php echo number_format($ho['total'] ?? 0, 2); ?></td>
                <td><span class="badge badge-<?php echo htmlspecialchars($ho['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($ho['status'] ?? 'Pending'); ?></span></td>
                <td><?php echo htmlspecialchars($ho['payment_method'] ?? '—'); ?></td>
                <td style="white-space:nowrap;">
                    <a href="/order/<?php echo urlencode($ho['id']); ?>/invoice" class="action-btn action-btn-outline" target="_blank">Invoice</a>
                    <?php if (in_array($ho['status'] ?? '', ['Approved', 'Invoiced'], true) && ($ho['payment_verification_status'] ?? 'unverified') !== 'verified'): ?>
                    <a href="/order/<?php echo urlencode($ho['id']); ?>/pay" class="action-btn action-btn-outline" style="margin-left:6px;">Pay Now</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <div class="results-count"><?php echo number_format($total); ?> order<?php echo $total !== 1 ? 's' : ''; ?> found</div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if ($selected_order): ?>
    <?php $so = $selected_order; $si = $selected_items; ?>
    <div class="section">
        <h2>&#128196; Order Details — <?php echo htmlspecialchars($so['custom_order_id'] ?? $so['id']); ?></h2>
        <div class="order-detail">
            <div>
                <div class="detail-row"><span class="label">Invoice ID:</span><span style="font-weight:700;color:var(--brand-teal);"><?php echo htmlspecialchars($so['custom_order_id'] ?? $so['id']); ?></span></div>
                <div class="detail-row"><span class="label">Company:</span><span><?php echo htmlspecialchars($so['company_name'] ?? ''); ?></span></div>
                <div class="detail-row"><span class="label">Contact:</span><span><?php echo htmlspecialchars($so['contact_name'] ?? ''); ?></span></div>
                <div class="detail-row"><span class="label">Email:</span><span><?php echo htmlspecialchars($so['email'] ?? ''); ?></span></div>
                <?php if (!empty($so['phone'])): ?>
                <div class="detail-row"><span class="label">Phone:</span><span><?php echo htmlspecialchars($so['phone']); ?></span></div>
                <?php endif; ?>
                <div class="detail-row"><span class="label">Booth:</span><span style="font-weight:600;"><?php echo htmlspecialchars($so['booth_number'] ?? '—'); ?></span></div>
            </div>
            <div>
                <div class="detail-row"><span class="label">Status:</span><span><span class="badge badge-<?php echo htmlspecialchars($so['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($so['status'] ?? 'Pending'); ?></span></span></div>
                <div class="detail-row"><span class="label">Date:</span><span><?php echo substr($so['created_at'] ?? '', 0, 10); ?></span></div>
                <div class="detail-row"><span class="label">Payment Method:</span><span><?php echo htmlspecialchars($so['payment_method'] ?? '—'); ?></span></div>
                <?php if (!empty($so['payment_reference'])): ?>
                <div class="detail-row"><span class="label">Payment Ref:</span><span><?php echo htmlspecialchars($so['payment_reference']); ?></span></div>
                <?php endif; ?>
                <?php if (!empty($so['client_payment_reference'])): ?>
                <div class="detail-row"><span class="label">Client Payment Ref:</span><span><?php echo htmlspecialchars($so['client_payment_reference']); ?></span></div>
                <?php endif; ?>
                <div class="detail-row"><span class="label">Payment Verification:</span><span><span class="badge badge-<?php echo htmlspecialchars($so['payment_verification_status'] ?? 'unverified'); ?>"><?php echo htmlspecialchars(ucfirst($so['payment_verification_status'] ?? 'unverified')); ?></span></span></div>
            </div>
        </div>

        <h3 style="margin-top:20px;font-size:14px;font-weight:700;color:var(--brand-teal);">&#128722; Items Ordered</h3>
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

        <div style="margin-top:12px;max-width:300px;margin-left:auto;">
            <div class="sum-line"><span>Subtotal:</span><span>$<?php echo number_format($so['subtotal'] ?? 0, 2); ?></span></div>
            <div class="sum-line"><span>VAT (16%):</span><span>$<?php echo number_format($so['vat'] ?? 0, 2); ?></span></div>
            <div class="sum-line total"><span>Total:</span><span>$<?php echo number_format($so['total'] ?? 0, 2); ?></span></div>
        </div>

        <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
            <a href="/order/<?php echo urlencode($so['id']); ?>/invoice" class="action-btn action-btn-outline" target="_blank">&#128424; Download Invoice PDF</a>
            <?php if (in_array($so['status'] ?? '', ['Approved', 'Invoiced'], true) && ($so['payment_verification_status'] ?? 'unverified') !== 'verified'): ?>
            <a href="/order/<?php echo urlencode($so['id']); ?>/pay" class="action-btn action-btn-outline">&#128179; Make Payment</a>
            <?php endif; ?>
            <a href="/order/payment-reference?email=<?php echo urlencode($so['email'] ?? ''); ?>" class="action-btn action-btn-outline">&#128179; Submit Payment Ref</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
<?php include __DIR__ . '/_toast.php'; ?>
<script src="/static/js/storefront.js"></script>
</body>
</html>
