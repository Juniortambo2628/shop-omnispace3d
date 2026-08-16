<?php $active_page = 'orders'; ?>

<div class="container">
  <?php include __DIR__ . '/_header.php'; ?>

  <div class="stats">
    <div class="stat">
      <div class="stat-label">Total Orders</div>
      <div class="stat-val teal"><?php echo (int)$stats['total_orders']; ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Pending Review</div>
      <div class="stat-val amber"><?php echo (int)($stats['by_status']['Pending'] ?? 0); ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Approved</div>
      <div class="stat-val green"><?php echo (int)($stats['by_status']['Approved'] ?? 0); ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Invoiced / Paid</div>
      <div class="stat-val blue"><?php echo (int)(($stats['by_status']['Invoiced'] ?? 0) + ($stats['by_status']['Fulfilled'] ?? 0)); ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Total Revenue (USD)</div>
      <div class="stat-val teal" style="font-size:18px">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
    </div>
  </div>

  <?php if (!empty($stock_data)): ?>
  <div class="stock-section">
    <h3>
      <span>📦 Top Items by Stock Utilisation</span>
      <a href="/admin/stock" class="btn btn-outline btn-sm">Manage All Stock →</a>
    </h3>
    <?php foreach ($stock_data as $s): ?>
    <?php if (!empty($s['stock_limit'])): ?>
    <div class="stock-row">
      <span class="stock-name" title="<?php echo htmlspecialchars($s['product_name']); ?>"><?php echo htmlspecialchars($s['product_name']); ?></span>
      <div class="stock-bar-wrap">
        <div class="stock-bar <?php if ($s['pct'] >= 100) echo 'bar-crit'; elseif ($s['pct'] >= 80) echo 'bar-warn'; else echo 'bar-ok'; ?>"
             style="width:<?php echo min($s['pct'], 100); ?>%"></div>
      </div>
      <span class="stock-pct <?php if ($s['pct'] >= 100) echo 'bar-crit'; elseif ($s['pct'] >= 80) echo 'bar-warn'; else echo 'bar-ok'; ?>">
        <?php echo $s['pct']; ?>%
      </span>
      <span class="stock-nums"><?php echo $s['total_ordered']; ?>/<?php echo $s['stock_limit']; ?></span>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
    <div style="font-size:11px;color:var(--color-text-muted);margin-top:8px">
      Only items with stock limits set are shown.
      <a href="/admin/stock" class="btn btn-outline" style="color:var(--brand-teal)">Set limits →</a>
    </div>
  </div>
  <?php endif; ?>

  <div class="filters">
    <div style="flex:1">
        <?php include __DIR__ . '/_filters.php'; ?>
    </div>
    <span style="font-size:12px;color:var(--color-text-muted);white-space:nowrap"><?php echo count($orders); ?> order(s)</span>
  </div>

  <div class="table-wrap">
    <?php if (empty($orders)): ?>
    <div class="empty">
      <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="var(--brand-teal-pale)" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:15px">
        <circle cx="9" cy="21" r="1"></circle>
        <circle cx="20" cy="21" r="1"></circle>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
      </svg>
      <div style="margin-top:10px">No orders found.</div>
    </div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th style="width:32px"></th>
          <th>Order ID</th>
          <th>Invoice ID</th>
          <th>Company / Contact</th>
          <th>Stand</th>
          <th>Date</th>
          <th>Total (USD)</th>
          <th>Payment</th>
          <th>Verification</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($orders as $entry): ?>
      <?php $o = $entry['order']; $oi = $entry['items']; ?>
      <tr onclick="toggleDetail('<?php echo $o['id']; ?>')" style="cursor:pointer">
        <td style="text-align:center;color:var(--color-text-muted);font-size:11px" id="arrow_<?php echo $o['id']; ?>">▶</td>
        <td><span class="order-id"><?php echo htmlspecialchars($o['id']); ?></span></td>
        <td><span class="custom-order-id"><?php echo htmlspecialchars($o['custom_order_id'] ?? '—'); ?></span></td>
        <td>
          <div style="font-weight:600"><?php echo htmlspecialchars($o['company_name'] ?? ''); ?></div>
          <div style="font-size:11px;color:var(--color-text-muted)"><?php echo htmlspecialchars($o['contact_name'] ?? ''); ?></div>
        </td>
        <td style="font-weight:600"><?php echo htmlspecialchars($o['booth_number'] ?? '—'); ?></td>
        <td style="font-size:12px;color:var(--color-text-muted);white-space:nowrap">
          <?php echo substr($o['created_at'] ?? '', 0, 10); ?>
        </td>
        <td style="font-weight:700">$<?php echo number_format($o['total'] ?? 0, 2); ?></td>
        <td style="font-size:12px">
          <?php echo htmlspecialchars($o['payment_method'] ?? '—'); ?>
          <?php if (!empty($o['payment_reference'])): ?>
          <div style="font-size:10px;color:var(--color-text-muted)">Ref: <?php echo htmlspecialchars($o['payment_reference']); ?></div>
          <?php endif; ?>
        </td>
        <td onclick="event.stopPropagation()">
          <span class="badge badge-<?php echo htmlspecialchars($o['payment_verification_status'] ?? 'unverified'); ?>"><?php echo htmlspecialchars(ucfirst($o['payment_verification_status'] ?? 'unverified')); ?></span>
        </td>
        <td onclick="event.stopPropagation()">
          <span class="badge badge-<?php echo htmlspecialchars($o['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($o['status'] ?? 'Pending'); ?></span>
        </td>
        <td onclick="event.stopPropagation()">
          <div class="action-set">
            <?php if (($o['status'] ?? 'Pending') == 'Pending'): ?>
            <button class="sbtn sbtn-approve" onclick="setStatus('<?php echo $o['id']; ?>','Approved')">Approve</button>
            <?php endif; ?>
            <?php if (in_array(($o['status'] ?? 'Pending'), ['Pending','Approved'])): ?>
            <button class="sbtn sbtn-invoice" onclick="setStatus('<?php echo $o['id']; ?>','Invoiced')">Invoice ✉</button>
            <?php endif; ?>
            <?php if (($o['status'] ?? '') == 'Invoiced'): ?>
            <button class="sbtn sbtn-fulfill" onclick="setStatus('<?php echo $o['id']; ?>','Fulfilled')">Fulfilled</button>
            <?php endif; ?>
            <?php if (!in_array(($o['status'] ?? ''), ['Cancelled','Fulfilled'])): ?>
            <button class="sbtn sbtn-cancel" onclick="setStatus('<?php echo $o['id']; ?>','Cancelled')">Cancel</button>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <tr class="detail-row" id="detail_<?php echo $o['id']; ?>">
        <td colspan="11" style="padding:0">
          <div class="detail-inner">
            <div class="detail-grid">
              <div>
                <h4>👤 Client Details</h4>
                <div class="detail-line">Company: <span><?php echo htmlspecialchars($o['company_name'] ?? ''); ?></span></div>
                <div class="detail-line">Contact: <span><?php echo htmlspecialchars($o['contact_name'] ?? ''); ?></span></div>
                <div class="detail-line">Email: <span><?php echo htmlspecialchars($o['email'] ?? ''); ?></span></div>
                <div class="detail-line">Phone: <span><?php echo htmlspecialchars($o['phone'] ?? '—'); ?></span></div>
                <div class="detail-line">Address: <span><?php echo htmlspecialchars($o['address'] ?? '—'); ?></span></div>
                <div class="detail-line">Tax ID / PIN: <span><?php echo htmlspecialchars($o['tax_id'] ?? '—'); ?></span></div>
                <?php if (!empty($o['special_instructions'])): ?>
                <div class="detail-line">Notes: <span><?php echo htmlspecialchars($o['special_instructions']); ?></span></div>
                <?php endif; ?>
              </div>
              <div>
                <h4>💳 Payment &amp; Status</h4>
                <div class="detail-line">Method: <span><?php echo htmlspecialchars($o['payment_method'] ?? '—'); ?></span></div>
                <div class="detail-line" style="margin-bottom:10px">
                  Payment Reference:<br>
                  <input class="pay-ref-input" id="payref_<?php echo $o['id']; ?>"
                         value="<?php echo htmlspecialchars($o['payment_reference'] ?? ''); ?>"
                         placeholder="Add ref / transaction ID…"
                         onclick="event.stopPropagation()">
                  <button class="btn btn-outline btn-sm" style="margin-top:4px"
                          onclick="event.stopPropagation();savePayRef('<?php echo $o['id']; ?>')">
                    Save Ref
                  </button>
                </div>
                <div class="detail-line" style="margin-bottom:10px">
                  Client Payment Ref:<br>
                  <div style="font-size:12px;color:var(--color-text);font-weight:500;padding:5px 9px;background:var(--color-bg);border-radius:var(--radius-sm);border:1px solid var(--color-border);min-height:28px">
                    <?php echo htmlspecialchars($o['client_payment_reference'] ?? '— Not submitted —'); ?>
                  </div>
                </div>
                <div class="detail-line" style="margin-bottom:10px">
                  Payment Verification:<br>
                  <div style="display:flex;gap:6px;margin-top:4px;align-items:center">
                    <span class="badge badge-<?php echo htmlspecialchars($o['payment_verification_status'] ?? 'unverified'); ?>">
                      <?php echo htmlspecialchars(ucfirst($o['payment_verification_status'] ?? 'unverified')); ?>
                    </span>
                    <?php if (($o['payment_verification_status'] ?? 'unverified') === 'unverified'): ?>
                    <button class="sbtn sbtn-approve" onclick="event.stopPropagation();verifyPayment('<?php echo $o['id']; ?>','verified','<?php echo htmlspecialchars($o['client_payment_reference'] ?? ''); ?>')">✓ Verify</button>
                    <button class="sbtn sbtn-cancel" onclick="event.stopPropagation();verifyPayment('<?php echo $o['id']; ?>','rejected')">✗ Reject</button>
                    <?php elseif (($o['payment_verification_status'] ?? '') === 'pending'): ?>
                    <button class="sbtn sbtn-approve" onclick="event.stopPropagation();verifyPayment('<?php echo $o['id']; ?>','verified')">✓ Verify</button>
                    <button class="sbtn sbtn-cancel" onclick="event.stopPropagation();verifyPayment('<?php echo $o['id']; ?>','rejected')">✗ Reject</button>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($o['payment_verified_at'])): ?>
                  <div style="font-size:10px;color:var(--color-text-muted);margin-top:4px">
                    Verified by <?php echo htmlspecialchars($o['payment_verified_by'] ?? '—'); ?> on <?php echo htmlspecialchars($o['payment_verified_at'] ?? ''); ?>
                  </div>
                  <?php endif; ?>
                </div>
                <div class="detail-line">Status: <span class="badge badge-<?php echo htmlspecialchars($o['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($o['status'] ?? ''); ?></span></div>
                <div class="detail-line">Created: <span><?php echo substr($o['created_at'] ?? '', 0, 10); ?></span></div>
              </div>
            </div>
            <h4 style="font-size:12px;font-weight:700;color:var(--brand-teal);margin-bottom:8px;text-transform:uppercase">🛒 Items</h4>
            <table class="items-mini">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Product</th>
                  <th>Color</th>
                  <th style="text-align:center">Qty</th>
                  <th style="text-align:right">Unit Price</th>
                  <th style="text-align:right">Total</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($oi as $item): ?>
                <tr>
                  <td style="font-family:monospace;font-size:11px"><?php echo htmlspecialchars($item['product_code'] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($item['color_name'] ?? '—'); ?></td>
                  <td style="text-align:center;font-weight:700"><?php echo (int)($item['quantity'] ?? 0); ?></td>
                  <td style="text-align:right"><?php echo number_format($item['unit_price'] ?? 0, 2); ?></td>
                  <td style="text-align:right;font-weight:600"><?php echo number_format($item['total_price'] ?? 0, 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                  <td colspan="5" style="text-align:right;color:var(--color-text-muted);padding-top:8px">Subtotal:</td>
                  <td style="text-align:right;padding-top:8px"><?php echo number_format($o['subtotal'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                  <td colspan="5" style="text-align:right;color:var(--color-text-muted)">VAT (16%):</td>
                  <td style="text-align:right"><?php echo number_format($o['vat'] ?? 0, 2); ?></td>
                </tr>
                <tr class="total-row">
                  <td colspan="5" style="text-align:right;font-weight:700;background:var(--brand-teal-pale)">TOTAL (incl. VAT):</td>
                  <td style="text-align:right;font-weight:700;background:var(--brand-teal-pale);color:var(--brand-teal)">
                    $<?php echo number_format($o['total'] ?? 0, 2); ?>
                  </td>
                </tr>
              </tbody>
            </table>
            <div class="detail-actions">
              <a href="/admin/orders/<?php echo $o['id']; ?>/edit" class="btn btn-outline btn-sm">✏️ Edit Order</a>
              <a href="/admin/orders/<?php echo $o['id']; ?>/invoice" target="_blank" class="btn btn-outline btn-sm" hx-boost="false">📄 Download Invoice</a>
              <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();sendInvoice('<?php echo $o['id']; ?>')">📧 Re-send Invoice Email</button>
            </div>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<div class="toast" id="toast"></div>

<script src="/static/js/orders.js"></script>
