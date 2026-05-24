<header class="header">
    <div style="display:flex;align-items:center;gap:16px">
        <img src="/static/images/omnispace-logo-white.png" alt="OmniSpace 3D Events">
        <?php if (!empty($header_event_logo)): ?>
        <img src="<?php echo htmlspecialchars($header_event_logo); ?>" alt="" style="height:28px;margin-left:8px;opacity:0.9;filter:brightness(0) invert(1);">
        <?php endif; ?>
    </div>
    <?php if (!empty($header_center)): ?>
    <div style="text-align:center;flex:1">
        <h1><?php echo $header_center['title'] ?? ''; ?></h1>
        <?php if (!empty($header_center['subtitle'])): ?>
        <p style="font-size:12px;opacity:0.9;margin-top:2px"><?php echo htmlspecialchars($header_center['subtitle']); ?></p>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <h1><?php echo htmlspecialchars($header_title ?? 'OmniShop'); ?></h1>
    <?php endif; ?>
    <?php if (!empty($header_right)): ?>
    <div><?php echo $header_right; ?></div>
    <?php endif; ?>
</header>
