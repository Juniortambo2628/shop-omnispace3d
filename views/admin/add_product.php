<style>
    .color-row{display:flex;gap:8px;align-items:center;margin-bottom:8px}
    .color-row input{flex:1}
    .remove-color{background:none;border:none;color:var(--color-error);cursor:pointer;font-size:18px;line-height:1;padding:0 4px}
    #add-color-btn{background:var(--brand-teal-light);color:var(--brand-teal);border:1.5px dashed var(--brand-teal);border-radius:7px;padding:7px 14px;font-size:12px;font-weight:600;font-family:inherit;cursor:pointer;margin-top:4px}
    .poa-toggle{display:flex;align-items:center;gap:8px;margin-bottom:16px}
    .poa-toggle input[type=checkbox]{width:16px;height:16px;accent-color:var(--brand-teal)}
</style>

<div class="container" style="max-width:800px">
  <?php include __DIR__ . '/_header.php'; ?>

  <div class="form-card">
    <?php if ($error): ?>
    <div class="alert alert-error">⚠ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/products/add">

      <div class="form-row">
        <div class="form-group">
          <label>Product Code *</label>
          <input type="text" name="code" placeholder="e.g. SOF10" required style="text-transform:uppercase">
          <div class="hint">Short code like SOF10, CHR05, AV03 — no spaces</div>
        </div>
        <div class="form-group">
          <label>Category *</label>
          <select name="category_id" required>
            <option value="">— Select category —</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['icon']); ?> <?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Product Name *</label>
        <input type="text" name="name" placeholder="e.g. EXECUTIVE ARMCHAIR" required>
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea name="description" placeholder="Brief description of the product..."></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Dimensions</label>
          <input type="text" name="dimensions" placeholder="e.g. L80xW75xH90cm">
        </div>
        <div class="form-group">
          <label>Unit</label>
          <select name="unit">
            <option value="per item">per item</option>
            <option value="per day">per day</option>
            <option value="per event">per event</option>
            <option value="per sqm">per sqm</option>
            <option value="per hour">per hour</option>
            <option value="per person">per person</option>
          </select>
        </div>
      </div>

      <div class="poa-toggle">
        <input type="checkbox" id="is_poa" name="is_poa" value="1" onchange="togglePrice(this)">
        <label for="is_poa" style="text-transform:none;letter-spacing:0;font-size:13px;color:var(--color-text);margin:0">Price on Application (POA) — hide price from customers</label>
      </div>

      <div id="price-section" class="form-row">
        <div class="form-group">
          <label>Price (numeric)</label>
          <input type="number" name="price" id="price" placeholder="0" min="0" step="0.01">
        </div>
        <div class="form-group">
          <label>Currency</label>
          <select name="currency">
            <option value="KES">KES</option>
            <option value="USD" selected>USD ($)</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Colour / Variant Options</label>
        <div id="colors-container">
          <div class="color-row">
            <input type="text" name="color_name[]" placeholder="e.g. Black">
            <button type="button" class="remove-color" onclick="removeColor(this)">×</button>
          </div>
        </div>
        <button type="button" id="add-color-btn" onclick="addColor()">+ Add another colour / variant</button>
        <div class="hint">Add one entry per colour or variant. If no colours, leave as-is (will default to "Standard").</div>
      </div>

      <div class="footer-actions">
        <button type="submit" class="btn btn-primary">✓ Add Product</button>
        <a href="/admin/products" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script src="/static/js/product-colors.js"></script>
