<?php
$page_title = 'Make Payment - ' . htmlspecialchars($order['custom_order_id'] ?? '');
$header_title = 'Make Payment';
$queryParams = $_GET ?? [];

ob_start();
?>

<div class="container">
    <a href="/order/history?email=<?php echo urlencode($order['email'] ?? ''); ?>" class="back-link">&larr; Back to Order History</a>

    <?php if (!empty($queryParams['submitted'])): ?>
    <div class="success-banner">
        <h3>Payment Reference Submitted</h3>
        <p>Your reference has been submitted and our team will verify it shortly.</p>
    </div>
    <?php endif; ?>

    <?php if (!empty($queryParams['error'])): ?>
    <div class="error-banner">
        <?php echo $queryParams['error'] === 'missing' ? 'Please provide an email and payment reference.' : 'An error occurred. Please try again.'; ?>
    </div>
    <?php endif; ?>

    <div class="pay-layout">
        <div>
            <div class="section">
                <span class="method-badge"><?php echo htmlspecialchars($order['payment_method'] ?? 'Bank Transfer'); ?></span>
                <h2 style="margin-bottom:4px;"><?php echo htmlspecialchars($order['custom_order_id'] ?? $order['id']); ?></h2>
                <p style="font-size:13px;color:var(--color-text-muted);margin-bottom:16px;"><?php echo htmlspecialchars($order['company_name'] ?? ''); ?> &bull; Booth <?php echo htmlspecialchars($order['booth_number'] ?? ''); ?></p>

                <div class="order-summary">
                    <div class="detail-row"><span class="label">Contact</span><span class="value"><?php echo htmlspecialchars($order['contact_name'] ?? ''); ?></span></div>
                    <div class="detail-row"><span class="label">Email</span><span class="value"><?php echo htmlspecialchars($order['email'] ?? ''); ?></span></div>
                    <div class="detail-row"><span class="label">Booth</span><span class="value"><?php echo htmlspecialchars($order['booth_number'] ?? '—'); ?></span></div>
                    <div class="detail-row"><span class="label">Payment Method</span><span class="value"><?php echo htmlspecialchars($order['payment_method'] ?? '—'); ?></span></div>
                    <div class="detail-row"><span class="label">Order Status</span><span class="value"><span class="badge badge-<?php echo htmlspecialchars($order['status'] ?? 'Pending'); ?>"><?php echo htmlspecialchars($order['status'] ?? 'Pending'); ?></span></span></div>
                </div>

                <div class="pay-total">$<?php echo number_format($order['total'] ?? 0, 2); ?> USD</div>
            </div>

            <?php if (!empty($order['client_payment_reference']) && ($order['payment_verification_status'] ?? '') === 'verified'): ?>
            <div class="section">
                <div class="ref-status verified">
                    &#10003; Payment Verified &mdash; Reference: <?php echo htmlspecialchars($order['client_payment_reference']); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div>
            <?php
            $method = strtolower($order['payment_method'] ?? 'bank transfer');
            ?>

            <?php if (str_contains($method, 'bank') || str_contains($method, 'transfer')): ?>
            <div class="section instruction-card">
                <h3>&#127974; Bank Transfer Instructions</h3>
                <div class="step"><span class="step-num">1</span><span>Transfer the total amount to the bank account below.</span></div>
                <div class="step"><span class="step-num">2</span><span>Use your Invoice ID <strong><?php echo htmlspecialchars($order['custom_order_id'] ?? $order['id']); ?></strong> as the payment reference.</span></div>
                <div class="step"><span class="step-num">3</span><span>Submit your transaction reference below after payment.</span></div>
                <?php if (!empty($bank_details)): ?>
                <div class="bank-box">
                    <?php foreach (explode("\n", $bank_details) as $line): ?>
                        <?php $parts = explode(':', trim($line), 2); ?>
                        <?php if (count($parts) === 2): ?>
                        <div style="margin-bottom:6px;"><span class="field-label"><?php echo htmlspecialchars(trim($parts[0])); ?></span><br><span class="field-value"><?php echo htmlspecialchars(trim($parts[1])); ?></span></div>
                        <?php else: ?>
                        <div style="margin-bottom:6px;font-weight:600;color:var(--color-text);"><?php echo htmlspecialchars(trim($line)); ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($bank_warning)): ?>
                <div class="warning-box">&#9888; <?php echo htmlspecialchars($bank_warning); ?></div>
                <?php endif; ?>
            </div>

            <?php elseif (str_contains($method, 'paypal')): ?>
            <div class="section instruction-card">
                <h3>&#128177; PayPal Payment</h3>
                <div class="step"><span class="step-num">1</span><span>Click the PayPal link below to make your payment.</span></div>
                <div class="step"><span class="step-num">2</span><span>In the note/memo field, enter your Invoice ID: <strong><?php echo htmlspecialchars($order['custom_order_id'] ?? $order['id']); ?></strong></span></div>
                <div class="step"><span class="step-num">3</span><span>Submit your PayPal transaction ID below.</span></div>
                <?php if (!empty($paypalLink) && $paypalLink !== '#'): ?>
                <div style="margin-top:12px;">
                    <a href="<?php echo htmlspecialchars($paypalLink); ?>" target="_blank" class="action-btn" style="width:100%;justify-content:center;">Pay with PayPal &rarr;</a>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif (str_contains($method, 'mpesa') || str_contains($method, 'm-pesa')): ?>
            <div class="section instruction-card">
                <h3>&#128176; M-Pesa Payment</h3>
                <div class="step"><span class="step-num">1</span><span>Go to M-Pesa Lipa na M-Pesa &rarr; Pay Bill.</span></div>
                <div class="step"><span class="step-num">2</span><span>Enter Business Number and your Invoice ID <strong><?php echo htmlspecialchars($order['custom_order_id'] ?? $order['id']); ?></strong> as the account number.</span></div>
                <div class="step"><span class="step-num">3</span><span>Enter the amount: <strong>$<?php echo number_format($order['total'] ?? 0, 2); ?></strong></span></div>
                <div class="step"><span class="step-num">4</span><span>Submit the M-Pesa confirmation code below.</span></div>
            </div>

            <?php else: ?>
            <div class="section instruction-card">
                <h3>&#128179; Payment Instructions</h3>
                <div class="step"><span class="step-num">1</span><span>Complete payment using your selected method: <strong><?php echo htmlspecialchars($order['payment_method'] ?? ''); ?></strong></span></div>
                <div class="step"><span class="step-num">2</span><span>Reference your Invoice ID: <strong><?php echo htmlspecialchars($order['custom_order_id'] ?? $order['id']); ?></strong></span></div>
                <div class="step"><span class="step-num">3</span><span>Submit your transaction reference below.</span></div>
                <?php if (!empty($portal_url) && $portal_url !== '#'): ?>
                <div style="margin-top:12px;">
                    <a href="<?php echo htmlspecialchars($portal_url); ?>" target="_blank" class="action-btn" style="width:100%;justify-content:center;">Pay via Portal &rarr;</a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($payment_note)): ?>
            <div class="section" style="background:#f9fffe;border:1px solid var(--brand-teal-pale);">
                <p style="font-size:13px;color:var(--color-text-secondary);line-height:1.6;margin:0;">&#128161; <?php echo htmlspecialchars($payment_note); ?></p>
            </div>
            <?php endif; ?>

            <?php if (($order['payment_verification_status'] ?? '') !== 'verified'): ?>
            <div class="section ref-section">
                <h3>&#128221; Submit Payment Reference</h3>
                <?php if (!empty($order['client_payment_reference']) && ($order['payment_verification_status'] ?? '') === 'pending'): ?>
                <div class="ref-status submitted">
                    &#9203; Reference submitted: <?php echo htmlspecialchars($order['client_payment_reference']); ?> &mdash; Awaiting verification
                </div>
                <?php elseif (!empty($order['client_payment_reference']) && ($order['payment_verification_status'] ?? '') === 'rejected'): ?>
                <div class="ref-status rejected">
                    &#10007; Previous reference rejected: <?php echo htmlspecialchars($order['client_payment_reference']); ?> &mdash; Please resubmit
                </div>
                <form method="POST" action="/order/<?php echo urlencode($order['id']); ?>/pay/submit-ref" class="ref-form">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($order['email'] ?? ''); ?>">
                    <label>Payment Reference / Transaction ID <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="payment_reference" required placeholder="e.g. TXN-2026-001234 or MPESA-XXXXXXXX">
                    <p class="help-text">Enter the new reference from your bank statement or payment confirmation.</p>
                    <button type="submit" class="submit-btn">Submit New Reference</button>
                </form>
                <?php else: ?>
                <p>After completing your payment, enter the transaction reference here so our team can verify it.</p>
                <form method="POST" action="/order/<?php echo urlencode($order['id']); ?>/pay/submit-ref" class="ref-form">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($order['email'] ?? ''); ?>">
                    <label>Payment Reference / Transaction ID <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="payment_reference" required placeholder="e.g. TXN-2026-001234 or MPESA-XXXXXXXX">
                    <p class="help-text">This is the reference shown on your bank statement, M-Pesa confirmation, or PayPal receipt.</p>
                    <button type="submit" class="submit-btn">Submit Payment Reference</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div style="margin-top:16px;">
                <a href="/order/<?php echo urlencode($order['id']); ?>/invoice" class="action-btn action-btn-outline" style="width:100%;justify-content:center;" target="_blank">&#128424; Download Invoice PDF</a>
            </div>

            <?php if (!empty($contactEmail) || !empty($contactPhone)): ?>
            <div style="margin-top:16px;text-align:center;font-size:12px;color:var(--color-text-muted);">
                Need help? Contact us at
                <?php if (!empty($contactEmail)): ?><a href="mailto:<?php echo htmlspecialchars($contactEmail); ?>"><?php echo htmlspecialchars($contactEmail); ?></a><?php endif; ?>
                <?php if (!empty($contactEmail) && !empty($contactPhone)): ?> &bull; <?php endif; ?>
                <?php if (!empty($contactPhone)): ?><?php echo htmlspecialchars($contactPhone); ?><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="/static/js/storefront.js"></script>
