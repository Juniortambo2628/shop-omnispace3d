<?php $active_page = 'products'; ?>
<style>
/* ── PRODUCTS SPECIFIC STYLES ── */
.badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600}
.badge-db{background:#dbeafe;color:#1d4ed8}
.badge-catalog{background:#f3f4f6;color:#6b7280}
.colors-list{display:flex;gap:4px;flex-wrap:wrap}
.color-pill{background:#e0f7f7;color:#065f46;font-size:11px;padding:2px 7px;border-radius:10px}
.actions{display:flex;gap:6px;align-items:center}
</style>

<div class="container">
  <?php include __DIR__ . '/_header.php'; ?>

  <?php include __DIR__ . '/_flash.php'; ?>

  <?php include __DIR__ . '/_filters.php'; ?>

  <div class="table-wrap">
  <table id="prodTable">
    <thead>
      <tr>
        <th>Code</th>
        <th>Name</th>
        <th>Category</th>
        <th>Colours / Variants</th>
        <th>Price</th>
        <th>Source</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $prod): ?>
      <?php $is_db = in_array(($prod['id'] ?? ''), $db_prod_ids); ?>
      <tr data-cat="<?php echo htmlspecialchars($prod['category_id'] ?? ''); ?>" data-source="<?php echo ($is_db ? 'db' : 'catalog'); ?>">
        <td><span class="prod-code"><?php echo htmlspecialchars($prod['code'] ?? ''); ?></span></td>
        <td class="prod-name-cell"><?php echo htmlspecialchars($prod['name'] ?? ''); ?></td>
        <td class="prod-category-cell">
          <?php echo htmlspecialchars(\App\Support\AdminProductList::categoryNameForProduct($prod, $categories)); ?>
        </td>
        <td>
          <div class="colors-list">
            <?php foreach (($prod['colors'] ?? []) as $c): ?>
            <span class="color-pill"><?php echo htmlspecialchars($c['name'] ?? ''); ?></span>
            <?php endforeach; ?>
          </div>
        </td>
        <td>
          <?php if (!empty($prod['is_poa'])): ?>
          <span style="color:#d97706;font-weight:600">POA</span>
          <?php else: ?>
          <?php echo htmlspecialchars($prod['price_display'] ?? ''); ?>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($is_db): ?>
          <span class="badge badge-db">Admin-added</span>
          <?php else: ?>
          <span class="badge badge-catalog">Catalogue</span>
          <?php endif; ?>
        </td>
        <td>
          <div class="actions">
          <a href="/admin/products/<?php echo htmlspecialchars($prod['id'] ?? ''); ?>/edit" class="btn btn-outline btn-sm">✏️ Edit</a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>

  <?php include __DIR__ . '/_pagination.php'; ?>
</div>
