<?php $active_page = 'orders'; ?>
<style>
    h2{font-size:20px;font-weight:700;color:#1a1a1a;margin-bottom:6px}
    .order-id{font-family:monospace;font-size:12px;background:#f3f4f6;padding:2px 8px;border-radius:4px;color:#374151;margin-bottom:22px;display:inline-block}
    .items-table{width:100%;border-collapse:collapse;margin-top:8px}
    .items-table th,.items-table td{padding:8px 10px;font-size:12px;border-bottom:1px solid #f3f4f6;text-align:left}
    .items-table th{font-size:11px;color:#6b7280;text-transform:uppercase}
</style>

<div class="container" style="max-width:900px">
  <?php $o = $order; ?>
  <?php include __DIR__ . '/_header.php'; ?>
  <?php include __DIR__ . '/_admin_hero.php'; ?>

  <?php include __DIR__ . '/_flash.php'; ?>

  <?php if (! empty($error)): ?>
  <div class="alert alert-error">⚠ <?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div style="margin-bottom:16px">
    <span class="order-id">Internal ID: <?php echo htmlspecialchars($o['id'] ?? ''); ?></span>
    <?php if (!empty($o['custom_order_id'])): ?>
    <span class="order-id" style="margin-left:10px;background:var(--brand-teal-pale);color:var(--brand-teal)">Display ID: <?php echo htmlspecialchars($o['custom_order_id']); ?></span>
    <?php endif; ?>
  </div>

  <div class="form-card form-card--medium">
    <form method="POST" action="/admin/orders/<?php echo htmlspecialchars($o['id'] ?? ''); ?>/edit?event=<?php echo htmlspecialchars($event_slug ?? 'solarandstorage'); ?>">
      <div class="form-row">
        <div class="form-group">
          <label>Company Name</label>
          <input type="text" name="company_name" value="<?php echo htmlspecialchars($o['company_name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <label>Contact Name</label>
          <input type="text" name="contact_name" value="<?php echo htmlspecialchars($o['contact_name'] ?? ''); ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?php echo htmlspecialchars($o['email'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="phone" value="<?php echo htmlspecialchars($o['phone'] ?? ''); ?>">
        </div>
      </div>

      <div class="form-group">
        <label>Address</label>
        <textarea name="address"><?php echo htmlspecialchars($o['address'] ?? ''); ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Tax ID / VAT Number</label>
          <input type="text" name="tax_id" value="<?php echo htmlspecialchars($o['tax_id'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label>Booth Number</label>
          <input type="text" name="booth_number" value="<?php echo htmlspecialchars($o['booth_number'] ?? ''); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <?php foreach ($statuses as $status): ?>
            <option value="<?php echo htmlspecialchars($status); ?>" <?php if (($o['status'] ?? '') === $status) echo 'selected'; ?>>
              <?php echo htmlspecialchars($status); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Payment Reference</label>
          <input type="text" name="payment_reference" value="<?php echo htmlspecialchars($o['payment_reference'] ?? ''); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Payment Method</label>
          <input type="text" class="readonly-field" value="<?php echo htmlspecialchars($o['payment_method'] ?? ''); ?>" readonly>
          <div class="hint">Payment method cannot be changed after checkout.</div>
        </div>
        <div class="form-group">
          <label>Order Total (USD)</label>
          <input type="text" class="readonly-field" value="$<?php echo number_format((float) ($o['total'] ?? 0), 2); ?>" readonly>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Client Payment Reference</label>
          <input type="text" class="readonly-field" value="<?php echo htmlspecialchars($o['client_payment_reference'] ?? '— Not submitted —'); ?>" readonly>
        </div>
        <div class="form-group">
          <label>Payment Verification Status</label>
          <input type="text" class="readonly-field" value="<?php echo htmlspecialchars(ucfirst($o['payment_verification_status'] ?? 'unverified')); ?>" readonly>
        </div>
      </div>

      <div class="form-group">
        <label>Special Instructions</label>
        <textarea name="special_instructions"><?php echo htmlspecialchars($o['special_instructions'] ?? ''); ?></textarea>
      </div>

      <?php if (! empty($items)): ?>
      <div class="form-group">
        <label>Line Items (read-only)</label>
        <table class="items-table">
          <thead>
            <tr>
              <th>Code</th>
              <th>Product</th>
              <th>Colour</th>
              <th>Qty</th>
              <th>Unit</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
              <td><?php echo htmlspecialchars($item['product_code'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($item['color_name'] ?? ''); ?></td>
              <td><?php echo (int) ($item['quantity'] ?? 0); ?></td>
              <td>$<?php echo number_format((float) ($item['unit_price'] ?? 0), 2); ?></td>
              <td>$<?php echo number_format((float) ($item['total_price'] ?? 0), 2); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <div class="footer-actions">
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
        <a href="/admin/orders?event=<?php echo htmlspecialchars($event_slug ?? 'solarandstorage'); ?>" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>
