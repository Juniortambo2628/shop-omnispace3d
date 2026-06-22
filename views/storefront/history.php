<?php $page_title = 'Order History - OmniShop'; ?>
<?php include __DIR__ . '/_head.php'; ?>
    <style>
        .container { max-width: 1100px; margin: 0 auto; padding: 30px 20px; }
        .section { background: #fff; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); padding: 24px; margin-bottom: 20px; }
        .section h2 { font-size: 18px; font-weight: 700; color: #1a1a1a; margin-bottom: 16px; }

        .filter-bar { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 16px; }
        .search-input { flex: 1; min-width: 200px; padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; font-family: inherit; }
        .search-input:focus { outline: none; border-color: #0A9696; box-shadow: 0 0 0 3px rgba(10,150,150,0.1); }
        .filter-select { padding: 10px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; font-family: inherit; background: #fff; cursor: pointer; }
        .filter-select:focus { outline: none; border-color: #0A9696; }
        .filter-btn { padding: 10px 18px; background: #0A9696; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; font-family: inherit; cursor: pointer; transition: background 0.2s; }
        .filter-btn:hover { background: #088080; }
        .clear-btn { padding: 10px 14px; background: #fff; color: #888; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; font-family: inherit; cursor: pointer; text-decoration: none; }
        .clear-btn:hover { background: #f5f5f5; }

        .orders-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .orders-table th { background: #0A9696; color: #fff; padding: 10px 14px; text-align: left; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        .orders-table td { padding: 12px 14px; border-bottom: 1px solid #f0f0f0; }
        .orders-table tr:hover td { background: #f9fffe; }
        .orders-table tr.active td { background: #D6F0EF; }
        .orders-table a { color: #0A9696; text-decoration: none; font-weight: 600; }
        .orders-table a:hover { text-decoration: underline; }

        .badge { padding: 3px 9px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
        .badge-Pending { background: #FEF3C7; color: #92400E; }
        .badge-Approved { background: #D1FAE5; color: #065F46; }
        .badge-Invoiced { background: #DBEAFE; color: #1E40AF; }
        .badge-Fulfilled { background: #D6F0EF; color: #0A9696; }
        .badge-Cancelled { background: #FEE2E2; color: #991B1B; }

        .order-detail { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; border-bottom: 1px solid #f5f5f5; }
        .detail-row .label { color: #888; }
        .item-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
        .item-name { flex: 1; font-weight: 500; }
        .item-color { font-size: 11px; color: #888; }
        .item-qty { color: #888; min-width: 50px; text-align: center; }
        .item-price { font-weight: 600; color: #0A9696; min-width: 80px; text-align: right; }
        .sum-line { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
        .sum-line.total { font-weight: 700; font-size: 18px; border-top: 2px solid #0A9696; padding-top: 12px; margin-top: 8px; }

        .action-btn { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #0A9696; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 600; font-family: inherit; text-decoration: none; transition: background 0.2s; }
        .action-btn:hover { background: #088080; }
        .action-btn-outline { background: #fff; color: #0A9696; border: 1.5px solid #0A9696; }
        .action-btn-outline:hover { background: #D6F0EF; }

        .empty-state { text-align: center; padding: 60px 20px; color: #999; }

        .pagination { display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 20px; }
        .pagination a, .pagination span { padding: 8px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s; }
        .pagination a { background: #fff; color: #0A9696; border: 1px solid #ddd; }
        .pagination a:hover { background: #D6F0EF; border-color: #0A9696; }
        .pagination .active { background: #0A9696; color: #fff; border: 1px solid #0A9696; }
        .pagination .disabled { color: #ccc; pointer-events: none; }
        .results-count { font-size: 12px; color: #888; text-align: center; margin-top: 12px; }

        @media (max-width: 768px) { .order-detail { grid-template-columns: 1fr; } .filter-bar { flex-direction: column; } .search-input { min-width: 100%; } }
    </style>
</head>
<body>
<?php
$header_title = 'Order History';
include __DIR__ . '/_header.php';
?>

<div class="container">
    <div class="section">
        <h2>&#128203; Order History</h2>

        <form method="GET" action="/order/history" class="filter-bar">
            <input type="text" name="search" class="search-input" placeholder="Search company, contact, order ID, booth, email..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="status" class="filter-select">
                <option value="">All Statuses</option>
                <?php foreach (['Pending', 'Approved', 'Invoiced', 'Fulfilled', 'Cancelled'] as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $status_filter === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="filter-btn">Search</button>
            <?php if ($search || $status_filter): ?>
            <a href="/order/history" class="clear-btn">Clear</a>
            <?php endif; ?>
        </form>

        <?php if (empty($orders)): ?>
        <div class="empty-state">
            <p style="font-size:48px;margin-bottom:12px;">&#128269;</p>
            <p>No orders found<?php echo ($search || $status_filter) ? ' matching your criteria' : ''; ?></p>
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
                <td><a href="/order/history?order=<?php echo urlencode($ho['id']); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $page > 1 ? '&page=' . $page : ''; ?>"><?php echo htmlspecialchars($ho['custom_order_id'] ?? $ho['id']); ?></a></td>
                <td><?php echo substr($ho['created_at'] ?? '', 0, 10); ?></td>
                <td><?php echo htmlspecialchars($ho['company_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($ho['contact_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($ho['booth_number'] ?? '—'); ?></td>
                <td style="font-weight:700;">$<?php echo number_format($ho['total'] ?? 0, 2); ?></td>
                <td><span class="badge badge-<?php echo htmlspecialchars($ho['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($ho['status'] ?? 'Pending'); ?></span></td>
                <td><?php echo htmlspecialchars($ho['payment_method'] ?? '—'); ?></td>
                <td style="white-space:nowrap;">
                    <a href="/order/<?php echo urlencode($ho['id']); ?>/invoice" class="action-btn action-btn-outline" target="_blank">Invoice</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <div class="results-count"><?php echo number_format($total); ?> order<?php echo $total !== 1 ? 's' : ''; ?> found</div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $baseUrl = '/order/history?';
            if ($search) $baseUrl .= 'search=' . urlencode($search) . '&';
            if ($status_filter) $baseUrl .= 'status=' . urlencode($status_filter) . '&';
            ?>
            <a href="<?php echo $baseUrl . 'page=' . max(1, $page - 1); ?>" class="<?php echo $page <= 1 ? 'disabled' : ''; ?>">&#8592; Prev</a>
            <?php
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            if ($start > 1): ?>
                <a href="<?php echo $baseUrl . 'page=1'; ?>">1</a>
                <?php if ($start > 2): ?><span class="disabled">...</span><?php endif; ?>
            <?php endif; ?>
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a href="<?php echo $baseUrl . 'page=' . $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><span class="disabled">...</span><?php endif; ?>
                <a href="<?php echo $baseUrl . 'page=' . $total_pages; ?>"><?php echo $total_pages; ?></a>
            <?php endif; ?>
            <a href="<?php echo $baseUrl . 'page=' . min($total_pages, $page + 1); ?>" class="<?php echo $page >= $total_pages ? 'disabled' : ''; ?>">Next &#8594;</a>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if ($selected_order): ?>
    <?php $so = $selected_order; $si = $selected_items; ?>
    <div class="section">
        <h2>&#128196; Order Details — <?php echo htmlspecialchars($so['custom_order_id'] ?? $so['id']); ?></h2>
        <div class="order-detail">
            <div>
                <div class="detail-row"><span class="label">Invoice ID:</span><span style="font-weight:700;color:#0A9696;"><?php echo htmlspecialchars($so['custom_order_id'] ?? $so['id']); ?></span></div>
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

        <h3 style="margin-top:20px;font-size:14px;font-weight:700;color:#0A9696;">&#128722; Items Ordered</h3>
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
            <a href="/order/payment-reference?email=<?php echo urlencode($so['email'] ?? ''); ?>" class="action-btn action-btn-outline">&#128179; Submit Payment Ref</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
<?php include __DIR__ . '/_toast.php'; ?>
</body>
</html>
