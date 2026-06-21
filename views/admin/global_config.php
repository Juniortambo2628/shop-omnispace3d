<?php $active_page = 'global_config'; ?>
<style>
    .config-card{background:#fff;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.06);padding:28px 32px;margin-bottom:24px}
    .config-card h2{font-size:16px;font-weight:700;margin-bottom:6px;color:#222;display:flex;align-items:center;gap:10px}
    .config-card .desc{font-size:13px;color:#777;margin-bottom:20px;line-height:1.6}
    .field-group{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}
    .field{display:flex;flex-direction:column;gap:5px}
    .field label{font-size:12px;font-weight:700;color:#555}
    .field input,.field textarea,.field select{transition:all .2s;padding:10px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px;font-family:inherit}
    .field input:focus,.field textarea:focus{outline:none;border-color:#0A9696;box-shadow:0 0 0 3px rgba(10,150,150,.1)}
    .field textarea{resize:vertical;min-height:100px;line-height:1.5}
    .field .hint{font-size:11px;color:#999;margin-top:2px;line-height:1.4}
    .separator{border:none;border-top:1px solid #f0f0f0;margin:22px 0}
    .full-width{grid-column:1/-1}
    .disclaimer-box{font-size:11px;line-height:1.6;color:#555;background:#f9fafb;border:1px solid #eee;border-radius:8px;padding:14px 16px;max-height:320px;overflow-y:auto;white-space:pre-wrap;margin-top:8px}
    .warning-box{font-size:11px;line-height:1.6;color:#92400e;background:#FEF3C7;border:1px solid #F59E0B;border-radius:8px;padding:14px 16px;max-height:200px;overflow-y:auto;white-space:pre-wrap;margin-top:8px}
    @media(max-width:700px){.field-group{grid-template-columns:1fr}}
</style>

<div class="container">
  <?php include __DIR__ . '/_header.php'; ?>

  <?php if (isset($_GET['saved'])): ?>
  <div class="alert alert-success">✓ Global configuration saved successfully.</div>
  <?php endif; ?>

  <!-- PAYMENT SETTINGS -->
  <div class="config-card">
    <h2>💳 Payment Settings</h2>
    <p class="desc">Configure payment methods, bank transfer details, and payment portal links. These appear on invoices and order notification emails.</p>

    <form action="/admin/global-config" method="POST">
      <input type="hidden" name="action" value="payment">
      <div class="field-group">
        <div class="field full-width">
          <label>Bank Transfer Details</label>
          <textarea name="bank_transfer_details" rows="6" placeholder="Account Name, Bank, Branch, Account Number, SWIFT Code..."><?php echo htmlspecialchars($settings['bank_transfer_details'] ?? ''); ?></textarea>
          <span class="hint">Displayed on invoices and payment instruction emails. One detail per line recommended.</span>
        </div>
      </div>
      <div class="field-group">
        <div class="field">
          <label>PayPal Payment Link</label>
          <input type="url" name="paypal_payment_link" value="<?php echo htmlspecialchars($settings['paypal_payment_link'] ?? ''); ?>" placeholder="https://paypal.me/yourbusiness">
          <span class="hint">Full PayPal.me or hosted payment page URL</span>
        </div>
        <div class="field">
          <label>Payment Portal URL</label>
          <input type="url" name="payment_portal_url" value="<?php echo htmlspecialchars($settings['payment_portal_url'] ?? ''); ?>" placeholder="https://payments.omnispace3d.com">
          <span class="hint">External payment portal base URL sent in availability confirmation emails</span>
        </div>
      </div>
      <div style="margin-top:16px">
        <button type="submit" class="btn btn-primary">💾 Save Payment Settings</button>
      </div>
    </form>
  </div>

  <!-- COMPANY & TAX DETAILS -->
  <div class="config-card">
    <h2>🏢 Company & Tax Details</h2>
    <p class="desc">Company information and tax registration details shown on all invoices and official documents.</p>

    <form action="/admin/global-config" method="POST">
      <input type="hidden" name="action" value="company">
      <div class="field-group">
        <div class="field">
          <label>Company Name</label>
          <input type="text" name="company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? 'OmniSpace 3D Events Ltd'); ?>">
        </div>
        <div class="field">
          <label>PIN / Tax Registration Number</label>
          <input type="text" name="company_pin" value="<?php echo htmlspecialchars($settings['company_pin'] ?? ''); ?>" placeholder="e.g. P051234567A">
          <span class="hint">Appended to the FROM block on invoices</span>
        </div>
      </div>
      <div class="field-group">
        <div class="field">
          <label>Company Email</label>
          <input type="email" name="company_email" value="<?php echo htmlspecialchars($settings['company_email'] ?? ''); ?>">
        </div>
        <div class="field">
          <label>Company Phone</label>
          <input type="text" name="company_phone" value="<?php echo htmlspecialchars($settings['company_phone'] ?? ''); ?>">
        </div>
      </div>
      <div class="field-group">
        <div class="field">
          <label>Website</label>
          <input type="text" name="company_website" value="<?php echo htmlspecialchars($settings['company_website'] ?? ''); ?>">
        </div>
        <div class="field">
          <label>WhatsApp Number</label>
          <input type="text" name="company_whatsapp" value="<?php echo htmlspecialchars($settings['company_whatsapp'] ?? ''); ?>">
        </div>
      </div>
      <div class="field full-width" style="margin-bottom:18px">
        <label>Company Address (as shown on invoices)</label>
        <textarea name="company_address" rows="2"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
      </div>
      <div style="margin-top:16px">
        <button type="submit" class="btn btn-primary">💾 Save Company Details</button>
      </div>
    </form>
  </div>

  <!-- INVOICE DISCLAIMER -->
  <div class="config-card">
    <h2>📄 Invoice Disclaimer Text</h2>
    <p class="desc">Full disclaimer text appended to the bottom of every invoice PDF. Use small compact formatting to prevent layout overflow.</p>

    <form action="/admin/global-config" method="POST">
      <input type="hidden" name="action" value="disclaimer">
      <div class="field full-width">
        <label>Disclaimer Text</label>
        <textarea name="invoice_disclaimer_text" rows="18"><?php echo htmlspecialchars($settings['invoice_disclaimer_text'] ?? ''); ?></textarea>
        <span class="hint">This text appears at the bottom of each invoice in a compact font. It includes order fulfillment instructions, payment terms, and other legal notes.</span>
      </div>
      <?php if (!empty($settings['invoice_disclaimer_text'])): ?>
      <div class="disclaimer-box"><?php echo htmlspecialchars($settings['invoice_disclaimer_text']); ?></div>
      <?php endif; ?>
      <div style="margin-top:16px">
        <button type="submit" class="btn btn-primary">💾 Save Disclaimer Text</button>
      </div>
    </form>
  </div>

  <!-- BANK CHARGE WARNING -->
  <div class="config-card">
    <h2>⚠️ Bank Charge Warning Text</h2>
    <p class="desc">Highlighted callout text shown on invoices regarding bank charges and payment requirements.</p>

    <form action="/admin/global-config" method="POST">
      <input type="hidden" name="action" value="warning">
      <div class="field full-width">
        <label>Bank Charge Warning Text</label>
        <textarea name="bank_charge_warning_text" rows="6"><?php echo htmlspecialchars($settings['bank_charge_warning_text'] ?? ''); ?></textarea>
        <span class="hint">The phrase "ALL BANK CHARGES TO BE BORNE BY SENDER" is highlighted on the invoice.</span>
      </div>
      <?php if (!empty($settings['bank_charge_warning_text'])): ?>
      <div class="warning-box"><?php echo htmlspecialchars($settings['bank_charge_warning_text']); ?></div>
      <?php endif; ?>
      <div style="margin-top:16px">
        <button type="submit" class="btn btn-primary">💾 Save Warning Text</button>
      </div>
    </form>
  </div>

  <!-- EMAIL TEMPLATES -->
  <div class="config-card">
    <h2>📧 Email Templates</h2>
    <p class="desc">
      Customize the automated email templates sent to clients at each order stage. Available variables:<br>
      <code>{contact_name}</code>, <code>{order_id}</code>, <code>{booth_number}</code>, <code>{company_email}</code>, <code>{company_phone}</code>,<br>
      <code>{paypal_link}</code>, <code>{payment_portal_url}</code>, <code>{items_table}</code>, <code>{totals_block}</code>
    </p>

    <form action="/admin/global-config" method="POST">
      <input type="hidden" name="action" value="email_templates">
      <div class="field full-width" style="margin-bottom:18px">
        <label>1. New Order Created (No Invoice Attached)</label>
        <textarea name="email_template_new_order" rows="8"><?php echo htmlspecialchars($settings['email_template_new_order'] ?? ''); ?></textarea>
        <span class="hint">Sent immediately after order submission. Thank you + review pending notice.</span>
      </div>
      <div class="field full-width" style="margin-bottom:18px">
        <label>2. Availability Confirmed (Invoice Attached)</label>
        <textarea name="email_template_availability_confirmed" rows="8"><?php echo htmlspecialchars($settings['email_template_availability_confirmed'] ?? ''); ?></textarea>
        <span class="hint">Sent when admin approves order. Includes payment instructions and dynamic portal URL.</span>
      </div>
      <div class="field full-width" style="margin-bottom:18px">
        <label>3. Payment Processed (Order Complete)</label>
        <textarea name="email_template_payment_processed" rows="8"><?php echo htmlspecialchars($settings['email_template_payment_processed'] ?? ''); ?></textarea>
        <span class="hint">Sent after payment is confirmed. Final summary with booth numbers and closure.</span>
      </div>
      <div style="margin-top:16px">
        <button type="submit" class="btn btn-primary">💾 Save Email Templates</button>
      </div>
    </form>
  </div>

  <!-- SIGNATORY LABELS -->
  <div class="config-card">
    <h2>✍️ Invoice Signatory Labels</h2>
    <p class="desc">Configure the column titles for the dual-signature section at the bottom of invoices.</p>

    <form action="/admin/global-config" method="POST">
      <input type="hidden" name="action" value="signatory">
      <div class="field-group">
        <div class="field">
          <label>Left Column Title (Customer)</label>
          <input type="text" name="invoice_signatory_left_title" value="<?php echo htmlspecialchars($settings['invoice_signatory_left_title'] ?? 'Customer Approval'); ?>">
        </div>
        <div class="field">
          <label>Right Column Title (Omnispace)</label>
          <input type="text" name="invoice_signatory_right_title" value="<?php echo htmlspecialchars($settings['invoice_signatory_right_title'] ?? 'Omnispace Verification'); ?>">
        </div>
      </div>
      <div style="margin-top:16px">
        <button type="submit" class="btn btn-primary">💾 Save Signatory Labels</button>
      </div>
    </form>
  </div>
</div>
