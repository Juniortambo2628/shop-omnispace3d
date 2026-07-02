
<div class="container" x-data="{ saved: <?php echo !empty($saved) ? 'true' : 'false'; ?> }">
    <?php include __DIR__ . '/_header.php'; ?>

    <?php if (!empty($saved)): ?>
    <div class="alert alert-success" x-show="saved" x-transition>✓ Stock limits saved successfully.</div>
    <?php endif; ?>

    <div class="info-box">
        <strong>How stock limits work:</strong>
        Set a maximum quantity available per product. When orders approach or reach the limit
        you'll see a warning — orange at 80%+, red at 100%+. Leave blank for unlimited.
        Quantities shown are from all non-cancelled orders for this event.
        Use the filters and pagination to browse products; saving updates limits on the current page only.
    </div>

    <?php include __DIR__ . '/_filters.php'; ?>

    <div class="table-wrap">
        <form id="stockForm" action="/admin/stock" method="POST"
              onsubmit="event.preventDefault(); Swal.fire({
                  title: 'Save Stock Limits?',
                  text: 'This will update stock limits for products on this page.',
                  icon: 'question',
                  showCancelButton: true,
                  confirmButtonColor: '#0A9696',
                  confirmButtonText: 'Yes, Save'
              }).then((r) => { if (r.isConfirmed) this.submit(); })">
            <table id="stockTable">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Ordered</th>
                        <th>Stock Limit</th>
                        <th>Utilisation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stock_map = [];
                    foreach ($stock_data as $s) {
                        if (!empty($s['product_code'])) {
                            $stock_map[$s['product_code']] = $s;
                        }
                    }
                    ?>

                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:40px 20px;color:#888;">No products match your filters.</td>
                    </tr>
                    <?php endif; ?>

                    <?php foreach ($products as $prod): ?>
                    <?php
                    $s = $stock_map[$prod['code']] ?? [];
                    $ordered = $s['total_ordered'] ?? 0;
                    $limit = $s['stock_limit'] ?? null;
                    $pct = $s['pct'] ?? null;
                    ?>
                    <tr>
                        <td><span class="prod-code"><?php echo htmlspecialchars($prod['code']); ?></span></td>
                        <td class="prod-name-cell"><?php echo htmlspecialchars($prod['name']); ?></td>
                        <td class="prod-category-cell"><?php echo htmlspecialchars(\App\Support\AdminProductList::categoryNameForProduct($prod, $categories)); ?></td>
                        <td><span class="ordered-num"><?php echo (int)$ordered; ?></span>
                            <?php if ($ordered > 0): ?>
                            <span style="font-size:11px;color:#888"> unit(s)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="number" class="limit-input"
                                   name="limit_<?php echo htmlspecialchars($prod['code']); ?>"
                                   value="<?php echo ($limit !== null ? (int)$limit : ''); ?>"
                                   placeholder="∞" min="0" max="9999">
                        </td>
                        <td>
                            <?php if ($pct !== null): ?>
                                <div class="bar-wrap">
                                    <div class="bar <?php if ($pct >= 100) echo 'bar-crit'; elseif ($pct >= 80) echo 'bar-warn'; else echo 'bar-ok'; ?>"
                                         style="width:<?php echo min($pct, 100); ?>%"></div>
                                </div>
                                <span class="pct <?php if ($pct >= 100) echo 'bar-crit'; elseif ($pct >= 80) echo 'bar-warn'; else echo 'bar-ok'; ?>"><?php echo $pct; ?>%</span>
                            <?php else: ?>
                                <span class="no-limit">— no limit set</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    </div>

    <?php include __DIR__ . '/_pagination.php'; ?>

    <div style="text-align:right;margin-top:10px;margin-bottom:30px">
        <button class="btn btn-primary" onclick="document.getElementById('stockForm').requestSubmit()">
            💾 Save Page Limits
        </button>
    </div>
</div>
