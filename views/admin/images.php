<?php $active_page = 'images'; ?>

<div class="container">
  <?php include __DIR__ . '/_header.php'; ?>
  <p class="header-desc">Upload a photo for each product. Images are shown to exhibitors on the catalog page.</p>

  <?php if (isset($success)): ?>
    <div class="alert alert-success">✓ <?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>
  <?php if (isset($error)): ?>
    <div class="alert alert-error">⚠ <?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div class="upload-card">
    <h2>📥 Upload a Product Image</h2>
    <p class="subtext">
      Select a product from the list, then choose an image file from your computer (JPG, PNG, or WebP).
      Large photos are compressed automatically before upload. All images are saved as optimized WebP.
    </p>

    <form method="POST" action="/admin/images" enctype="multipart/form-data" data-compress-images hx-encoding="multipart/form-data" hx-target="#admin-content">
      <div class="form-grid">
        <div class="form-group">
          <label>Product</label>
          <select name="product_id" id="product_id" required onchange="AdminImages.updateColors()">
            <option value="">— Select a product —</option>
            <?php foreach ($products as $p): ?>
            <option value="<?php echo htmlspecialchars($p['id']); ?>" data-colors='<?php echo json_encode($p['colors'] ?? []); ?>'>
              <?php echo htmlspecialchars($p['code']); ?> — <?php echo htmlspecialchars($p['name']); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Colour (optional)</label>
          <select name="color_id" id="color_id">
            <option value="default">Default (all colours)</option>
          </select>
          <p style="font-size:10px;color:#aaa;margin-top:5px">Choose a colour for a colour-specific photo</p>
        </div>

        <div class="form-group">
          <label>Image File</label>
          <div class="file-picker" onclick="document.getElementById('file-input').click()">
            <input type="file" name="image" id="file-input" accept="image/jpeg,image/png,image/webp" required onchange="AdminImages.updateFileName(this)">
            <span id="file-label">📁 Choose image file</span>
          </div>
          <p id="file-name" style="font-size:11px;color:#888;margin-top:5px">No file chosen</p>
        </div>
      </div>
      
      <button type="submit" class="btn-upload">📤 Upload Image</button>
    </form>
  </div>

  <div class="filter-bar">
    <div class="search-wrap">
      <input type="search" id="p-search" placeholder="Search by product code or name..." autocomplete="off" spellcheck="false">
    </div>
    
    <select id="p-cat">
      <option value="">All Categories</option>
      <?php foreach ($categories as $cat): ?>
      <option value="<?php echo htmlspecialchars($cat['id']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
      <?php endforeach; ?>
    </select>

    <div class="filter-btns">
      <button type="button" class="fbtn active" data-status="all">All</button>
      <button type="button" class="fbtn" data-status="missing">Missing images</button>
      <button type="button" class="fbtn" data-status="uploaded">Has image</button>
    </div>
  </div>

  <div style="font-size:13px;color:#888;margin-bottom:15px" id="showing-count">
    Loading products…
  </div>

  <div class="grid admin-images-grid" id="product-grid" data-lazy-images="1" data-placeholder-image="<?php echo htmlspecialchars($placeholder_image ?? '/static/images/omnispace-logo.jpg'); ?>"></div>
</div>

<textarea hidden readonly id="admin-images-products" aria-hidden="true" class="admin-images-json"><?php echo htmlspecialchars($products_json, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
<textarea hidden readonly id="admin-images-map" aria-hidden="true" class="admin-images-json"><?php echo htmlspecialchars($images_json, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
<textarea hidden readonly id="admin-images-versions" aria-hidden="true" class="admin-images-json"><?php echo htmlspecialchars($image_versions_json ?? '{}', ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
