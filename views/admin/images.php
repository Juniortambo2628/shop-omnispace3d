<?php $active_page = 'images'; ?>
<style>
/* ── IMAGES SPECIFIC STYLES ── */
.header-row{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:12px}
.header-row h1{font-size:22px;font-weight:700;color:#1a1a1a;display:flex;align-items:center;gap:10px}
.header-desc{font-size:13px;color:#888;margin-bottom:24px}
.stats-badge{font-size:18px;font-weight:700;color:#0A9696}
.stats-badge span{color:#ccc;font-weight:400;font-size:14px}
.upload-card{background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:24px;margin-bottom:32px;border:1px solid #eef0f2}
.upload-card h2{font-size:16px;font-weight:700;margin-bottom:8px;display:flex;align-items:center;gap:10px}
.upload-card .subtext{font-size:12px;color:#888;line-height:1.6;margin-bottom:24px;max-width:900px}
.form-grid{display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:20px;align-items:start}
.form-group label{display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:6px}
.form-group select,.form-group input{width:100%}
.form-group select:focus{outline:none;border-color:#0A9696}
.file-picker{border:1px dashed #0A9696;border-radius:8px;padding:12px;display:flex;align-items:center;justify-content:center;gap:8px;background:#f0fdfd;cursor:pointer;position:relative}
.file-picker input[type=file]{position:absolute;width:100%;height:100%;opacity:0;cursor:pointer}
.file-picker span{font-size:12px;font-weight:600;color:#0A9696}
.btn-upload{background:#0A9696;color:#fff;border:none;padding:12px 24px;border-radius:8px;font-weight:700;cursor:pointer;font-size:13px;display:flex;align-items:center;gap:8px;margin-top:16px}
.btn-upload:hover{background:#088080}
.filter-bar{display:flex;gap:10px;margin-bottom:20px;align-items:center}
.search-wrap{position:relative;flex:1}
.search-wrap input{width:100%}
.search-wrap::before{content:'🔍';position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:14px;opacity:.5}
.filter-bar select{min-width:0}
.filter-btns{display:flex;gap:6px}
.fbtn{padding:9px 16px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid #ddd;background:#fff;color:#555;transition:background .18s ease,border-color .18s ease,color .18s ease}
.fbtn.active{background:#0A9696;color:#fff;border-color:#0A9696}
.grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(240px, 1fr));gap:20px}
.img-card{background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.06);overflow:hidden;border:1px solid #eef0f2;display:flex;flex-direction:column;transform:scale(1);transform-origin:center center;transition:box-shadow .32s ease,transform .32s ease}
.img-card:hover{box-shadow:0 3px 14px rgba(10,150,150,.1);transform:scale(1.012)}
.img-wrap{height:180px;background:#f8fafc;position:relative;display:flex;align-items:center;justify-content:center;padding:10px}
.img-wrap img{max-width:100%;max-height:100%;object-fit:contain}
.badge-uploaded{position:absolute;top:10px;right:10px;background:#0A9696;color:#fff;font-size:10px;font-weight:700;padding:4px 10px;border-radius:12px;display:flex;align-items:center;gap:4px}
.img-body{padding:14px;flex:1}
.p-code{font-family:monospace;font-size:11px;font-weight:700;color:#0A9696;margin-bottom:4px}
.p-name{font-size:13px;font-weight:600;color:#333;line-height:1.4;margin-bottom:12px;min-height:36px}
.p-meta{font-size:11px;color:#888;display:flex;justify-content:space-between;align-items:center}
.thumb.active{border-color:#0A9696 !important;background:#D6F0EF !important;color:#0A9696 !important}
</style>

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
