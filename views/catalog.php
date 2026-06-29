<?php $page_title = htmlspecialchars($event["name"]) . ' - OmniShop'; ?>
<?php include __DIR__ . '/storefront/_head.php'; ?>
    <style>
        /* ── CATALOG-SPECIFIC STYLES ── */
        .header-left { display: flex; align-items: center; gap: 16px; }
        .header-left img { height: 38px; }
        .header-center { text-align: center; flex: 1; }
        .header-center h1 { font-size: 20px; font-weight: 700; letter-spacing: 0.3px; }
        .header-center p { font-size: 12px; opacity: 0.9; margin-top: 2px; }
        .cart-btn { position: relative; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); border-radius: 8px; padding: 8px 16px; color: #fff; cursor: pointer; font-size: 15px; display: flex; align-items: center; gap: 8px; transition: background 0.2s; font-family: inherit; }
        .cart-btn:hover { background: rgba(255,255,255,0.25); }
        .cart-badge { background: #ff5252; color: #fff; border-radius: 50%; min-width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }

        .event-hero { background: #fff; border-bottom: 3px solid #0A9696; padding: 32px 40px; display: flex; align-items: center; gap: 40px; animation: catalog-hero-in 0.55s cubic-bezier(0.22, 1, 0.36, 1) both; }
        .event-hero-logo { flex-shrink: 0; animation: catalog-hero-logo-in 0.6s cubic-bezier(0.22, 1, 0.36, 1) 0.08s both; }
        .event-hero-logo img { max-height: 140px; max-width: 320px; width: auto; object-fit: contain; }
        .event-hero-divider { width: 2px; height: 120px; background: #D6F0EF; flex-shrink: 0; }
        .event-hero-info { flex: 1; animation: catalog-hero-text-in 0.55s cubic-bezier(0.22, 1, 0.36, 1) 0.14s both; }
        .event-hero-info h2 { font-size: 22px; font-weight: 700; color: #0A9696; margin-bottom: 6px; line-height: 1.25; }
        .event-hero-info .hero-dates { font-size: 15px; font-weight: 600; color: #333; margin-bottom: 4px; }
        .event-hero-info .hero-venue { font-size: 13px; color: #6E6E6E; margin-bottom: 12px; }
        .event-hero-info .hero-tagline { display: inline-block; background: #D6F0EF; color: #0A9696; font-size: 12px; font-weight: 600; padding: 5px 14px; border-radius: 20px; }

        .info-bar { background: #D6F0EF; padding: 10px 28px; font-size: 12px; color: #555; display: flex; gap: 24px; flex-wrap: wrap; border-bottom: 1px solid #b8e0df; animation: catalog-fade-up 0.45s ease 0.2s both; }
        .info-bar span { display: flex; align-items: center; gap: 6px; }

        .deadlines { border-bottom: 2px solid #0A9696; }
        .deadlines-toggle { padding: 12px 28px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: #eaf6f6; font-size: 14px; font-weight: 600; color: #0A9696; user-select: none; }
        .deadlines-toggle:hover { background: #d6f0ef; }
        .deadlines-arrow { transition: transform 0.3s; font-size: 12px; }
        .deadlines-arrow.open { transform: rotate(180deg); }
        .deadlines-body { max-height: 0; overflow: hidden; transition: max-height 0.4s ease; }
        .deadlines-body.open { max-height: 400px; }
        .deadlines-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .deadlines-table th { background: #0A9696; color: #fff; padding: 10px 16px; text-align: left; font-weight: 600; }
        .deadlines-table td { padding: 9px 16px; border-bottom: 1px solid #d6f0ef; }
        .deadlines-table tr:nth-child(even) { background: #f0fafa; }

        .layout { display: flex; max-width: 1440px; margin: 0 auto; padding: 20px 20px; gap: 24px; min-height: calc(100vh - 200px); animation: catalog-fade-up 0.5s ease 0.26s both; }

        .sidebar { width: 260px; flex-shrink: 0; }
        .sidebar-inner { position: sticky; top: 80px; background: #fff; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); overflow: hidden; display: flex; flex-direction: column; max-height: calc(100vh - 100px); animation: catalog-sidebar-in 0.5s cubic-bezier(0.22, 1, 0.36, 1) 0.3s both; }
        .sidebar-title { padding: 16px 18px 12px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6E6E6E; border-bottom: 1px solid #eee; flex-shrink: 0; }
        .cat-list { list-style: none; overflow-y: auto; overflow-x: hidden; flex: 1; scrollbar-width: thin; scrollbar-color: #0A9696 #f0f0f0; margin: 0; padding: 0; }
        .cat-list::-webkit-scrollbar { width: 5px; }
        .cat-list::-webkit-scrollbar-track { background: #f0f0f0; border-radius: 0 0 10px 0; }
        .cat-list::-webkit-scrollbar-thumb { background: #0A9696; border-radius: 10px; }
        .cat-item { padding: 10px 12px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: 12px; border-left: 3px solid transparent; transition: all 0.15s; white-space: normal; word-break: break-word; }
        .cat-item:hover { background: #f0fafa; }
        .cat-item.active { background: #D6F0EF; border-left-color: #0A9696; font-weight: 600; color: #0A9696; transform: translateX(2px); }
        .cat-item.scroll-active { background: #D6F0EF; border-left-color: #0A9696; font-weight: 600; color: #0A9696; transform: translateX(2px); }
        .cat-item.scroll-active .cat-count { background: #0A9696; color: #fff; }
        .cat-icon { margin-right: 8px; }
        .cat-count { background: #eee; border-radius: 10px; padding: 2px 8px; font-size: 11px; color: #888; font-weight: 600; }
        .cat-item.active .cat-count { background: #0A9696; color: #fff; }

        .main { flex: 1; min-width: 0; }
        .search-box { margin-bottom: 20px; position: relative; }
        .search-box input { width: 100%; padding: 12px 16px 12px 44px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; font-family: inherit; background: #fff; transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s; }
        .search-box input:focus { outline: none; border-color: #0A9696; box-shadow: 0 0 0 3px rgba(10,150,150,0.12); transform: translateY(-1px); }
        .search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #aaa; transition: color 0.2s; }
        .search-box:focus-within .search-icon { color: #0A9696; }
        .results-info { font-size: 13px; color: #888; margin-bottom: 16px; transition: opacity 0.25s ease; }
        .results-info.is-updating { opacity: 0.45; }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; }
        .grid-sentinel { grid-column: 1 / -1; display: flex; align-items: center; justify-content: center; padding: 28px 0 8px; min-height: 56px; }
        .grid-sentinel__spinner { width: 28px; height: 28px; border: 3px solid #D6F0EF; border-top-color: #0A9696; border-radius: 50%; animation: catalog-spin 0.75s linear infinite; }
        .grid-end-note { grid-column: 1 / -1; text-align: center; font-size: 12px; color: #aaa; padding: 8px 0 16px; }

        @keyframes catalog-spin { to { transform: rotate(360deg); } }
        @keyframes catalog-card-in {
            from { opacity: 0; transform: scale(0.985); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes catalog-grid-in {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes catalog-hero-in {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes catalog-hero-logo-in {
            from { opacity: 0; transform: scale(0.94); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes catalog-hero-text-in {
            from { opacity: 0; transform: translateX(12px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes catalog-fade-up {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes catalog-sidebar-in {
            from { opacity: 0; transform: translateX(-14px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .card { background: #fff; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); overflow: hidden; display: flex; flex-direction: column; border: 1px solid #f0f0f0; opacity: 0; transform: scale(1); transform-origin: center center; transition: box-shadow 0.32s ease, transform 0.32s ease; }
        .card.card-enter { animation: catalog-card-in 0.42s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
        .card:hover { box-shadow: 0 3px 14px rgba(10,150,150,0.1); transform: scale(1.012); }
        .card.card-enter:hover { opacity: 1; transform: scale(1.012); }

        #productGrid.grid-refresh { animation: catalog-grid-in 0.28s ease; }
        .card-img-wrap { width: 100%; height: 180px; background: #f4f7f9; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .card-img { max-width: 100%; max-height: 180px; width: auto; height: auto; object-fit: contain; display: block; }
        .card-img-placeholder { width: 100%; height: 140px; background: linear-gradient(135deg, #D6F0EF 0%, #f0fdfd 100%); display: flex; align-items: center; justify-content: center; font-size: 36px; color: #b2dede; }
        .card-body { padding: 16px 18px; display: flex; flex-direction: column; flex: 1; }
        .card-code { font-size: 11px; color: #0A9696; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .card-name { font-size: 15px; font-weight: 700; color: #222; margin-bottom: 8px; line-height: 1.3; }
        .card-desc { font-size: 12px; color: #777; margin-bottom: 6px; line-height: 1.4; }
        .card-dims { font-size: 11px; color: #999; margin-bottom: 10px; }
        .card-colors { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
        .color-chip { padding: 4px 10px; border-radius: 14px; font-size: 11px; cursor: pointer; border: 1.5px solid #ddd; background: #fafafa; transition: all 0.18s ease; color: #555; }
        .color-chip:hover, .color-chip.selected { border-color: #0A9696; background: #D6F0EF; color: #0A9696; font-weight: 600; }
        .card-bottom { margin-top: auto; padding-top: 12px; border-top: 1px solid #f0f0f0; }
        .card-price { font-size: 20px; font-weight: 700; color: #0A9696; margin-bottom: 4px; }
        .card-unit { font-size: 11px; color: #999; margin-bottom: 12px; }
        .card-price.poa { color: #F59E0B; font-size: 14px; }
        .card-actions { display: flex; align-items: center; gap: 10px; }
        .qty-ctrl { display: flex; align-items: stretch; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; height: 32px; }
        .qty-btn { width: 32px; min-width: 32px; height: 32px; border: none; background: #f5f5f5; cursor: pointer; font-size: 16px; color: #555; transition: background 0.15s; display: flex; align-items: center; justify-content: center; padding: 0; line-height: 1; }
        .qty-btn:hover { background: #e0e0e0; }
        .qty-val { width: 40px; min-width: 40px; height: 32px; padding: 0; margin: 0; text-align: center; border: none; border-left: 1px solid #ddd; border-right: 1px solid #ddd; font-size: 13px; font-family: inherit; font-weight: 600; line-height: 32px; background: #fff; -moz-appearance: textfield; appearance: textfield; }
        .qty-val::-webkit-outer-spin-button,
        .qty-val::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .qty-val:focus { outline: none; background: #f0fdfd; }
        .add-btn { flex: 1; padding: 8px 16px; background: #0A9696; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; font-family: inherit; transition: background 0.2s, transform 0.15s; }
        .add-btn:hover { background: #088080; transform: translateY(-1px); }
        .add-btn:active { transform: translateY(0); }
        .quote-btn { width: 100%; padding: 10px; background: #fff; color: #F59E0B; border: 2px solid #F59E0B; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; font-family: inherit; transition: all 0.2s; }
        .quote-btn:hover { background: #FEF3C7; transform: translateY(-1px); }
        .cat-item { transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease; }

        .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 200; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        .overlay.open { opacity: 1; pointer-events: auto; }
        .drawer { position: fixed; top: 0; right: -420px; width: 420px; height: 100vh; background: #fff; z-index: 201; transition: right 0.35s cubic-bezier(0.4,0,0.2,1); display: flex; flex-direction: column; box-shadow: -4px 0 24px rgba(0,0,0,0.15); }
        .drawer.open { right: 0; }
        .drawer-head { background: #0A9696; color: #fff; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 16px; }
        .drawer-close { background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; padding: 4px 8px; }
        .drawer-items { flex: 1; overflow-y: auto; padding: 16px; }
        .drawer-empty { text-align: center; padding: 40px 20px; color: #bbb; font-size: 14px; }
        .drawer-item { padding: 14px 0; border-bottom: 1px solid #f0f0f0; }
        .di-name { font-weight: 600; font-size: 13px; color: #222; }
        .di-code { font-size: 11px; color: #aaa; }
        .di-color { font-size: 11px; color: #666; margin-top: 2px; }
        .di-row { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; }
        .di-price { font-weight: 700; color: #0A9696; font-size: 14px; }
        .di-qty { display: flex; align-items: center; gap: 4px; }
        .di-qty button { width: 26px; height: 26px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9; cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center; }
        .di-qty span { font-size: 13px; font-weight: 600; min-width: 20px; text-align: center; }
        .di-remove { font-size: 11px; color: #ef4444; cursor: pointer; border: none; background: none; font-family: inherit; margin-top: 4px; }
        .di-remove:hover { text-decoration: underline; }
        .drawer-summary { padding: 16px 20px; border-top: 2px solid #f0f0f0; background: #fafafa; }
        .sum-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; color: #666; }
        .sum-row.total { font-size: 16px; font-weight: 700; color: #222; margin-top: 8px; padding-top: 8px; border-top: 1px solid #ddd; }
        .checkout-btn { width: 100%; padding: 14px; background: #0A9696; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 700; font-family: inherit; margin-top: 12px; transition: background 0.2s; }
        .checkout-btn:hover { background: #088080; }
        .checkout-btn:disabled { background: #ccc; cursor: not-allowed; }

        .back-to-top {
            position: fixed;
            bottom: 28px;
            left: 28px;
            z-index: 250;
            width: 46px;
            height: 46px;
            border: none;
            border-radius: 50%;
            background: #0A9696;
            color: #fff;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(10, 150, 150, 0.35);
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px) scale(0.92);
            transition: opacity 0.28s ease, transform 0.28s ease, visibility 0.28s ease, background 0.2s ease;
            font-family: inherit;
        }
        .back-to-top:hover { background: #088080; transform: translateY(0) scale(1.04); }
        .back-to-top.is-visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }
        .back-to-top.is-visible:hover { transform: translateY(-2px) scale(1.04); }

        .vat-note { font-size: 11px; color: #0A9696; background: #D6F0EF; padding: 8px 14px; border-radius: 6px; margin-bottom: 16px; text-align: center; font-weight: 500; }

        @media (max-width: 1024px) {
            .grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
        }
        @media (max-width: 768px) {
            .layout { flex-direction: column; padding: 12px; }
            .sidebar { width: 100%; }
            .sidebar-inner { position: static; max-height: none; }
            .cat-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 2px; overflow-y: visible; }
            .cat-item { padding: 8px 12px; font-size: 12px; border-left: none; border-bottom: 2px solid transparent; }
            .cat-item.active { border-bottom-color: #0A9696; border-left: none; }
            .cat-item.scroll-active { border-bottom-color: #0A9696; border-left: none; }
            .grid { grid-template-columns: 1fr; }
            .back-to-top { bottom: 20px; left: 20px; width: 42px; height: 42px; font-size: 18px; }
            .drawer { width: 100%; right: -100%; }
            .header { padding: 10px 14px; }
            .header-center h1 { font-size: 16px; }
            .info-bar { padding: 8px 14px; flex-direction: column; gap: 4px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .card, .card.card-enter, .card:hover, #productGrid.grid-refresh,
            .add-btn, .quote-btn, .grid-sentinel__spinner,
            .event-hero, .event-hero-logo, .event-hero-info, .info-bar, .layout, .sidebar-inner,
            .search-box input, .results-info {
                animation: none !important;
                transition: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
            .back-to-top { transition: none !important; }
        }
    </style>
</head>
<body>

<?php 
$header_event_logo = $event['logo'];
$header_center = [
    'title' => 'Event Services Catalog',
    'subtitle' => $event["name"] . ' — ' . $event["dates"]
];
ob_start(); ?>
    <button class="cart-btn" id="openCart">
        &#128722; Cart <span class="cart-badge" id="cartBadge">0</span>
    </button>
<?php 
$header_right = ob_get_clean();
$is_catalog_page = true;
include __DIR__ . '/storefront/_header.php'; 
?>

<div class="event-hero">
    <div class="event-hero-logo">
        <img src="<?php echo htmlspecialchars($event['logo']); ?>" alt="<?php echo htmlspecialchars($event['short_name']); ?>">
    </div>
    <div class="event-hero-divider"></div>
    <div class="event-hero-info">
        <h2>Event Services Catalog 2026</h2>
        <div class="hero-dates">&#128197; <?php echo htmlspecialchars($event["dates"]); ?></div>
        <div class="hero-venue">&#128205; <?php echo htmlspecialchars($event["venue"]); ?></div>
        <span class="hero-tagline">&#128722; Order your stand furniture &amp; services below</span>
    </div>
</div>

<div class="info-bar">
    <span>&#128205; <?php echo htmlspecialchars($event["venue"]); ?></span>
    <span>&#9993; <?php echo htmlspecialchars($event["contact_email"]); ?></span>
    <span>&#9742; <?php echo htmlspecialchars($config["contact_phone"]); ?></span>
</div>

<div class="deadlines">
    <div class="deadlines-toggle" id="dlToggle">
        <span>&#128197; Order Deadlines &mdash; Click to view</span>
        <span class="deadlines-arrow" id="dlArrow">&#9660;</span>
    </div>
    <div class="deadlines-body" id="dlBody">
        <table class="deadlines-table">
            <thead><tr><th>Category</th><th>Order Deadline</th></tr></thead>
            <tbody>
                <?php foreach ($event["deadlines"] as $dl): ?>
                <tr><td><?php echo htmlspecialchars($dl["category"]); ?></td><td><strong><?php echo htmlspecialchars($dl["deadline"]); ?></strong></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="padding:12px 16px;font-size:11px;color:#888;font-style:italic;">Rush orders received after cut-off dates are subject to availability and may incur a surcharge. All prices are exclusive of 16% VAT.</p>
    </div>
</div>

<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-inner">
            <div class="sidebar-title">Categories</div>
            <ul class="cat-list" id="catList">
                <li class="cat-item active" data-cat="all">
                    <span><span class="cat-icon">&#128230;</span> All Products</span>
                    <span class="cat-count" id="cnt-all"><?php echo (int)$products_count; ?></span>
                </li>
                <?php foreach ($categories as $cat): ?>
                <li class="cat-item" data-cat="<?php echo htmlspecialchars($cat['id']); ?>">
                    <span><span class="cat-icon"><?php echo htmlspecialchars($cat["icon"]); ?></span> <?php echo htmlspecialchars($cat["name"]); ?></span>
                    <span class="cat-count" id="cnt-<?php echo htmlspecialchars($cat['id']); ?>">0</span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

    <main class="main">
        <div class="search-box">
            <span class="search-icon">&#128269;</span>
            <input type="text" id="searchInput" placeholder="Search by product code or name...">
        </div>
        <div class="vat-note">All prices shown are in USD, exclusive of 16% VAT</div>
        <div class="results-info" id="resultsInfo"></div>
        <div class="grid" id="productGrid"></div>
    </main>
</div>

<div class="overlay" id="overlay"></div>
<div class="drawer" id="drawer">
    <div class="drawer-head">
        <span>&#128722; Shopping Cart</span>
        <button class="drawer-close" id="closeCart">&times;</button>
    </div>
    <div class="drawer-items" id="drawerItems">
        <div class="drawer-empty">Your cart is empty</div>
    </div>
    <div class="drawer-summary">
        <div class="sum-row"><span>Subtotal (excl. VAT):</span><span id="sumSub">$0.00</span></div>
        <div class="sum-row"><span>VAT (16%):</span><span id="sumVat">$0.00</span></div>
        <div class="sum-row total"><span>Total:</span><span id="sumTotal">$0.00</span></div>
        <button class="checkout-btn" id="checkoutBtn" disabled>Proceed to Checkout</button>
    </div>
</div>

<?php include __DIR__ . '/storefront/_footer.php'; ?>
<?php include __DIR__ . '/storefront/_toast.php'; ?>

<button type="button" class="back-to-top" id="backToTop" aria-label="Back to top" title="Back to top">&#8593;</button>

<script>
var PRODUCTS = <?php echo $products_json; ?>;
var PRODUCT_IMAGES = <?php echo $product_images_json; ?>;
var PRODUCT_IMAGE_VERSIONS = <?php echo $product_image_versions_json; ?>;
var PRODUCT_PLACEHOLDER_IMAGE = <?php echo json_encode($product_placeholder_image); ?>;
var EVENT_SLUG = "<?php echo htmlspecialchars($event_slug); ?>";
var VAT_RATE = <?php echo (int)$config["vat_rate"]; ?>;
var CONTACT_EMAIL = "<?php echo htmlspecialchars($config['contact_email']); ?>";

var cart = [];
var activeCat = 'all';
var searchQ = '';
var CARD_BATCH = 24;
var visibleCount = CARD_BATCH;
var renderedCount = 0;
var gridObserver = null;
var searchDebounce = null;
var scrollSpyTick = null;

document.addEventListener('DOMContentLoaded', function() {
    loadCart();
    resetGrid();
    updateCounts();
    setupUI();
    setupBackToTop();
    setupCategoryScrollSpy();
});

function loadCart() {
    try {
        var saved = localStorage.getItem('omnishop_cart_' + EVENT_SLUG);
        cart = saved ? JSON.parse(saved) : [];
    } catch(e) { cart = []; }
    refreshCartUI();
}

function saveCart() {
    localStorage.setItem('omnishop_cart_' + EVENT_SLUG, JSON.stringify(cart));
    refreshCartUI();
}

function addToCart(prodId, qty, colorName) {
    var prod = PRODUCTS.find(function(p) { return p.id === prodId; });
    if (!prod || prod.is_poa) return;
    var key = prodId + '|' + (colorName || '');
    var existing = cart.find(function(c) { return (c.id + '|' + (c.color || '')) === key; });
    if (existing) {
        existing.quantity += qty;
    } else {
        cart.push({
            id: prod.id,
            code: prod.code,
            name: prod.name,
            price: prod.price,
            price_display: prod.price_display,
            color: colorName || null,
            quantity: qty,
            unit: prod.unit || 'per item',
            category: prod.category_id
        });
    }
    saveCart();
    showToast(prod.name + ' added to cart!');
}

function removeItem(idx) {
    cart.splice(idx, 1);
    saveCart();
}

function changeQty(idx, delta) {
    cart[idx].quantity += delta;
    if (cart[idx].quantity < 1) cart.splice(idx, 1);
    saveCart();
}

function refreshCartUI() {
    var total = cart.reduce(function(s, c) { return s + c.quantity; }, 0);
    document.getElementById('cartBadge').textContent = total;
    document.getElementById('checkoutBtn').disabled = cart.length === 0;

    var el = document.getElementById('drawerItems');
    if (cart.length === 0) {
        el.innerHTML = '<div class="drawer-empty">Your cart is empty<br><span style="font-size:12px;margin-top:8px;display:block;">Browse the catalog and add items</span></div>';
    } else {
        var html = '';
        for (var i = 0; i < cart.length; i++) {
            var c = cart[i];
            var lineTotal = (c.price * c.quantity).toFixed(2);
            html += '<div class="drawer-item">';
            html += '<div class="di-name">' + esc(c.name) + '</div>';
            html += '<div class="di-code">' + esc(c.code) + '</div>';
            if (c.color) html += '<div class="di-color">Color: ' + esc(c.color) + '</div>';
            html += '<div class="di-row">';
            html += '<div class="di-qty"><button onclick="changeQty(' + i + ',-1)">&minus;</button><span>' + c.quantity + '</span><button onclick="changeQty(' + i + ',1)">+</button></div>';
            html += '<div class="di-price">$' + lineTotal + '</div>';
            html += '</div>';
            html += '<button class="di-remove" onclick="removeItem(' + i + ')">Remove</button>';
            html += '</div>';
        }
        el.innerHTML = html;
    }

    var subtotal = cart.reduce(function(s, c) { return s + c.price * c.quantity; }, 0);
    var vat = subtotal * (VAT_RATE / 100);
    document.getElementById('sumSub').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('sumVat').textContent = '$' + vat.toFixed(2);
    document.getElementById('sumTotal').textContent = '$' + (subtotal + vat).toFixed(2);
}

function productFieldScore(haystack, term, weight) {
    haystack = String(haystack || '').toLowerCase().trim();
    term = String(term || '').toLowerCase().trim();
    if (!term || !haystack) return 0;
    if (haystack.indexOf(term) === 0) return (weight * 1000) + Math.max(0, 50 - term.length);
    if (haystack.indexOf(term) !== -1) return (weight * 500) + Math.max(0, 50 - term.length);
    var i = 0;
    for (var j = 0; j < haystack.length && i < term.length; j++) {
        if (haystack.charAt(j) === term.charAt(i)) i++;
    }
    if (i === term.length) return weight * 100;
    return 0;
}

function productMatchScore(p, query) {
    if (!query) return Number.MAX_SAFE_INTEGER;
    var terms = query.split(/\s+/).filter(Boolean);
    var total = 0;
    for (var t = 0; t < terms.length; t++) {
        var term = terms[t];
        var codeScore = productFieldScore(p.code, term, 10);
        var nameScore = productFieldScore(p.name, term, 3);
        var termScore = Math.max(codeScore, nameScore);
        if (!termScore) return 0;
        total += termScore;
    }
    return total;
}

function getFiltered() {
    var list = PRODUCTS.filter(function(p) {
        var catOk = activeCat === 'all' || p.category_id === activeCat;
        if (!catOk) return false;
        if (!searchQ) return true;
        return productMatchScore(p, searchQ) > 0;
    });
    if (searchQ) {
        list.sort(function(a, b) {
            return productMatchScore(b, searchQ) - productMatchScore(a, searchQ);
        });
    }
    return list;
}

function resetGrid() {
    visibleCount = CARD_BATCH;
    renderedCount = 0;
    renderGrid(true);
}

function buildSentinelHtml(filteredLength, shown) {
    if (shown < filteredLength) {
        return '<div id="gridSentinel" class="grid-sentinel" aria-hidden="true"><div class="grid-sentinel__spinner"></div></div>';
    }
    if (filteredLength > CARD_BATCH) {
        return '<div class="grid-end-note">All ' + filteredLength + ' products loaded</div>';
    }
    return '';
}

function renderGrid(isRefresh) {
    var filtered = getFiltered();
    var grid = document.getElementById('productGrid');
    var info = document.getElementById('resultsInfo');
    var shown = Math.min(visibleCount, filtered.length);

    if (info) {
        info.classList.add('is-updating');
        requestAnimationFrame(function() {
            requestAnimationFrame(function() { info.classList.remove('is-updating'); });
        });
    }

    if (filtered.length === 0) {
        info.textContent = 'No products match your search';
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:#aaa;"><p style="font-size:48px;margin-bottom:12px;">&#128270;</p><p>No products found</p></div>';
        renderedCount = 0;
        disconnectGridObserver();
        scheduleCategoryScrollSpy();
        return;
    }

    info.textContent = 'Showing ' + shown + ' of ' + filtered.length + ' products'
        + (shown < filtered.length ? ' — scroll for more' : '');

    if (isRefresh) {
        renderedCount = 0;
        grid.classList.remove('grid-refresh');
        void grid.offsetWidth;
        grid.classList.add('grid-refresh');
        var html = '';
        for (var i = 0; i < shown; i++) {
            html += buildCardHtml(filtered[i], i);
        }
        html += buildSentinelHtml(filtered.length, shown);
        grid.innerHTML = html;
        renderedCount = shown;
        observeGridSentinel();
        scheduleCategoryScrollSpy();
        return;
    }

    if (shown <= renderedCount) {
        return;
    }

    var sentinel = document.getElementById('gridSentinel');
    var endNote = grid.querySelector('.grid-end-note');
    if (sentinel) sentinel.remove();
    if (endNote) endNote.remove();

    var batchHtml = '';
    for (var j = renderedCount; j < shown; j++) {
        batchHtml += buildCardHtml(filtered[j], j - renderedCount);
    }
    grid.insertAdjacentHTML('beforeend', batchHtml);
    grid.insertAdjacentHTML('beforeend', buildSentinelHtml(filtered.length, shown));
    renderedCount = shown;
    observeGridSentinel();
    scheduleCategoryScrollSpy();
}

function productImageUrl(file) {
    if (!file) {
        return PRODUCT_PLACEHOLDER_IMAGE;
    }

    var stem = file.replace(/^thumb_/, '').replace(/\.[^.]+$/, '');
    var thumbFile = 'thumb_' + stem + '.webp';
    var displayFile = (PRODUCT_IMAGE_VERSIONS && PRODUCT_IMAGE_VERSIONS[thumbFile]) ? thumbFile : file;
    var url = '/static/images/products/' + displayFile;
    var version = (PRODUCT_IMAGE_VERSIONS && PRODUCT_IMAGE_VERSIONS[displayFile]) ? PRODUCT_IMAGE_VERSIONS[displayFile] : '';

    return version ? url + '?v=' + version : url;
}

function productImageMainUrl(file) {
    if (!file) {
        return PRODUCT_PLACEHOLDER_IMAGE;
    }

    var url = '/static/images/products/' + file;
    var version = (PRODUCT_IMAGE_VERSIONS && PRODUCT_IMAGE_VERSIONS[file]) ? PRODUCT_IMAGE_VERSIONS[file] : '';

    return version ? url + '?v=' + version : url;
}

function buildCardHtml(p, index) {
    var firstColorId = (p.colors && p.colors.length > 0) ? (p.colors[0].id || '01') : '';
    var delay = (index % CARD_BATCH) * 0.035;
    var html = '<div class="card card-enter" data-id="' + esc(p.id) + '" data-code="' + esc(p.code.toUpperCase()) + '" data-cat="' + esc(p.category_id || '') + '" style="animation-delay:' + delay.toFixed(3) + 's">';

    var imgData  = PRODUCT_IMAGES[p.code.toUpperCase()] || {};
    var imgFile  = imgData[firstColorId] || imgData['default'] || null;
    var catIcons = {'sofas':'🛋️','chairs':'🪑','stools':'🪑','tables':'🪑','packages':'📦','displays':'🔷','cabinets':'🗄️','audiovisual':'📺','lighting':'💡','amenities':'☕','flooring':'🟫','graphics':'🎨','staffing':'👥','catering':'🍽️','flowers':'🌸','custom':'🏗️'};
    if (imgFile) {
        var isEager = (index < 6);
        var loadingAttr = isEager ? 'loading="eager" fetchpriority="high"' : 'loading="lazy" decoding="async"';
        var imgSrc = productImageUrl(imgFile);
        var mainSrc = productImageMainUrl(imgFile);
        html += '<div class="card-img-wrap skeleton"><img class="card-img" src="' + esc(imgSrc) + '" alt="' + esc(p.name) + '" ' + loadingAttr
            + ' onload="this.parentElement.classList.remove(\'skeleton\')" onerror="this.onerror=null;this.src=\'' + esc(mainSrc) + '\'"></div>';
    } else {
        html += '<div class="card-img-wrap"><img class="card-img" src="' + esc(PRODUCT_PLACEHOLDER_IMAGE) + '" alt="' + esc(p.name) + '" loading="lazy" decoding="async"></div>';
    }
    html += '<div class="card-body">';
    html += '<div class="card-code">' + esc(p.code) + '</div>';
    html += '<div class="card-name">' + esc(p.name) + '</div>';
    if (p.description) html += '<div class="card-desc">' + esc(p.description) + '</div>';
    if (p.dimensions) html += '<div class="card-dims">&#128207; ' + esc(p.dimensions) + '</div>';

    if (p.colors && p.colors.length > 0) {
        html += '<div class="card-colors">';
        for (var c = 0; c < p.colors.length; c++) {
            var col = p.colors[c];
            var cls = c === 0 ? ' selected' : '';
            html += '<span class="color-chip' + cls + '"'
                  + ' data-color="' + esc(col.name) + '"'
                  + ' data-color-id="' + esc(col.id || String(c+1).padStart(2,'0')) + '"'
                  + ' onclick="selectChip(this)">' + esc(col.name) + '</span>';
        }
        html += '</div>';
    }

    html += '<div class="card-bottom">';
    if (p.is_poa) {
        html += '<div class="card-price poa">Price on Application</div>';
        html += '<button type="button" class="quote-btn" onclick="requestQuote(\'' + esc(p.code) + '\',\'' + esc(p.name) + '\')">&#9993; Request Quote</button>';
    } else {
        html += '<div class="card-price">' + esc(p.price_display) + '</div>';
        if (p.unit && p.unit !== 'per item') html += '<div class="card-unit">' + esc(p.unit) + '</div>';
        html += '<div class="card-actions">';
        html += '<div class="qty-ctrl"><button type="button" class="qty-btn" onclick="adjQty(this,-1)">&minus;</button><input class="qty-val" type="number" value="1" min="1" aria-label="Quantity"><button type="button" class="qty-btn" onclick="adjQty(this,1)">+</button></div>';
        html += '<button type="button" class="add-btn" onclick="addFromCard(this,\'' + esc(p.id) + '\')">Add to Cart</button>';
        html += '</div>';
    }
    html += '</div>';
    html += '</div>';
    html += '</div>';
    return html;
}

function disconnectGridObserver() {
    if (gridObserver) {
        gridObserver.disconnect();
        gridObserver = null;
    }
}

function observeGridSentinel() {
    disconnectGridObserver();
    var sentinel = document.getElementById('gridSentinel');
    if (!sentinel) return;

    gridObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (!entry.isIntersecting) return;
            var filtered = getFiltered();
            if (visibleCount >= filtered.length) return;
            visibleCount = Math.min(visibleCount + CARD_BATCH, filtered.length);
            renderGrid(false);
        });
    }, { root: null, rootMargin: '240px 0px', threshold: 0.01 });

    gridObserver.observe(sentinel);
}

function updateCounts() {
    document.getElementById('cnt-all').textContent = PRODUCTS.length;
    var counts = {};
    PRODUCTS.forEach(function(p) {
        counts[p.category_id] = (counts[p.category_id] || 0) + 1;
    });
    Object.keys(counts).forEach(function(catId) {
        var el = document.getElementById('cnt-' + catId);
        if (el) el.textContent = counts[catId];
    });
}

function selectChip(el) {
    var siblings = el.parentElement.querySelectorAll('.color-chip');
    for (var i = 0; i < siblings.length; i++) siblings[i].classList.remove('selected');
    el.classList.add('selected');

    var card     = el.closest('.card');
    var code     = card.getAttribute('data-code');
    var colorId  = el.getAttribute('data-color-id');
    var imgData  = (code && PRODUCT_IMAGES[code]) ? PRODUCT_IMAGES[code] : {};
    var newFile  = imgData[colorId] || imgData['default'] || null;
    var wrap     = card.querySelector('.card-img-wrap');
    var img      = card.querySelector('.card-img');

    if (!newFile) {
        if (img) {
            wrap.classList.remove('skeleton');
            img.src = PRODUCT_PLACEHOLDER_IMAGE;
        }
        return;
    }

    if (newFile && img) {
        img.parentElement.classList.add('skeleton');
        img.onload = function() { this.parentElement.classList.remove('skeleton'); };
        img.onerror = function() { this.onerror = null; this.src = productImageMainUrl(newFile); };
        img.src = productImageUrl(newFile);
    } else if (newFile && wrap) {
        wrap.classList.add('skeleton');
        wrap.innerHTML = '<img class="card-img" src="' + esc(productImageUrl(newFile)) + '" loading="lazy" onload="this.parentElement.classList.remove(\'skeleton\')" onerror="this.onerror=null;this.src=\'' + esc(productImageMainUrl(newFile)) + '\'">';
    }
}

function adjQty(btn, delta) {
    var input = btn.parentElement.querySelector('.qty-val');
    var val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    input.value = val;
}

function addFromCard(btn, prodId) {
    var card = btn.closest('.card');
    var qtyInput = card.querySelector('.qty-val');
    var qty = parseInt(qtyInput.value) || 1;
    var selColor = card.querySelector('.color-chip.selected');
    var colorName = selColor ? selColor.getAttribute('data-color') : null;
    addToCart(prodId, qty, colorName);
    qtyInput.value = 1;
}

function requestQuote(code, name) {
    window.location.href = 'mailto:' + CONTACT_EMAIL + '?subject=Quote Request: ' + code + ' - ' + name + '&body=Hi OmniSpace,%0A%0AI would like to request a quote for:%0A%0AProduct: ' + name + ' (' + code + ')%0AEvent: ' + EVENT_SLUG + '%0A%0APlease provide pricing and availability.%0A%0AThank you.';
}

// showToast and esc moved to _toast.php

function isScrollSpyEnabled() {
    return activeCat === 'all' && !searchQ;
}

function clearScrollSpyHighlight() {
    document.querySelectorAll('.cat-item.scroll-active').forEach(function(item) {
        item.classList.remove('scroll-active');
    });
}

function setCategoryNavActive(catId) {
    var items = document.querySelectorAll('.cat-item');
    for (var i = 0; i < items.length; i++) {
        var item = items[i];
        var id = item.getAttribute('data-cat');
        item.classList.remove('scroll-active');
        item.classList.toggle('active', id === catId);
    }
}

function updateCategoryScrollSpy() {
    if (!isScrollSpyEnabled()) {
        clearScrollSpyHighlight();
        setCategoryNavActive(activeCat);
        return;
    }

    var items = document.querySelectorAll('.cat-item');
    for (var i = 0; i < items.length; i++) {
        items[i].classList.remove('active');
    }

    if (window.scrollY < 120) {
        clearScrollSpyHighlight();
        var allItem = document.querySelector('.cat-item[data-cat="all"]');
        if (allItem) allItem.classList.add('active');
        return;
    }

    var cards = document.querySelectorAll('#productGrid .card[data-cat]');
    if (!cards.length) {
        clearScrollSpyHighlight();
        return;
    }

    var focusLine = window.innerHeight * 0.32;
    var bestCat = null;
    var bestScore = -Infinity;

    for (var c = 0; c < cards.length; c++) {
        var card = cards[c];
        var rect = card.getBoundingClientRect();
        if (rect.bottom <= 0 || rect.top >= window.innerHeight) continue;

        var cat = card.getAttribute('data-cat');
        if (!cat) continue;

        var visibleTop = Math.max(rect.top, 0);
        var visibleBottom = Math.min(rect.bottom, window.innerHeight);
        var visibleHeight = visibleBottom - visibleTop;
        if (visibleHeight <= 0) continue;

        var score = visibleHeight;
        if (rect.top <= focusLine && rect.bottom >= focusLine) {
            score += 10000;
        } else if (rect.top <= focusLine) {
            score += 5000 - (focusLine - rect.top);
        } else {
            score -= rect.top - focusLine;
        }

        if (score > bestScore) {
            bestScore = score;
            bestCat = cat;
        }
    }

    clearScrollSpyHighlight();
    if (bestCat) {
        var match = null;
        document.querySelectorAll('.cat-item[data-cat]').forEach(function(item) {
            if (item.getAttribute('data-cat') === bestCat) match = item;
        });
        if (match) {
            match.classList.add('scroll-active');
            scrollCategoryIntoView(match);
        }
    }
}

function scrollCategoryIntoView(item) {
    var list = document.getElementById('catList');
    if (!list || !item) return;

    var listRect = list.getBoundingClientRect();
    var itemRect = item.getBoundingClientRect();
    if (itemRect.top < listRect.top + 8 || itemRect.bottom > listRect.bottom - 8) {
        item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
}

function scheduleCategoryScrollSpy() {
    if (scrollSpyTick) cancelAnimationFrame(scrollSpyTick);
    scrollSpyTick = requestAnimationFrame(function() {
        scrollSpyTick = null;
        updateCategoryScrollSpy();
    });
}

function setupCategoryScrollSpy() {
    window.addEventListener('scroll', scheduleCategoryScrollSpy, { passive: true });
    window.addEventListener('resize', scheduleCategoryScrollSpy, { passive: true });
}

function setupBackToTop() {
    var btn = document.getElementById('backToTop');
    if (!btn) return;

    function toggleBackToTop() {
        btn.classList.toggle('is-visible', window.scrollY > 420);
    }

    window.addEventListener('scroll', toggleBackToTop, { passive: true });
    toggleBackToTop();

    btn.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

function setupUI() {
    document.getElementById('openCart').addEventListener('click', function() {
        document.getElementById('drawer').classList.add('open');
        document.getElementById('overlay').classList.add('open');
    });
    document.getElementById('closeCart').addEventListener('click', closeDrawer);
    document.getElementById('overlay').addEventListener('click', closeDrawer);

    function closeDrawer() {
        document.getElementById('drawer').classList.remove('open');
        document.getElementById('overlay').classList.remove('open');
    }

    document.getElementById('checkoutBtn').addEventListener('click', function() {
        if (cart.length > 0) window.location.href = '/' + EVENT_SLUG + '/checkout';
    });

    var items = document.querySelectorAll('.cat-item');
    for (var i = 0; i < items.length; i++) {
        items[i].addEventListener('click', function() {
            activeCat = this.getAttribute('data-cat');
            setCategoryNavActive(activeCat);
            resetGrid();
        });
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchDebounce);
        var input = this;
        searchDebounce = setTimeout(function() {
            searchQ = input.value.toLowerCase().trim();
            resetGrid();
        }, 180);
    });

    document.getElementById('dlToggle').addEventListener('click', function() {
        document.getElementById('dlBody').classList.toggle('open');
        document.getElementById('dlArrow').classList.toggle('open');
    });
}
</script>
</body>
</html>
