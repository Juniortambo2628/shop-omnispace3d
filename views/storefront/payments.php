<?php
$page_title = 'My Payments - OmniShop';
$header_title = 'My Payments';

ob_start();
?>

<?php if (empty($email)): ?>
<div class="container">
    <?php 
    $lookup_title = '&#128179; My Payments';
    $lookup_action = '/order/payments';
    $lookup_subtitle_suffix = 'payment history and status';
    $lookup_btn_text = 'View Payments';
    include __DIR__ . '/_lookup_form.php';
    ?>
</div>

<?php else: ?>
<div class="container">
    <a href="/order/payments" class="back-link">&#8592; Search Again</a>

    <div class="stats">
        <div class="stat">
            <div class="stat-label">Total Orders</div>
            <div class="stat-val teal"><?php echo $stats['total']; ?></div>
        </div>
        <div class="stat">
            <div class="stat-label">Total Amount</div>
            <div class="stat-val teal" style="font-size:18px;">$<?php echo number_format($stats['total_amount'], 2); ?></div>
        </div>
        <div class="stat">
            <div class="stat-label">Payment Verified</div>
            <div class="stat-val green"><?php echo $stats['verified']; ?></div>
        </div>
    </div>

    <div class="section">
        <h2>&#128179; Payment History</h2>

        <div class="filter-tabs">
            <a href="/order/payments?email=<?php echo urlencode($email); ?>" class="filter-tab <?php echo empty($filter) ? 'active' : ''; ?>">All</a>
            <a href="/order/payments?email=<?php echo urlencode($email); ?>&filter=pending" class="filter-tab <?php echo ($filter === 'pending') ? 'active' : ''; ?>">Pending</a>
            <a href="/order/payments?email=<?php echo urlencode($email); ?>&filter=verified" class="filter-tab <?php echo ($filter === 'verified') ? 'active' : ''; ?>">Verified</a>
            <a href="/order/payments?email=<?php echo urlencode($email); ?>&filter=unverified" class="filter-tab <?php echo ($filter === 'unverified') ? 'active' : ''; ?>">Unverified</a>
        </div>

        <?php if (empty($payments)): ?>
        <div class="empty-state">
            <p style="font-size:48px;margin-bottom:12px;">&#128179;</p>
            <p>No payments found<?php echo $filter ? " with filter: {$filter}" : ''; ?></p>
        </div>
        <?php else: ?>
        <table class="payments-table">
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Date</th>
                    <th>Company</th>
                    <th>Booth</th>
                    <th>Amount (USD)</th>
                    <th>Order Status</th>
                    <th>Payment Method</th>
                    <th>Payment Verification</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $entry): ?>
            <?php $po = $entry['order']; ?>
            <tr>
                <td style="font-weight:600;color:var(--brand-teal);"><?php echo htmlspecialchars($po['custom_order_id'] ?? $po['id']); ?></td>
                <td><?php echo substr($po['created_at'] ?? '', 0, 10); ?></td>
                <td><?php echo htmlspecialchars($po['company_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($po['booth_number'] ?? '—'); ?></td>
                <td style="font-weight:700;">$<?php echo number_format($po['total'] ?? 0, 2); ?></td>
                <td><span class="badge badge-<?php echo htmlspecialchars($po['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($po['status'] ?? 'Pending'); ?></span></td>
                <td><?php echo htmlspecialchars($po['payment_method'] ?? '—'); ?></td>
                <td><span class="badge badge-<?php echo htmlspecialchars($po['payment_verification_status'] ?? 'unverified'); ?>"><?php echo htmlspecialchars(ucfirst($po['payment_verification_status'] ?? 'unverified')); ?></span></td>
                <td>
                    <a href="/order/<?php echo urlencode($po['id']); ?>/invoice" class="action-btn action-btn-outline" target="_blank" style="padding:5px 10px;font-size:11px;">&#128424; Invoice</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script src="/static/js/storefront.js"></script>
<?php
$page_content = ob_get_clean();

$page_css = '<style>
    .back-link { margin-bottom: 16px; }
    .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px; }
    .stat { background: #fff; border-radius: 10px; padding: 18px 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
    .stat-label { font-size: 11px; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-val { font-size: 22px; font-weight: 700; margin-top: 6px; }
    .stat-val.teal { color: var(--brand-teal); }
    .stat-val.amber { color: #F59E0B; }
    .stat-val.green { color: #10B981; }
    .payments-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .payments-table th { background: var(--brand-teal); color: #fff; padding: 10px 14px; text-align: left; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
    .payments-table td { padding: 12px 14px; border-bottom: 1px solid var(--color-border-light); }
    .payments-table tr:hover td { background: #f9fffe; }
    .filter-tabs { display: flex; gap: 8px; margin-bottom: 16px; }
    .filter-tab { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1px solid var(--color-border); background: #fff; color: var(--color-text-secondary); transition: all 0.2s; text-decoration: none; }
    .filter-tab.active { background: var(--brand-teal); color: #fff; border-color: var(--brand-teal); }
    @media (max-width: 768px) { .stats { grid-template-columns: 1fr; } }
</style>';

include __DIR__ . '/_layout.php';
