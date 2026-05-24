<?php
/**
 * Reusable admin entity hero (profile, product edit, order edit, etc.).
 *
 * Expected: $hero_title, optional $hero_meta, $hero_badge, $hero_image, $hero_initials, $hero_placeholder
 */
$hero_placeholder = $hero_placeholder ?? '📦';
?>
<div class="admin-hero">
  <div class="admin-hero__media<?php echo ! empty($hero_image) ? ' admin-hero__media--img skeleton' : ' admin-hero__media--empty'; ?>">
    <?php if (! empty($hero_image)): ?>
      <img src="<?php echo htmlspecialchars($hero_image); ?>"
           alt=""
           class="admin-hero__img"
           loading="eager"
           decoding="async"
           onload="this.closest('.admin-hero__media').classList.remove('skeleton')">
    <?php elseif (! empty($hero_initials)): ?>
      <span class="admin-hero__initials" aria-hidden="true"><?php echo htmlspecialchars($hero_initials); ?></span>
    <?php else: ?>
      <span class="admin-hero__placeholder" aria-hidden="true"><?php echo htmlspecialchars($hero_placeholder); ?></span>
    <?php endif; ?>
  </div>
  <div class="admin-hero__body">
    <div class="admin-hero__title"><?php echo htmlspecialchars($hero_title ?? ''); ?></div>
    <?php if (! empty($hero_meta)): ?>
      <div class="admin-hero__meta"><?php echo htmlspecialchars($hero_meta); ?></div>
    <?php endif; ?>
    <?php if (! empty($hero_badge)): ?>
      <span class="admin-hero__badge"><?php echo htmlspecialchars($hero_badge); ?></span>
    <?php endif; ?>
  </div>
</div>
