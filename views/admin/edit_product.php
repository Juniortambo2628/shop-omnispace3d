<?php $active_page = 'products'; ?>
<style>
    .color-row{display:flex;gap:8px;align-items:center;margin-bottom:8px}
    .color-row input{flex:1}
    .color-row--extra{display:none}
    .colors-expanded .color-row--extra{display:flex}
    .color-preview{width:50px;height:50px;border:1px solid #ddd;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;background:#fff;overflow:hidden;flex-shrink:0}
    .color-preview img{width:100%;height:100%;object-fit:contain;display:block}
    .remove-color{background:none;border:none;color:var(--color-error);cursor:pointer;font-size:18px;line-height:1;padding:0 4px}
    #add-color-btn,#toggle-colors-btn{background:var(--brand-teal-light);color:var(--brand-teal);border:1.5px dashed var(--brand-teal);border-radius:7px;padding:7px 14px;font-size:12px;font-weight:600;font-family:inherit;cursor:pointer;margin-top:4px}
    #toggle-colors-btn{border-style:solid;margin-left:8px}
    .poa-toggle{display:flex;align-items:center;gap:8px;margin-bottom:16px}
    .stock-card{margin-top:8px;padding:20px;border:1px solid #eef0f2;border-radius:var(--radius-lg);background:#f8fafc}
    .stock-card h3{font-size:14px;font-weight:700;color:var(--brand-teal);margin-bottom:14px;display:flex;align-items:center;gap:8px}
    .stock-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;align-items:end}
    .stock-stat{background:#fff;border:1px solid #eef0f2;border-radius:var(--radius-md);padding:14px}
    .stock-stat__label{font-size:11px;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px}
    .stock-stat__value{font-size:22px;font-weight:700;color:var(--brand-teal)}
.stock-bar-wrap{width:100%;height:10px;background:#eee;border-radius:var(--radius-sm);overflow:hidden;margin-top:8px}
.stock-bar{height:100%;border-radius:var(--radius-sm)}
.stock-bar--ok{background:var(--brand-teal)}
.stock-bar--warn{background:var(--color-warning)}
.stock-bar--crit{background:var(--color-error)}
    .main-image-preview{width:80px;height:80px;border:1px solid #ddd;border-radius:var(--radius-sm);background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
    .main-image-preview img{width:100%;height:100%;object-fit:contain}
    .main-image-preview--empty{border-style:dashed;background:#f9f9f9;color:#bbb;font-size:11px;text-align:center;padding:6px}
    .upload-hint{background:var(--brand-teal-light);border:1px solid #b8e4e3;border-radius:var(--radius-md);padding:10px 14px;font-size:12px;color:var(--brand-teal);margin-bottom:16px;line-height:1.5}
    @media(max-width:700px){.stock-grid{grid-template-columns:1fr}}
</style>

<div class="container">
  <?php include __DIR__ . '/_header.php'; ?>
  <?php include __DIR__ . '/_admin_hero.php'; ?>

  <?php include __DIR__ . '/_flash.php'; ?>

  <?php if ($error): ?>
  <div class="alert alert-error">⚠ <?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="/admin/products/<?php echo htmlspecialchars($prod['id'] ?? ''); ?>/edit?event=<?php echo htmlspecialchars($event_slug); ?>" enctype="multipart/form-data" data-compress-images id="editProductForm">

  <div class="form-card">
    <?php if (!$is_super_admin): ?>
    <div class="locked-notice">
      🔒 As a Product Editor you can update colours/variants, description and dimensions.
      Pricing and product codes can only be changed by a Super Admin.
    </div>
    <?php endif; ?>

      <h3>📝 Product Details</h3>
      <p class="section-desc">Core catalogue information for this product.</p>

      <div class="form-row">
        <div class="form-group">
          <label>Product Code <?php if (!$is_super_admin) echo '🔒'; ?></label>
          <input type="text" name="code" value="<?php echo htmlspecialchars($prod['code'] ?? ''); ?>"
                 <?php if (!$is_super_admin) echo 'disabled'; ?> style="text-transform:uppercase">
        </div>
        <div class="form-group">
          <label>Category <?php if (!$is_super_admin) echo '🔒'; ?></label>
          <select name="category_id" <?php if (!$is_super_admin) echo 'disabled'; ?>>
            <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php if ($cat['id'] == ($prod['category_id'] ?? '')) echo 'selected'; ?>>
              <?php echo htmlspecialchars($cat['icon']); ?> <?php echo htmlspecialchars($cat['name']); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Product Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($prod['name'] ?? ''); ?>" required>
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea name="description"><?php echo htmlspecialchars($prod['description'] ?? ''); ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Dimensions</label>
          <input type="text" name="dimensions" value="<?php echo htmlspecialchars($prod['dimensions'] ?? ''); ?>" placeholder="e.g. L80xW75xH90cm">
        </div>
        <div class="form-group">
          <label>Unit</label>
          <select name="unit">
            <?php foreach (['per item','per day','per event','per sqm','per hour','per person'] as $u): ?>
            <option value="<?php echo $u; ?>" <?php if (($prod['unit'] ?? 'per item') == $u) echo 'selected'; ?>><?php echo $u; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
  </div>

  <div class="form-card">
      <h3>🖼 Product Images</h3>
      <p class="section-desc">Thumbnails load optimized WebP previews. Large uploads are compressed in your browser before saving.</p>
      <div class="upload-hint">Photos over ~80 KB are auto-compressed before upload. All images are saved as optimized WebP on the server.</div>

      <div class="form-group">
        <label>Product Main Image</label>
        <div style="display:flex;gap:16px;align-items:flex-start">
          <div class="main-image-preview" id="mainImagePreview">
            <img src="<?php echo htmlspecialchars($image_urls['default'] ?? $placeholder_image); ?>"
                 alt=""
                 loading="lazy"
                 decoding="async">
          </div>
          <div style="flex:1">
            <input type="file" name="image_main" accept="image/jpeg,image/png,image/gif,image/webp" data-image-preview="#mainImagePreview">
            <div class="hint">Upload a new main image. Recommended: square JPG or PNG.</div>
          </div>
        </div>
      </div>

      <?php
        $colors = $prod['colors'] ?? [];
        if (empty($colors)) {
            $colors = [['id' => '', 'name' => '']];
        }
        $colorCount = count($colors);
        $visibleColors = 4;
      ?>
      <div class="form-group">
        <label>Colour / Variant Options (<?php echo $colorCount; ?>)</label>
        <div id="colors-container" class="<?php echo $colorCount > $visibleColors ? '' : 'colors-expanded'; ?>">
          <?php foreach ($colors as $index => $c):
            $c_id = $c['id'] ?? '';
            $previewUrl = $image_urls[$c_id] ?? $placeholder_image;
          ?>
          <div class="color-row<?php echo $index >= $visibleColors ? ' color-row--extra' : ''; ?>">
            <div class="color-preview" id="colorPreview_<?php echo htmlspecialchars($c_id !== '' ? $c_id : 'new_' . $index); ?>">
              <img src="<?php echo htmlspecialchars($previewUrl); ?>"
                   alt=""
                   loading="lazy"
                   decoding="async">
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;flex:1;">
              <input type="text" name="color_name[]" value="<?php echo htmlspecialchars($c['name'] ?? ''); ?>" placeholder="Colour name">
              <input type="hidden" name="color_id[]" value="<?php echo htmlspecialchars($c_id); ?>">
              <input type="file"
                     name="<?php echo $c_id !== '' ? 'image_' . htmlspecialchars($c_id) : 'image_new[]'; ?>"
                     accept="image/*"
                     style="font-size:11px;padding:4px;width:100%;"
                     data-image-preview="#colorPreview_<?php echo htmlspecialchars($c_id !== '' ? $c_id : 'new_' . $index); ?>">
            </div>
            <button type="button" class="remove-color" onclick="removeColor(this)" style="align-self:flex-start;margin-top:10px;">×</button>
          </div>
          <?php endforeach; ?>
        </div>
        <?php if ($colorCount > $visibleColors): ?>
        <button type="button" id="toggle-colors-btn" onclick="toggleColorRows(this)">
          Show all <?php echo $colorCount; ?> variants
        </button>
        <?php endif; ?>
        <button type="button" id="add-color-btn" onclick="addColor()">+ Add colour / variant</button>
        <div class="hint">Add, remove or rename colour options. Existing orders won't be affected.</div>
      </div>
  </div>

  <div class="form-card">
      <?php
        $stock = $stock_summary ?? ['stock_limit' => null, 'total_ordered' => 0, 'pct' => null];
        $stockPct = $stock['pct'];
        $stockBarClass = 'stock-bar--ok';
        if ($stockPct !== null && $stockPct >= 100) {
            $stockBarClass = 'stock-bar--crit';
        } elseif ($stockPct !== null && $stockPct >= 80) {
            $stockBarClass = 'stock-bar--warn';
        }
      ?>
      <div class="stock-card" style="margin-top:0;padding:0;border:none;background:transparent">
        <h3>📦 Stock Levels</h3>
        <p class="hint" style="margin-bottom:16px">
          Ordered quantities are counted from all non-cancelled orders for this event.
          Leave the limit blank for unlimited stock.
        </p>
        <div class="stock-grid">
          <div class="stock-stat">
            <div class="stock-stat__label">Category</div>
            <div style="font-size:14px;font-weight:600;color:#333"><?php echo htmlspecialchars($category_name ?? ($prod['category_id'] ?? '')); ?></div>
          </div>
          <div class="stock-stat">
            <div class="stock-stat__label">Ordered (<?php echo htmlspecialchars($event_slug); ?>)</div>
            <div class="stock-stat__value"><?php echo (int) ($stock['total_ordered'] ?? 0); ?></div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label>Stock Limit</label>
            <input type="number" name="stock_limit" class="limit-input" style="width:100%;max-width:160px"
                   value="<?php echo $stock['stock_limit'] !== null ? (int) $stock['stock_limit'] : ''; ?>"
                   placeholder="∞" min="0" max="9999">
            <div class="hint">Maximum units available for this product</div>
          </div>
        </div>
        <?php if ($stockPct !== null): ?>
        <div style="margin-top:16px">
          <div style="display:flex;justify-content:space-between;font-size:12px;color:#666;margin-bottom:6px">
            <span>Utilisation</span>
            <strong><?php echo (int) $stockPct; ?>%</strong>
          </div>
          <div class="stock-bar-wrap">
            <div class="stock-bar <?php echo $stockBarClass; ?>" style="width:<?php echo min((int) $stockPct, 100); ?>%"></div>
          </div>
        </div>
        <?php endif; ?>
        <div style="margin-top:14px">
          <a href="/admin/stock?event=<?php echo htmlspecialchars($event_slug); ?>" class="btn btn-outline btn-sm">View all stock levels</a>
        </div>
      </div>

      <?php if ($is_super_admin): ?>
      <div class="poa-toggle" style="margin-top:24px;padding-top:20px;border-top:1px solid #f0f0f0">
        <input type="checkbox" id="is_poa" name="is_poa" value="1"
               <?php if (!empty($prod['is_poa'])) echo 'checked'; ?> onchange="togglePrice(this)">
        <label for="is_poa" style="text-transform:none;letter-spacing:0;font-size:13px;color:#333;margin:0">Price on Application (POA)</label>
      </div>

      <div id="price-section" class="form-row" <?php if (!empty($prod['is_poa'])) echo 'style="display:none"'; ?>>
        <div class="form-group">
          <label>Price (numeric)</label>
          <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($prod['price'] ?? 0); ?>" min="0" step="0.01">
        </div>
        <div class="form-group">
          <label>Currency</label>
          <select name="currency">
            <option value="KES" <?php if (strpos($prod['price_display'] ?? '', 'KES') !== false || strpos($prod['price_display'] ?? '', '$') === false) echo 'selected'; ?>>KES</option>
            <option value="USD" <?php if (strpos($prod['price_display'] ?? '', '$') !== false) echo 'selected'; ?>>USD ($)</option>
          </select>
        </div>
      </div>
      <?php endif; ?>

      <div class="footer-actions">
        <button type="submit" class="btn btn-primary">✓ Save Changes</button>
        <a href="/admin/products?event=<?php echo htmlspecialchars($event_slug); ?>" class="btn btn-outline">Cancel</a>
      </div>
  </div>
  </form>

  <?php if (! empty($can_delete_product)): ?>
  <div class="form-card form-card--danger">
    <h3>🗑 Delete Product</h3>
    <p class="section-desc">Permanently remove this product from the catalogue. This cannot be undone.</p>
    <form method="POST" action="/admin/products/<?php echo htmlspecialchars($prod['id'] ?? ''); ?>/delete"
          onsubmit="event.preventDefault(); Swal.fire({
              title: 'Delete Product?',
              text: 'Are you sure you want to delete <?php echo addslashes(htmlspecialchars($prod['name'] ?? '')); ?>? This cannot be undone.',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#dc2626',
              confirmButtonText: 'Yes, delete it!'
          }).then((result) => { if (result.isConfirmed) this.submit(); })">
      <button type="submit" class="btn btn-danger">Delete Product</button>
    </form>
  </div>
  <?php endif; ?>
</div>

<script src="/static/js/product-colors.js"></script>
<script>
var colorRowIndex = <?php echo (int) $colorCount; ?>;
var productPlaceholderImage = <?php echo json_encode($placeholder_image ?? '/static/images/omnispace-logo.jpg'); ?>;

function addColor() {
  colorRowIndex++;
  var previewId = 'colorPreview_new_' + colorRowIndex;
  var div = document.createElement('div');
  div.className = 'color-row';
  div.innerHTML = '<div class="color-preview" id="' + previewId + '"><img src="' + productPlaceholderImage + '" alt="" loading="lazy" decoding="async"></div>'
                + '<div style="display:flex;flex-direction:column;gap:4px;flex:1;"><input type="text" name="color_name[]" placeholder="e.g. White"><input type="file" name="image_new[]" accept="image/*" style="font-size:11px;padding:4px;width:100%;" data-image-preview="#' + previewId + '"></div>'
                + '<button type="button" class="remove-color" onclick="removeColor(this)" style="align-self:flex-start;margin-top:10px;">×</button>';
  document.getElementById('colors-container').appendChild(div);
  if (window.OmniImageUpload && window.OmniImageUpload.bindPreviewInputs) {
    window.OmniImageUpload.bindPreviewInputs(div);
  }
  var toggleBtn = document.getElementById('toggle-colors-btn');
  if (toggleBtn) {
    document.getElementById('colors-container').classList.add('colors-expanded');
    toggleBtn.style.display = 'none';
  }
}
function toggleColorRows(btn) {
  document.getElementById('colors-container').classList.add('colors-expanded');
  btn.style.display = 'none';
}
</script>
