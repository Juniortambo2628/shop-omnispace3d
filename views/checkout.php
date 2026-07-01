<?php
$page_title = 'Checkout - OmniShop';
$body_class = '';
$header_title = 'Checkout';
$header_right = '<a href="/' . htmlspecialchars($event_slug) . '">&#8592; Back to Catalog</a>';

ob_start();
?>

<div class="container" id="mainContainer">
    <div>
        <a href="/<?php echo htmlspecialchars($event_slug); ?>" class="back-link">&#8592; Continue Shopping</a>
        <div class="error-msg" id="errorMsg"></div>
        <button type="button" class="autofill-btn" id="autofillBtn" onclick="autofillDetails()" style="display:none">
            &#9889; Autofill with Existing Details
        </button>
        <div class="autofill-success" id="autofillSuccess">&#10003; Details filled from previous order!</div>
        <form id="orderForm" onsubmit="return false;">
            <div class="section">
                <h2>&#128100; Your Details</h2>
                <div class="row">
                    <div><label>Company Name <span class="req">*</span></label><input type="text" id="companyName" required placeholder="Your company name"></div>
                    <div><label>Stand / Booth Number <span class="req">*</span></label><input type="text" id="boothNumber" required placeholder="e.g. A12"></div>
                </div>
                <div class="row">
                    <div><label>Contact Name <span class="req">*</span></label><input type="text" id="contactName" required placeholder="Full name"></div>
                    <div><label>Email Address <span class="req">*</span></label><input type="email" id="email" required placeholder="email@company.com"></div>
                </div>
                <div class="row">
                    <div><label>Phone Number</label><input type="tel" id="phone" placeholder="+254 7XX XXX XXX"></div>
                    <div><label>Tax ID / PIN Number <span style="font-size:11px;color:#888;font-weight:400;">(required for tax invoice)</span></label><input type="text" id="taxId" placeholder="e.g. P051234567A"></div>
                </div>
                <label>Postal / Delivery Address</label><textarea id="address" rows="2" placeholder="Physical or postal address for invoice and delivery purposes"></textarea>
                <label>Special Instructions / Notes</label><textarea id="instructions" placeholder="Any special requirements, delivery notes, or questions..."></textarea>
            </div>
            <div class="section">
                <h2>&#128179; Payment Method</h2>
                <p style="font-size:13px;color:#666;margin-bottom:16px;">Select your preferred payment method:</p>
                <label class="pay-option"><input type="radio" name="payment" value="PayPal" checked><strong>PayPal</strong><span style="font-size:12px;color:#888;">- Pay securely online</span></label>
                <label class="pay-option"><input type="radio" name="payment" value="Invoice"><strong>Invoice / Bank Transfer</strong><span style="font-size:12px;color:#888;">- We'll send a tax invoice</span></label>
                <label class="pay-option"><input type="radio" name="payment" value="M-Pesa"><strong>M-Pesa</strong><span style="font-size:12px;color:#888;">- Lipa na M-Pesa (local exhibitors)</span></label>
                <div class="pay-note"><strong>Note:</strong> All orders are reviewed by our team before processing. For PayPal, you'll receive a payment link. For bank transfer, an invoice with payment details will be sent within 24 hours.</div>
            </div>
        </form>
    </div>
    <div>
        <div class="section summary-card">
            <h2>&#128722; Order Summary</h2>
            <div id="summaryItems"></div>
            <div style="margin-top:16px;">
                <div class="sum-line"><span>Subtotal (excl. VAT):</span><span id="ckSub">$0.00</span></div>
                <div class="sum-line"><span>VAT (16%):</span><span id="ckVat">$0.00</span></div>
                <div class="sum-line total"><span>Total:</span><span id="ckTotal">$0.00</span></div>
            </div>
            <button class="submit-btn" id="submitBtn" onclick="submitOrder()">Submit Order</button>
            <div class="loading" id="loadingMsg">&#9203; Processing your order...</div>
        </div>
    </div>
</div>

<script>
var EVENT_SLUG = "<?php echo htmlspecialchars($event_slug); ?>";
var VAT_RATE = <?php echo (int)$config["vat_rate"]; ?>;
var cart = [];

document.addEventListener('DOMContentLoaded', function() {
    try { cart = JSON.parse(localStorage.getItem('omnishop_cart_' + EVENT_SLUG) || '[]'); } catch(e) { cart = []; }
    if (cart.length === 0) { document.getElementById('mainContainer').style.display = 'none'; document.getElementById('emptyMsg').style.display = 'block'; return; }
    renderSummary();
    checkSavedDetails();
});

function checkSavedDetails() {
    var saved = localStorage.getItem('omnishop_details');
    if (saved) {
        try {
            var details = JSON.parse(saved);
            if (details.company_name || details.email) {
                document.getElementById('autofillBtn').style.display = 'inline-flex';
            }
        } catch(e) {}
    }
}

function saveDetails() {
    var details = {
        company_name: document.getElementById('companyName').value.trim(),
        contact_name: document.getElementById('contactName').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        address: document.getElementById('address').value.trim(),
        tax_id: document.getElementById('taxId').value.trim(),
        booth_number: document.getElementById('boothNumber').value.trim()
    };
    localStorage.setItem('omnishop_details', JSON.stringify(details));
}

function autofillDetails() {
    var saved = localStorage.getItem('omnishop_details');
    if (!saved) return;
    
    try {
        var details = JSON.parse(saved);
        if (details.company_name) document.getElementById('companyName').value = details.company_name;
        if (details.contact_name) document.getElementById('contactName').value = details.contact_name;
        if (details.email) document.getElementById('email').value = details.email;
        if (details.phone) document.getElementById('phone').value = details.phone;
        if (details.address) document.getElementById('address').value = details.address;
        if (details.tax_id) document.getElementById('taxId').value = details.tax_id;
        if (details.booth_number) document.getElementById('boothNumber').value = details.booth_number;
        
        var successEl = document.getElementById('autofillSuccess');
        successEl.style.display = 'block';
        setTimeout(function() { successEl.style.display = 'none'; }, 3000);
    } catch(e) {}
}

