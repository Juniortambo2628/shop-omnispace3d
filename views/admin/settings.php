
<div class="container">
  <?php include __DIR__ . '/_header.php'; ?>

  <?php if (isset($_GET['saved'])): ?>
  <div class="alert alert-success">Settings saved successfully.</div>
  <?php endif; ?>
  <?php if (isset($_GET['tested'])): ?>
  <div class="alert alert-success">Gmail connection successful! Email is working correctly.</div>
  <?php endif; ?>
  <?php if (isset($test_err)): ?>
  <div class="alert alert-error">Email test failed: <?php echo htmlspecialchars($test_err); ?></div>
  <?php endif; ?>

  <!-- ── EMAIL SETTINGS ───────────────────────────────────────────── -->
  <div class="form-card">
    <h2>📧 Email Settings (Gmail)</h2>
    <p class="section-desc">
      OmniShop sends automatic order confirmations and payment notices by email. You need a Gmail account and a <strong>Gmail App Password</strong>.<br>
      <small>How to get an App Password: Go to <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:var(--brand-teal)">myaccount.google.com/apppasswords</a> → Create a new App Password → copy the 16-character code and paste it below.</small>
    </p>

    <form action="/admin/settings" method="POST">
      <input type="hidden" name="action" value="email">
      <div class="profile-field-group">
        <div class="profile-field">
          <label>Gmail Address (the "From" address)</label>
          <input type="email" name="gmail_address"
                 value="<?php echo htmlspecialchars($settings['gmail_address'] ?? ''); ?>"
                 placeholder="orders@omnispace3d.com">
          <span class="hint">Emails to clients will appear to come from this address</span>
        </div>
        <div class="profile-field">
          <label>Gmail App Password</label>
          <input type="password" name="gmail_app_password"
                 value="<?php echo htmlspecialchars($settings['gmail_app_password'] ?? ''); ?>"
                 placeholder="16-character app password"
                 autocomplete="off">
          <span class="hint">This is a special password from Google — not your regular Gmail password</span>
        </div>
      </div>
      <div class="profile-field-group">
        <div class="profile-field">
          <label>Admin Notification Email</label>
          <input type="email" name="admin_notification_email"
                 value="<?php echo htmlspecialchars($settings['admin_notification_email'] ?? ''); ?>"
                 placeholder="solarandstoragelive@omnispace3d.com">
          <span class="hint">New order alerts are sent to this address</span>
        </div>
        <div class="profile-field">
          <label>SMTP Host</label>
          <input type="text" name="smtp_host"
                 value="<?php echo htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com'); ?>"
                 placeholder="smtp.gmail.com">
          <span class="hint">Leave as smtp.gmail.com unless using Outlook or another provider</span>
        </div>
      </div>
      <div style="margin-top:20px">
        <button type="submit" class="btn btn-primary">Save Email Settings</button>
      </div>
    </form>

    <hr class="separator">
    <p style="font-size:12px;color:var(--color-text-secondary);margin-bottom:12px">
      Once saved, click below to test your Gmail connection:
    </p>
    <form action="/admin/test-email" method="POST" style="display:inline">
      <button type="submit" class="btn btn-outline">Test Email Connection</button>
    </form>
  </div>

  <!-- ── COMPANY DETAILS ──────────────────────────────────────────── -->
  <div class="form-card">
    <h2>🏢 Company Details (for Tax Invoices)</h2>
    <p class="section-desc">
      These details appear on the tax invoices that are attached to order confirmation emails. Fill in your full legal company name, PIN, and address as they should appear on invoices.
    </p>

    <form action="/admin/settings" method="POST">
      <input type="hidden" name="action" value="company">
      <div class="profile-field-group">
        <div class="profile-field">
          <label>Company Name</label>
          <input type="text" name="company_name"
                 value="<?php echo htmlspecialchars($settings['company_name'] ?? 'OmniSpace 3D Events Ltd'); ?>">
        </div>
        <div class="profile-field">
          <label>PIN / Tax Registration Number</label>
          <input type="text" name="company_pin"
                 value="<?php echo htmlspecialchars($settings['company_pin'] ?? ''); ?>"
                 placeholder="e.g. P051234567A">
        </div>
      </div>
      <div class="profile-field-group">
        <div class="profile-field">
          <label>VAT Registration Number [If different from PIN]</label>
          <input type="text" name="company_vat_no"
                 value="<?php echo htmlspecialchars($settings['company_vat_no'] ?? ''); ?>"
                 placeholder="Optional">
        </div>
        <div class="profile-field">
          <label>Phone</label>
          <input type="text" name="company_phone"
                 value="<?php echo htmlspecialchars($settings['company_phone'] ?? '+254 204 489 504'); ?>">
        </div>
      </div>
      <div class="profile-field-group">
        <div class="profile-field">
          <label>WhatsApp Number [shown on invoices]</label>
          <input type="text" name="company_whatsapp"
                 value="<?php echo htmlspecialchars($settings['company_whatsapp'] ?? '+254 731 001 723'); ?>">
          <span class="hint">Displayed as WhatsApp on the invoice header</span>
        </div>
      </div>
      <div class="profile-field-group">
        <div class="profile-field">
          <label>Company Email (on invoices)</label>
          <input type="email" name="company_email"
                 value="<?php echo htmlspecialchars($settings['company_email'] ?? 'solarandstorage@omnispace3d.com'); ?>">
        </div>
        <div class="profile-field">
          <label>Website</label>
          <input type="text" name="company_website"
                 value="<?php echo htmlspecialchars($settings['company_website'] ?? 'www.omnispace3d.com'); ?>">
        </div>
      </div>
      
      <div class="profile-field" style="margin-bottom:20px">
        <label>Company Address (as shown on invoices)</label>
        <textarea name="company_address" placeholder="e.g. P.O. Box 12345, Nairobi, Kenya"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
      </div>

      <div class="profile-field" style="margin-bottom:20px">
        <label>Invoice Payment Note (shown at bottom of each invoice)</label>
        <textarea name="invoice_payment_note" placeholder="e.g. Please make payment within 14 days quoting your Invoice Number..."><?php echo htmlspecialchars($settings['invoice_payment_note'] ?? ''); ?></textarea>
      </div>

      <div class="profile-field">
        <label>Terms & Conditions (printed on each invoice — one clause per line)</label>
        <textarea name="invoice_terms" placeholder="1. All prices are exclusive of VAT unless stated otherwise.&#10;2. Payment is due within 14 days of invoice date."><?php echo htmlspecialchars($settings['invoice_terms'] ?? ''); ?></textarea>
        <span class="hint">Each line becomes a numbered clause on the invoice. Leave blank to use the default T&C.</span>
      </div>

      <div style="margin-top:20px">
        <button type="submit" class="btn btn-primary">Save Company Details</button>
      </div>
    </form>
  </div>

  <!-- ── PAYMENT SETTINGS ─────────────────────────────────────────── -->
  <div class="form-card">
    <h2>💳 Payment Settings</h2>
    <p class="section-desc">Configure payment methods, bank transfer details, and payment portal links. These appear on invoices and order notification emails.</p>

    <form action="/admin/settings" method="POST">
      <input type="hidden" name="action" value="payment">
      <div class="profile-field-group">
        <div class="field full-width">
          <label>Bank Transfer Details</label>
          <textarea name="bank_transfer_details" rows="6" placeholder="Account Name, Bank, Branch, Account Number, SWIFT Code..."><?php echo htmlspecialchars($settings['bank_transfer_details'] ?? ''); ?></textarea>
          <span class="hint">Displayed on invoices and payment instruction emails. One detail per line recommended.</span>
        </div>
      </div>
      <div class="profile-field-group">
        <div class="profile-field">
          <label>PayPal Payment Link</label>
          <input type="url" name="paypal_payment_link" value="<?php echo htmlspecialchars($settings['paypal_payment_link'] ?? ''); ?>" placeholder="https://paypal.me/yourbusiness">
          <span class="hint">Full PayPal.me or hosted payment page URL</span>
        </div>
        <div class="profile-field">
          <label>Payment Portal URL</label>
          <input type="url" name="payment_portal_url" value="<?php echo htmlspecialchars($settings['payment_portal_url'] ?? ''); ?>" placeholder="https://payments.omnispace3d.com">
          <span class="hint">External payment portal base URL sent in availability confirmation emails</span>
        </div>
      </div>
      <div style="margin-top:16px">
        <button type="submit" class="btn btn-primary">Save Payment Settings</button>
      </div>
    </form>
  </div>

  <!-- ── INVOICE DISCLAIMER ───────────────────────────────────────── -->
  <div class="form-card">
    <h2>📄 Invoice Disclaimer Text</h2>
    <p class="section-desc">Full disclaimer text appended to the bottom of every invoice PDF. Use small compact formatting to prevent layout overflow.</p>

    <form action="/admin/settings" method="POST">
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
        <button type="submit" class="btn btn-primary">Save Disclaimer Text</button>
      </div>
    </form>
  </div>

  <!-- ── BANK CHARGE WARNING ──────────────────────────────────────── -->
  <div class="form-card">
    <h2>⚠️ Bank Charge Warning Text</h2>
    <p class="section-desc">Highlighted callout text shown on invoices regarding bank charges and payment requirements.</p>

    <form action="/admin/settings" method="POST">
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
        <button type="submit" class="btn btn-primary">Save Warning Text</button>
      </div>
    </form>
  </div>

  <!-- ── EMAIL TEMPLATES ──────────────────────────────────────────── -->
  <div class="form-card">
    <h2>📧 Email Templates</h2>
    <p class="section-desc">
      Customize the automated email templates sent to clients at each order stage. Available variables:<br>
      <code>{contact_name}</code>, <code>{order_id}</code>, <code>{booth_number}</code>, <code>{company_email}</code>, <code>{company_phone}</code>,<br>
      <code>{paypal_link}</code>, <code>{payment_portal_url}</code>, <code>{items_table}</code>, <code>{totals_block}</code>
    </p>

    <form action="/admin/settings" method="POST">
      <input type="hidden" name="action" value="email_templates">
      <input type="hidden" name="email_template_new_order" id="tpl_new_order">
      <input type="hidden" name="email_template_availability_confirmed" id="tpl_availability">
      <input type="hidden" name="email_template_payment_processed" id="tpl_payment">

      <div class="field full-width" style="margin-bottom:24px">
        <label>1. New Order Created (No Invoice Attached)</label>
        <p class="hint" style="margin-bottom:6px">Sent immediately after order submission. Thank you + review pending notice.</p>
        <div class="rt-editor-wrap">
          <div class="rt-toolbar">
            <button type="button" onclick="rtCmd('bold',this)" title="Bold"><b>B</b></button>
            <button type="button" onclick="rtCmd('italic',this)" title="Italic"><i>I</i></button>
            <button type="button" onclick="rtCmd('underline',this)" title="Underline"><u>U</u></button>
            <button type="button" onclick="rtCmd('insertUnorderedList',this)" title="Bullet List">&#8226; List</button>
            <button type="button" onclick="rtInsertVar(this)" data-var="{contact_name}" title="Insert variable">+{name}</button>
            <button type="button" onclick="rtInsertVar(this)" data-var="{order_id}" title="Insert variable">+{order_id}</button>
            <button type="button" onclick="rtInsertVar(this)" data-var="{booth_number}" title="Insert variable">+{booth}</button>
          </div>
          <div class="rt-content" contenteditable="true" id="editor_new_order"><?php echo $settings['email_template_new_order'] ?? ''; ?></div>
        </div>
      </div>

      <div class="field full-width" style="margin-bottom:24px">
        <label>2. Availability Confirmed (Invoice Attached)</label>
        <p class="hint" style="margin-bottom:6px">Sent when admin approves order. Includes payment instructions and dynamic portal URL.</p>
        <div class="rt-editor-wrap">
          <div class="rt-toolbar">
            <button type="button" onclick="rtCmd('bold',this)" title="Bold"><b>B</b></button>
            <button type="button" onclick="rtCmd('italic',this)" title="Italic"><i>I</i></button>
            <button type="button" onclick="rtCmd('underline',this)" title="Underline"><u>U</u></button>
            <button type="button" onclick="rtCmd('insertUnorderedList',this)" title="Bullet List">&#8226; List</button>
            <button type="button" onclick="rtInsertVar(this)" data-var="{contact_name}" title="Insert variable">+{name}</button>
            <button type="button" onclick="rtInsertVar(this)" data-var="{order_id}" title="Insert variable">+{order_id}</button>
            <button type="button" onclick="rtInsertVar(this)" data-var="{paypal_link}" title="Insert variable">+{paypal}</button>
            <button type="button" onclick="rtInsertVar(this)" data-var="{payment_portal_url}" title="Insert variable">+{portal}</button>
          </div>
          <div class="rt-content" contenteditable="true" id="editor_availability"><?php echo $settings['email_template_availability_confirmed'] ?? ''; ?></div>
        </div>
      </div>

      <div class="field full-width" style="margin-bottom:24px">
        <label>3. Payment Processed (Order Complete)</label>
        <p class="hint" style="margin-bottom:6px">Sent after payment is confirmed. Final summary with booth numbers and closure.</p>
        <div class="rt-editor-wrap">
          <div class="rt-toolbar">
            <button type="button" onclick="rtCmd('bold',this)" title="Bold"><b>B</b></button>
            <button type="button" onclick="rtCmd('italic',this)" title="Italic"><i>I</i></button>
            <button type="button" onclick="rtCmd('underline',this)" title="Underline"><u>U</u></button>
            <button type="button" onclick="rtCmd('insertUnorderedList',this)" title="Bullet List">&#8226; List</button>
            <button type="button" onclick="rtInsertVar(this)" data-var="{contact_name}" title="Insert variable">+{name}</button>
            <button type="button" onclick="rtInsertVar(this)" data-var="{order_id}" title="Insert variable">+{order_id}</button>
            <button type="button" onclick="rtInsertVar(this)" data-var="{booth_number}" title="Insert variable">+{booth}</button>
          </div>
          <div class="rt-content" contenteditable="true" id="editor_payment"><?php echo $settings['email_template_payment_processed'] ?? ''; ?></div>
        </div>
      </div>

      <div style="margin-top:16px">
        <button type="submit" class="btn btn-primary" onclick="syncRtEditors()">Save Email Templates</button>
      </div>
    </form>
  </div>

  <!-- ── SIGNATORY LABELS ─────────────────────────────────────────── -->
  <div class="form-card">
    <h2>✍️ Invoice Signatory Labels</h2>
    <p class="section-desc">Configure the column titles for the dual-signature section at the bottom of invoices.</p>

    <form action="/admin/settings" method="POST">
      <input type="hidden" name="action" value="signatory">
      <div class="profile-field-group">
        <div class="profile-field">
          <label>Left Column Title (Customer)</label>
          <input type="text" name="invoice_signatory_left_title" value="<?php echo htmlspecialchars($settings['invoice_signatory_left_title'] ?? 'Customer Approval'); ?>">
        </div>
        <div class="profile-field">
          <label>Right Column Title (Omnispace)</label>
          <input type="text" name="invoice_signatory_right_title" value="<?php echo htmlspecialchars($settings['invoice_signatory_right_title'] ?? 'Omnispace Verification'); ?>">
        </div>
      </div>
      <div style="margin-top:16px">
        <button type="submit" class="btn btn-primary">Save Signatory Labels</button>
      </div>
    </form>
  </div>

  <!-- ── CATALOG PASSWORDS ────────────────────────────────────────── -->
  <div class="form-card">
    <h2>🔒 Catalog Access Password</h2>
    <p class="section-desc">Visitors must enter this password to view the exhibitor catalog. Share it with your exhibitors when you send out event communications. Leave blank to make the catalog publicly accessible.</p>
    <form method="POST" action="/admin/settings">
      <input type="hidden" name="action" value="passwords">
      <div class="profile-field-group">
        <div class="profile-field">
          <label>Exhibitor Password</label>
          <input type="text" name="catalog_password_solarandstorage"
                 value="<?php echo htmlspecialchars($settings['catalog_password_solarandstorage'] ?? 'ssl2026'); ?>">
          <span class="hint">Share with all exhibitors. Current: <strong style="color:var(--brand-teal)"><?php echo htmlspecialchars($settings['catalog_password_solarandstorage'] ?? 'ssl2026'); ?></strong></span>
        </div>
        <div class="profile-field">
          <label>Demo / Visitor Password</label>
          <input type="text" name="catalog_password_demo"
                 value="<?php echo htmlspecialchars($settings['catalog_password_demo'] ?? ''); ?>"
                 placeholder="e.g. DWTC2026">
          <span class="hint">For prospects & clients only — separate from exhibitor password. Leave blank to disable.</span>
        </div>
      </div>
      <p style="font-size:11px;color:var(--color-text-muted);margin-top:15px">Catalog URL: <code>/solarandstorage</code> · Both passwords grant the same view-only access to the catalog.</p>
      <div style="margin-top:20px">
        <button type="submit" class="btn btn-primary">Save Passwords</button>
      </div>
    </form>
  </div>

  <!-- ── INVOICE PREVIEW ─────────────────────────────────────────── -->
  <div class="form-card">
    <h2>📄 Invoice PDF Preview</h2>
    <p class="section-desc">Preview how invoices will look with the current settings. This generates a sample invoice PDF using your configured company details, disclaimer, bank warning, and signatory labels.</p>
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
      <a href="/admin/invoice-preview" target="_blank" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
        Open Invoice Preview (PDF)
      </a>
      <span style="font-size:12px;color:var(--color-text-muted);">Opens in a new tab. Save settings first to see the latest changes.</span>
    </div>
    <div style="margin-top:16px;border:1px solid var(--color-border);border-radius:var(--radius-md);overflow:hidden;background:var(--color-bg);">
      <iframe src="/admin/invoice-preview" style="width:100%;height:700px;border:none;" title="Invoice Preview"></iframe>
    </div>
  </div>
</div>

<script src="/static/js/settings.js"></script>
