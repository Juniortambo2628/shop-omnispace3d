<?php global $CONFIG; ?>
<link rel="stylesheet" href="/static/css/packing.css">

<div class="container" data-packing-storage-key="packing-stand-<?php echo htmlspecialchars($event_slug ?? 'default'); ?>">
  <div class="no-print">
    <?php include __DIR__ . '/_header.php'; ?>
  </div>

  <?php if (empty($booths)): ?>
  <div class="booth-card">No orders found for this event.</div>
  <?php else: ?>
    <?php foreach ($booths as $b): $o = $b['order']; $items = $b['items']; ?>
    <div class="booth-card">
      <!-- Branding Section -->
      <div class="company-branding">
        <img src="/static/images/omnispace-invoice-logo.jpg" class="logo-img" onerror="this.style.display='none'">
        <div class="company-info">
          <strong><?php echo htmlspecialchars($CONFIG['company_name'] ?? 'OmniSpace 3D Events Ltd'); ?></strong><br>
          <?php echo nl2br(htmlspecialchars($CONFIG['company_address'] ?? '')); ?><br>
          <?php echo htmlspecialchars($CONFIG['company_email'] ?? ''); ?> | <?php echo htmlspecialchars($CONFIG['company_phone'] ?? ''); ?>
        </div>
      </div>

      <div class="booth-header">
        <div class="booth-num">
          <small>Stand Number</small>
          <?php echo htmlspecialchars($o['booth_number'] ?? '—'); ?>
        </div>
        <div class="booth-client">
          <h3><?php echo htmlspecialchars($o['company_name']); ?></h3>
          <p><strong>Order #<?php echo htmlspecialchars($o['id']); ?></strong> · <?php echo htmlspecialchars($o['contact_name']); ?></p>
          <p><?php echo htmlspecialchars($o['email']); ?> · <?php echo htmlspecialchars($o['phone']); ?></p>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th style="width:40px;text-align:center">Check</th>
            <th style="width:120px">Code</th>
            <th>Product Description</th>
            <th>Color / Variant</th>
            <th style="text-align:center;width:60px">Qty</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $idx => $it): ?>
          <?php $checkKey = 'stand-' . ($o['id'] ?? 0) . '-' . $idx; ?>
          <tr>
            <td style="text-align:center">
              <label class="pack-check" title="Mark as packed">
                <input type="checkbox" class="pack-check__input" data-check-key="<?php echo htmlspecialchars($checkKey); ?>">
                <span class="pack-check__box" aria-hidden="true"></span>
              </label>
            </td>
            <td><span class="prod-code"><?php echo htmlspecialchars($it['product_code']); ?></span></td>
            <td class="prod-name-cell"><?php echo htmlspecialchars($it['product_name']); ?></td>
            <td><?php echo htmlspecialchars($it['color_name'] ?? '—'); ?></td>
            <td style="text-align:center;font-weight:800;font-size:18px;color:var(--brand-teal)"><?php echo (int)$it['quantity']; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      
      <?php if (!empty($o['special_instructions'])): ?>
      <div style="margin-top:20px;padding:15px;background:#fefce8;border:1px solid #fef08a;border-radius:var(--radius-sm);font-size:12px">
        <strong>Special Instructions:</strong><br>
        <?php echo nl2br(htmlspecialchars($o['special_instructions'])); ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