function renderSummary() {
    var el = document.getElementById('summaryItems'), html = '', subtotal = 0;
    for (var i = 0; i < cart.length; i++) {
        var c = cart[i], line = c.price * c.quantity;
        subtotal += line;
        html += '<div class="si"><div class="si-name">' + esc(c.name);
        if (c.color) html += '<br><span class="si-color">' + esc(c.color) + '</span>';
        html += '</div><div class="si-qty">x' + c.quantity + '</div><div class="si-price">$' + line.toFixed(2) + '</div></div>';
    }
    el.innerHTML = html;
    var vat = subtotal * (VAT_RATE / 100);
    document.getElementById('ckSub').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('ckVat').textContent = '$' + vat.toFixed(2);
    document.getElementById('ckTotal').textContent = '$' + (subtotal + vat).toFixed(2);
}

function submitOrder() {
    var errEl = document.getElementById('errorMsg');
    errEl.style.display = 'none';
    var company = document.getElementById('companyName').value.trim();
    var booth = document.getElementById('boothNumber').value.trim();
    var contact = document.getElementById('contactName').value.trim();
    var email = document.getElementById('email').value.trim();
    if (!company || !booth || !contact || !email) { errEl.textContent = 'Please fill in all required fields.'; errEl.style.display = 'block'; errEl.scrollIntoView({behavior:'smooth'}); return; }
    if (email.indexOf('@') === -1) { errEl.textContent = 'Please enter a valid email address.'; errEl.style.display = 'block'; return; }

    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').style.display = 'none';
    document.getElementById('loadingMsg').style.display = 'block';

    var items = cart.map(function(c) { return { product_id: c.id, product_name: c.name, product_code: c.code, category: c.category || '', color_id: c.colorId || '', color_name: c.color || '', quantity: c.quantity, unit_price: c.price, total_price: c.price * c.quantity }; });
    var body = { event_slug: EVENT_SLUG, company_name: company, contact_name: contact, email: email, phone: document.getElementById('phone').value.trim(), address: document.getElementById('address').value.trim(), tax_id: document.getElementById('taxId').value.trim(), booth_number: booth, special_instructions: document.getElementById('instructions').value.trim(), payment_method: document.querySelector('input[name="payment"]:checked').value, items: items };

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/orders', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onload = function() {
        if (xhr.status === 200) {
            var resp = JSON.parse(xhr.responseText);
            saveDetails();
            localStorage.removeItem('omnishop_cart_' + EVENT_SLUG);
            window.location.href = '/order/' + resp.order_id + '/confirmation';
        } else {
            var err = 'Something went wrong.'; try { err = JSON.parse(xhr.responseText).error; } catch(e) {}
            errEl.textContent = err; errEl.style.display = 'block';
            document.getElementById('submitBtn').disabled = false; document.getElementById('submitBtn').style.display = 'block'; document.getElementById('loadingMsg').style.display = 'none';
        }
    };
    xhr.onerror = function() { errEl.textContent = 'Network error.'; errEl.style.display = 'block'; document.getElementById('submitBtn').disabled = false; document.getElementById('submitBtn').style.display = 'block'; document.getElementById('loadingMsg').style.display = 'none'; };
    xhr.send(JSON.stringify(body));
}
</script>
<?php
$page_content = ob_get_clean();

$page_css = '<style>
    .container { max-width: 1100px; margin: 0 auto; padding: 30px 20px; display: grid; grid-template-columns: 1fr 380px; gap: 30px; }
    .row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .si { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
    .si-name { flex: 1; }
    .si-color { font-size: 11px; color: #888; }
    .si-qty { color: #888; margin: 0 12px; }
    .si-price { font-weight: 600; color: var(--brand-teal); min-width: 70px; text-align: right; }
    .sum-line { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
    .sum-line.total { font-size: 18px; font-weight: 700; color: #222; border-top: 2px solid var(--brand-teal); padding-top: 12px; margin-top: 8px; }
    .submit-btn { width: 100%; padding: 16px; font-size: 16px; margin-top: 16px; }
    .pay-note { background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 6px; padding: 12px; font-size: 12px; color: #92400e; margin-top: 12px; line-height: 1.5; }
    .error-msg { background: #FEE2E2; color: #991B1B; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; display: none; }
    .loading { display: none; text-align: center; padding: 12px; color: var(--brand-teal); font-size: 14px; }
    .empty-msg { text-align: center; padding: 60px 20px; color: #999; }
    .empty-msg a { color: var(--brand-teal); font-weight: 600; text-decoration: none; }
    .pay-option { display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 8px; }
    .pay-option:hover { border-color: var(--brand-teal); }
    .pay-option input { width: auto; margin: 0; }
    .req { color: #ef4444; }
    .autofill-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--brand-teal-pale); color: var(--brand-teal); border: 1px solid var(--brand-teal); border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; font-family: inherit; margin-bottom: 16px; transition: all 0.2s; }
    .autofill-btn:hover { background: var(--brand-teal); color: #fff; }
    .autofill-btn.hidden { display: none; }
    .autofill-success { background: #D1FAE5; color: #065F46; padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; margin-bottom: 12px; display: none; }
    @media (max-width: 768px) { .container { grid-template-columns: 1fr; } .summary-card { position: static; } .row { grid-template-columns: 1fr; } }
</style>';

include __DIR__ . '/storefront/_layout.php';