<script>
<?php if (!empty($queryParams['submitted'])): ?>
showToast('Payment reference submitted successfully!');
<?php endif; ?>
<?php if (!empty($queryParams['error'])): ?>
showToast('Error: please check your submission.');
<?php endif; ?>
</script>
<?php
$page_content = ob_get_clean();

$page_css = '<style>
    .pay-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }
    .order-summary .detail-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; border-bottom: 1px solid var(--color-divider); }
    .order-summary .detail-row .label { color: var(--color-text-muted); }
    .order-summary .detail-row .value { font-weight: 600; }
    .pay-total { font-size: 24px; font-weight: 700; color: var(--brand-teal); text-align: right; margin-top: 12px; padding-top: 12px; border-top: 2px solid var(--brand-teal); }
    .method-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 700; background: var(--brand-teal-pale); color: var(--brand-teal); margin-bottom: 16px; }
    .instruction-card { background: #f9fffe; border: 1px solid var(--brand-teal-pale); border-radius: 10px; padding: 20px; margin-bottom: 16px; }
    .instruction-card h3 { font-size: 15px; font-weight: 700; color: var(--brand-teal); margin-bottom: 12px; }
    .instruction-card .step { display: flex; gap: 12px; margin-bottom: 12px; font-size: 13px; line-height: 1.6; }
    .instruction-card .step-num { flex: 0 0 28px; height: 28px; background: var(--brand-teal); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
    .bank-box { background: #fff; border: 1px solid var(--color-border); border-radius: 8px; padding: 16px; margin-top: 12px; font-size: 13px; line-height: 1.8; }
    .bank-box .field-label { font-size: 11px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
    .bank-box .field-value { font-weight: 600; color: var(--color-text); }
    .warning-box { background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 8px; padding: 12px 16px; font-size: 12px; color: #92400E; font-weight: 600; margin-top: 12px; }
    .ref-section { margin-top: 12px; }
    .ref-section h3 { font-size: 15px; font-weight: 700; color: var(--color-text); margin-bottom: 8px; }
    .ref-section p { font-size: 13px; color: var(--color-text-secondary); margin-bottom: 16px; line-height: 1.6; }
    .ref-status { display: flex; align-items: center; gap: 10px; padding: 14px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; }
    .ref-status.submitted { background: #FEF3C7; color: #92400E; border: 1px solid #F59E0B; }
    .ref-status.verified { background: #D1FAE5; color: #065F46; border: 1px solid #10B981; }
    .ref-status.rejected { background: #FEE2E2; color: #991B1B; border: 1px solid #EF4444; }
    .ref-form input[type="text"] { margin-bottom: 12px; }
    .ref-form .submit-btn { margin-top: 4px; }
    .help-text { font-size: 12px; color: var(--color-text-muted); margin-top: -10px; margin-bottom: 18px; }
    .success-banner { background: #D1FAE5; border: 1px solid #10B981; border-radius: 8px; padding: 16px; text-align: center; margin-bottom: 16px; }
    .success-banner h3 { color: #065F46; font-size: 15px; margin-bottom: 4px; }
    .success-banner p { color: #065F46; font-size: 13px; }
    .error-banner { background: #FEE2E2; border: 1px solid #EF4444; border-radius: 8px; padding: 14px; text-align: center; margin-bottom: 16px; font-size: 13px; color: #991B1B; }
    @media (max-width: 768px) { .pay-layout { grid-template-columns: 1fr; } }
</style>';

include __DIR__ . '/_layout.php';
