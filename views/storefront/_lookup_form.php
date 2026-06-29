<?php
/**
 * Reusable Lookup Form Card for Storefront Pages
 * Variables expected:
 * - $lookup_title (string) e.g. '&#128203; Order History'
 * - $lookup_action (string) e.g. '/order/history'
 * - $lookup_subtitle_suffix (string) e.g. 'orders' or 'payments'
 * - $lookup_btn_text (string) e.g. 'Look Up Orders' or 'View Payments'
 * - $email (string|null)
 * - $search_mode (string|null)
 */
?>
<div class="section">
    <h2><?php echo $lookup_title; ?></h2>

    <?php if (!$email && ($search_mode ?? '') !== '1'): ?>
    <div class="lookup-tabs">
        <a href="<?php echo htmlspecialchars($lookup_action); ?>" class="lookup-tab active">By Email</a>
        <a href="<?php echo htmlspecialchars($lookup_action); ?>?search=1" class="lookup-tab">By Invoice Number</a>
    </div>
    <form method="GET" action="<?php echo htmlspecialchars($lookup_action); ?>" class="lookup-form" style="max-width:500px;margin:0 auto;padding:20px 20px 40px;">
        <p class="subtitle">Enter your email address to view your <?php echo htmlspecialchars($lookup_subtitle_suffix); ?>.</p>
        <label for="email">Email Address</label>
        <input type="email" name="email" id="email" placeholder="your@email.com" required>
        <button type="submit" class="submit-btn"><?php echo htmlspecialchars($lookup_btn_text); ?></button>
    </form>

    <?php elseif (($search_mode ?? '') === '1' && !$email): ?>
    <div class="lookup-tabs">
        <a href="<?php echo htmlspecialchars($lookup_action); ?>" class="lookup-tab">By Email</a>
        <a href="<?php echo htmlspecialchars($lookup_action); ?>?search=1" class="lookup-tab active">By Invoice Number</a>
    </div>
    <form method="GET" action="<?php echo htmlspecialchars($lookup_action); ?>" class="lookup-form" style="max-width:500px;margin:0 auto;padding:20px 20px 40px;">
        <input type="hidden" name="search" value="1">
        <p class="subtitle">Enter your invoice number to find a specific order.</p>
        <label for="invoice">Invoice Number</label>
        <input type="text" name="q" id="invoice" placeholder="e.g. OMN-SSL26-001" required>
        <button type="submit" class="submit-btn">Find Order</button>
    </form>
    <?php endif; ?>
</div>
