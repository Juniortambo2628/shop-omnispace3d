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
    <div style="display:flex;align-items:center;gap:16px">
        <?php if (empty($is_catalog_page)): ?>
        <a href="/<?php echo htmlspecialchars($event_slug ?? DEFAULT_EVENT); ?>" style="color:#fff;font-size:13px;opacity:0.9;text-decoration:none;white-space:nowrap;">&#8592; Catalog</a>
        <?php endif; ?>
        <div style="position:relative" class="nav-dropdown">
            <a href="/order/history" style="color:#fff;font-size:13px;text-decoration:none;opacity:0.9;cursor:pointer;">&#128203; My Orders &#9662;</a>
            <div class="nav-dropdown-menu">
                <a href="/order/history">Order History</a>
                <a href="/order/payments">Payment Status</a>
                <a href="/order/payment-reference">Submit Payment Ref</a>
            </div>
        </div>
        <?php if (!empty($header_right)): ?>
        <div><?php echo $header_right; ?></div>
        <?php endif; ?>
    </div>
</header>
<style>
.nav-dropdown{position:relative;padding-bottom:8px}
.nav-dropdown-menu{display:none;position:absolute;top:100%;right:0;background:#fff;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,0.15);min-width:180px;z-index:100;padding:6px 0;margin-top:-2px}
.nav-dropdown:hover .nav-dropdown-menu{display:block}
.nav-dropdown-menu a{display:block;padding:10px 16px;font-size:13px;color:#333;text-decoration:none;transition:background 0.15s}
.nav-dropdown-menu a:hover{background:#D6F0EF;color:#0A9696}
</style>
