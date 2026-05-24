<?php global $CONFIG; ?>
<style>
    .packing-page{background:#fff;padding:40px;min-height:297mm}
    .packing-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:40px;border-bottom:2px solid #0A9696;padding-bottom:20px}
    .logo-img{height:50px}
    .company-info{text-align:right;font-size:11px;color:#666;line-height:1.4}
    
    .cat-group{margin-bottom:50px;page-break-inside:avoid}
    .cat-title{font-size:18px;font-weight:800;color:#0A9696;text-transform:uppercase;letter-spacing:1px;margin-bottom:15px;display:flex;align-items:center;gap:10px}
    .cat-title::after{content:'';flex:1;height:2px;background:#D6F0EF}
    
    @media print{
        .topbar, .nav, .no-print{display:none !important}
        .container{max-width:100%;margin:0;padding:0}
        .packing-page{padding:0}
    }
</style>

<div class="container">
  <div class="no-print">
    <?php include __DIR__ . '/_header.php'; ?>
  </div>

  <div class="packing-page" data-packing-storage-key="packing-category-<?php echo htmlspecialchars($event_slug ?? 'default'); ?>">
    <div class="packing-header">
      <img src="/static/images/omnispace-invoice-logo.jpg" class="logo-img" onerror="this.style.display='none'">
      <div class="company-info">
        <h2 style="margin:0;font-size:16px;color:#333"><?php echo htmlspecialchars($CONFIG['company_name'] ?? 'OmniSpace 3D Events Ltd'); ?></h2>
        <?php echo htmlspecialchars($CONFIG['company_email'] ?? ''); ?> | <?php echo htmlspecialchars($CONFIG['company_phone'] ?? ''); ?><br>
        <strong>PACKING LIST - <?php echo date('d M Y'); ?></strong>
      </div>
    </div>

    <?php if (empty($items_by_cat)): ?>
    <div style="padding:40px;text-align:center;color:#666">No items found for this event.</div>
    <?php else: ?>
      <?php foreach ($items_by_cat as $cat => $items): ?>
      <div class="cat-group">
        <div class="cat-title"><?php echo htmlspecialchars($cat); ?></div>
        <table>
          <thead>
            <tr>
              <th style="width:30px;text-align:center">Ok</th>
              <th style="width:100px">Booth</th>
              <th>Company</th>
              <th>Product Details</th>
              <th style="text-align:center;width:60px">Qty</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $idx => $it): ?>
            <?php
              $checkKey = 'cat-' . ($it['order_id'] ?? 0) . '-' . $idx;
            ?>
            <tr>
              <td style="text-align:center">
                <label class="pack-check" title="Mark as packed">
                  <input type="checkbox" class="pack-check__input" data-check-key="<?php echo htmlspecialchars($checkKey); ?>">
                  <span class="pack-check__box" aria-hidden="true"></span>
                </label>
              </td>
              <td style="font-weight:800;font-size:14px;color:#0A9696"><?php echo htmlspecialchars($it['booth']); ?></td>
              <td style="font-size:12px"><?php echo htmlspecialchars($it['company']); ?></td>
              <td>
                <div style="font-weight:700"><?php echo htmlspecialchars($it['product']); ?></div>
                <div style="font-size:11px;color:#888;font-family:monospace"><?php echo htmlspecialchars($it['color']); ?></div>
              </td>
              <td style="text-align:center;font-weight:800;font-size:16px"><?php echo (int)$it['qty']; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
