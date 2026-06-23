<?php $active_page = 'orders'; ?>
<link rel="stylesheet" href="/static/css/components.css">
<style>
/* ── DASHBOARD SPECIFIC STYLES ── */
.stats{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px}
.stat{background:#fff;border-radius:10px;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)}
.stat-label{font-size:11px;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.stat-val{font-size:26px;font-weight:700;margin-top:6px}
.stat-val.teal{color:#0A9696}
.stat-val.amber{color:#F59E0B}
.stat-val.green{color:#10B981}
.stat-val.blue{color:#3B82F6}
.stat-val.red{color:#dc2626}
.stock-section{background:#fff;border-radius:10px;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:20px}
.stock-section h3{font-size:13px;font-weight:700;color:#555;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between}
.stock-row{display:flex;align-items:center;gap:10px;margin-bottom:8px;font-size:12px}
.stock-name{flex:0 0 180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#555}
.filters{background:#fff;border-radius:10px;padding:14px 18px;margin-bottom:14px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;box-shadow:0 1px 4px rgba(0,0,0,.06)}
.filters form{display:flex;gap:10px;flex:1;flex-wrap:wrap;align-items:center}
.filters input,.filters select{min-width:0}
.filters input{flex:1;min-width:180px}
.filters input:focus,.filters select:focus{outline:none;border-color:#0A9696}
.table-wrap{overflow-x:auto}
.custom-order-id{font-size:11px;color:#888;font-family:monospace}
.action-set{display:flex;gap:4px;flex-wrap:wrap}
.sbtn{padding:4px 9px;border-radius:4px;font-size:11px;font-weight:600;font-family:inherit;cursor:pointer;border:1px solid;background:#fff;white-space:nowrap}
.sbtn:hover{opacity:.85}
.sbtn-approve{color:#10B981;border-color:#10B981}
.sbtn-invoice{color:#3B82F6;border-color:#3B82F6}
.sbtn-fulfill{color:#0A9696;border-color:#0A9696}
.sbtn-cancel{color:#dc2626;border-color:#dc2626}
.detail-row{display:none}
.detail-inner{background:#f9fffe;padding:16px 20px;border-top:1px solid #D6F0EF}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px}
.detail-section h4{font-size:12px;font-weight:700;color:#0A9696;margin-bottom:8px;text-transform:uppercase;letter-spacing:.4px}
.detail-line{font-size:12px;color:#555;margin-bottom:4px}
.detail-line span{color:#333;font-weight:500}
.items-mini{width:100%;border-collapse:collapse;font-size:12px;margin-top:8px}
.items-mini th{background:#D6F0EF;color:#0A9696;padding:6px 10px;text-align:left;font-weight:700}
.items-mini td{padding:6px 10px;border-bottom:1px solid #eee}
.items-mini .total-row td{font-weight:700;background:#D6F0EF;color:#0A9696}
.detail-actions{display:flex;gap:8px;margin-top:14px;flex-wrap:wrap}
.pay-ref-input{padding:5px 9px;border:1px solid #ddd;border-radius:5px;font-size:12px;font-family:inherit;width:180px}
.pay-ref-input:focus{outline:none;border-color:#0A9696}
.empty{text-align:center;padding:60px 20px;color:#bbb}
@media(max-width:900px){.stats{grid-template-columns:repeat(2,1fr)}.detail-grid{grid-template-columns:1fr}}
@media(max-width:600px){.stats{grid-template-columns:1fr}}
</style>

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
    <div style="font-size:11px;color:#bbb;margin-top:8px">
      Only items with stock limits set are shown.
      <a href="/admin/stock" class="btn btn-outline" style="color:#0A9696">Set limits →</a>
    </div>
  </div>
  <?php endif; ?>

  <div class="filters">
    <div style="flex:1">
        <?php include __DIR__ . '/_filters.php'; ?>
    </div>
    <span style="font-size:12px;color:#888;white-space:nowrap"><?php echo count($orders); ?> order(s)</span>
  </div>

  <div class="table-wrap">
    <?php if (empty($orders)): ?>
    <div class="empty">
      <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="#D6F0EF" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:15px">
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
        <td style="text-align:center;color:#bbb;font-size:11px" id="arrow_<?php echo $o['id']; ?>">▶</td>
        <td><span class="order-id"><?php echo htmlspecialchars($o['id']); ?></span></td>
        <td><span class="custom-order-id"><?php echo htmlspecialchars($o['custom_order_id'] ?? '—'); ?></span></td>
        <td>
          <div style="font-weight:600"><?php echo htmlspecialchars($o['company_name'] ?? ''); ?></div>
          <div style="font-size:11px;color:#888"><?php echo htmlspecialchars($o['contact_name'] ?? ''); ?></div>
        </td>
        <td style="font-weight:600"><?php echo htmlspecialchars($o['booth_number'] ?? '—'); ?></td>
        <td style="font-size:12px;color:#888;white-space:nowrap">
          <?php echo substr($o['created_at'] ?? '', 0, 10); ?>
        </td>
        <td style="font-weight:700">$<?php echo number_format($o['total'] ?? 0, 2); ?></td>
        <td style="font-size:12px">
          <?php echo htmlspecialchars($o['payment_method'] ?? '—'); ?>
          <?php if (!empty($o['payment_reference'])): ?>
          <div style="font-size:10px;color:#888">Ref: <?php echo htmlspecialchars($o['payment_reference']); ?></div>
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
                  <div style="font-size:12px;color:#333;font-weight:500;padding:5px 9px;background:#f9fafb;border-radius:4px;border:1px solid #eee;min-height:28px">
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
                  <div style="font-size:10px;color:#888;margin-top:4px">
                    Verified by <?php echo htmlspecialchars($o['payment_verified_by'] ?? '—'); ?> on <?php echo htmlspecialchars($o['payment_verified_at'] ?? ''); ?>
                  </div>
                  <?php endif; ?>
                </div>
                <div class="detail-line">Status: <span class="badge badge-<?php echo htmlspecialchars($o['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($o['status'] ?? ''); ?></span></div>
                <div class="detail-line">Created: <span><?php echo substr($o['created_at'] ?? '', 0, 10); ?></span></div>
              </div>
            </div>
            <h4 style="font-size:12px;font-weight:700;color:#0A9696;margin-bottom:8px;text-transform:uppercase">🛒 Items</h4>
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
                  <td colspan="5" style="text-align:right;color:#888;padding-top:8px">Subtotal:</td>
                  <td style="text-align:right;padding-top:8px"><?php echo number_format($o['subtotal'] ?? 0, 2); ?></td>
                </tr>
                <tr>
                  <td colspan="5" style="text-align:right;color:#888">VAT (16%):</td>
                  <td style="text-align:right"><?php echo number_format($o['vat'] ?? 0, 2); ?></td>
                </tr>
                <tr class="total-row">
                  <td colspan="5" style="text-align:right;font-weight:700;background:#D6F0EF">TOTAL (incl. VAT):</td>
                  <td style="text-align:right;font-weight:700;background:#D6F0EF;color:#0A9696">
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

<script>
function toggleDetail(orderId) {
  var row   = document.getElementById('detail_' + orderId);
  var arrow = document.getElementById('arrow_' + orderId);
  var open  = row.style.display === 'table-row';
  row.style.display   = open ? 'none'       : 'table-row';
  arrow.textContent   = open ? '▶'          : '▼';
}
function setStatus(orderId, status) {
  var labels = {
    'Approved': { title: 'Approve Order?', text: 'This will mark the order as approved.', icon: 'question', confirm: 'Yes, Approve' },
    'Invoiced': { title: 'Send Invoice?', text: 'This will mark as invoiced and send the payment confirmation email.', icon: 'info', confirm: 'Yes, Invoice' },
    'Fulfilled': { title: 'Mark Fulfilled?', text: 'This will close the order as fulfilled.', icon: 'success', confirm: 'Yes, Fulfilled' },
    'Cancelled': { title: 'Cancel Order?', text: 'This action cannot be undone. The order will be cancelled.', icon: 'warning', confirm: 'Yes, Cancel It', danger: true }
  };
  var cfg = labels[status] || { title: 'Set status to ' + status + '?', text: '', icon: 'question', confirm: 'Confirm' };
  
  OmniConfirm(cfg).then((result) => {
    if (!result.isConfirmed) return;
    fetch('/admin/orders/' + orderId + '/status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: status })
    }).then(res => res.json()).then(data => {
      if (data.success) {
        OmniToast('Status updated to ' + status, 'success');
        setTimeout(() => htmx.ajax('GET', location.href, {target:'#admin-content'}), 1000);
      } else {
        OmniToast(data.error || 'Error updating status', 'error');
      }
    });
  });
}
function savePayRef(orderId) {
  var ref = document.getElementById('payref_' + orderId).value.trim();
  fetch('/admin/orders/' + orderId + '/payment-reference', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ payment_reference: ref })
  }).then(() => OmniToast('Payment reference saved', 'success'));
}
function sendInvoice(orderId) {
  OmniConfirm({
    title: 'Re-send Invoice?',
    text: 'This will re-send the order invoice email to the client.',
    icon: 'question',
    confirm: 'Yes, Send It'
  }).then((result) => {
    if (!result.isConfirmed) return;
    fetch('/admin/orders/' + orderId + '/send-invoice', { method: 'POST' })
      .then(() => OmniToast('Invoice email sent', 'success'));
  });
}
function verifyPayment(orderId, status, clientRef) {
  var titles = {
    'verified': { title: 'Verify Payment?', text: 'This will mark the payment as verified.', icon: 'success', confirm: 'Yes, Verify' },
    'rejected': { title: 'Reject Payment?', text: 'This will mark the payment as rejected.', icon: 'warning', confirm: 'Yes, Reject', danger: true }
  };
  var cfg = titles[status] || { title: 'Update verification?', text: '', icon: 'question', confirm: 'Confirm' };

  OmniConfirm(cfg).then((result) => {
    if (!result.isConfirmed) return;
    var body = { status: status };
    if (clientRef !== undefined && clientRef !== '') body.client_payment_reference = clientRef;
    fetch('/admin/orders/' + orderId + '/verify-payment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    }).then(res => res.json()).then(data => {
      if (data.success) {
        OmniToast('Payment ' + status, 'success');
        setTimeout(() => htmx.ajax('GET', location.href, {target:'#admin-content'}), 1000);
      } else {
        OmniToast(data.error || 'Error updating verification', 'error');
      }
    });
  });
}
</script>
