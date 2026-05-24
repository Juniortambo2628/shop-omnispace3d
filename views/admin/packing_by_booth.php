<?php global $CONFIG; ?>
<style>
    .packing-header{display:none}
    .booth-card{background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:25px;margin-bottom:40px;page-break-inside:avoid;border:1px solid #eee}
    .booth-header{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #0A9696;padding-bottom:15px;margin-bottom:20px}
    .booth-num{font-size:32px;font-weight:800;color:#0A9696;line-height:1}
    .booth-num small{display:block;font-size:11px;color:#888;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px}
    .booth-client{text-align:right}
    .booth-client h3{font-size:18px;margin-bottom:4px;color:#1a1a1a}
    .booth-client p{font-size:12px;color:#666;margin:0}
    
    .company-branding{display:flex;align-items:center;gap:20px;margin-bottom:30px;padding-bottom:20px;border-bottom:1px solid #eee}
    .logo-img{height:50px}
    .company-info{font-size:11px;color:#666;line-height:1.4}
    
    @media print{
        .topbar, .nav, .no-print{display:none !important}
        .container{max-width:100%;margin:0;padding:0}
        .booth-card{box-shadow:none;margin-bottom:60px}
        .packing-header{display:block}
    }
</style>

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
            <td style="text-align:center;font-weight:800;font-size:18px;color:#0A9696"><?php echo (int)$it['quantity']; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      
      <?php if (!empty($o['special_instructions'])): ?>
      <div style="margin-top:20px;padding:15px;background:#fefce8;border:1px solid #fef08a;border-radius:6px;font-size:12px">
        <strong>Special Instructions:</strong><br>
        <?php echo nl2br(htmlspecialchars($o['special_instructions'])); ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
