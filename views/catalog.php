<?php $page_title = htmlspecialchars($event["name"]) . ' - OmniShop'; ?>
<?php include __DIR__ . '/storefront/_head.php'; ?>
    <link rel="stylesheet" href="/static/css/catalog.css">
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

function fuzzyMatch(p, query) {
    if (!query) return Number.MAX_SAFE_INTEGER;
    var terms = query.split(/\s+/).filter(Boolean);
    var total = 0;
    for (var t = 0; t < terms.length; t++) {
        var term = terms[t];
        var codeScore = fuzzyScore(p.code, term, 10);
        var nameScore = fuzzyScore(p.name, term, 3);
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
        return fuzzyMatch(p, searchQ) > 0;
    });
    if (searchQ) {
        list.sort(function(a, b) {
            return fuzzyMatch(b, searchQ) - fuzzyMatch(a, searchQ);
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
    selectColorChip(el);

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
