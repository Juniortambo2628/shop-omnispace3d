<?php
/**
 * Reusable product thumbnail for admin lists.
 *
 * Expected variables:
 * - $code (string) product code
 * - $product_images (array) from ProductService::getProductImages()
 * - $thumb_size (int) optional, default 44
 */
$code = strtoupper($code ?? '');
$thumb_size = (int) ($thumb_size ?? 44);
$thumb_src = null;
$images = $product_images ?? [];
$placeholder_image = $placeholder_image ?? '/static/images/omnispace-logo.jpg';

if (! empty($images[$code])) {
    $set = $images[$code];
    $file = $set['default'] ?? reset($set);

    if ($file) {
        $stem = pathinfo($file, PATHINFO_FILENAME);
        $thumbFile = 'thumb_' . $stem . '.webp';
        $thumbPath = (defined('STATIC_PATH') ? STATIC_PATH : BASE_PATH . '/static') . '/images/products/' . $thumbFile;

        $thumb_src = is_file($thumbPath)
            ? '/static/images/products/' . $thumbFile . '?v=' . filemtime($thumbPath)
            : (is_file((defined('STATIC_PATH') ? STATIC_PATH : BASE_PATH . '/static') . '/images/products/' . $file)
                ? '/static/images/products/' . $file . '?v=' . filemtime((defined('STATIC_PATH') ? STATIC_PATH : BASE_PATH . '/static') . '/images/products/' . $file)
                : '/static/images/products/' . $file);
    }
}
?>
<div class="prod-thumb<?php echo $thumb_src ? ' prod-thumb--has-img skeleton' : ' prod-thumb--empty'; ?>"
     style="width:<?php echo $thumb_size; ?>px;height:<?php echo $thumb_size; ?>px">
    <?php if ($thumb_src): ?>
        <img src="<?php echo htmlspecialchars($thumb_src); ?>"
             alt=""
             class="prod-thumb__img"
             loading="lazy"
             decoding="async"
             onload="this.closest('.prod-thumb').classList.remove('skeleton')">
    <?php else: ?>
        <img src="<?php echo htmlspecialchars($placeholder_image); ?>"
             alt=""
             class="prod-thumb__img prod-thumb__img--placeholder"
             loading="lazy"
             decoding="async">
    <?php endif; ?>
</div>
