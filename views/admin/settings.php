<style>
    .card{background:#fff;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.06);padding:32px;margin-bottom:24px}
    .card h2{font-size:16px;font-weight:700;margin-bottom:8px;color:#222;display:flex;align-items:center;gap:10px}
    .card .desc{font-size:13px;color:#777;margin-bottom:24px;line-height:1.6}
    .field-group{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
    .field{display:flex;flex-direction:column;gap:6px}
    .field label{font-size:12px;font-weight:700;color:#555}
    .field input,.field textarea,.field select{transition:all .2s}
    .field input:focus,.field textarea:focus{outline:none;border-color:#0A9696;box-shadow:0 0 0 3px rgba(10,150,150,.1)}
    .field textarea{resize:vertical;min-height:90px}
    .field .hint{font-size:11px;color:#999;margin-top:2px;line-height:1.4}
    .separator{border:none;border-top:1px solid #f0f0f0;margin:24px 0}
    @media(max-width:600px){.field-group{grid-template-columns:1fr}}
</style>

<div class="container">
  <?php include __DIR__ . '/_header.php'; ?>

  <?php if (isset($_GET['saved'])): ?>
  <div class="alert alert-success">✓ Settings saved successfully.</div>
  <?php endif; ?>
  <?php if (isset($_GET['tested'])): ?>
  <div class="alert alert-success">✓ Gmail connection successful! Email is working correctly.</div>
  <?php endif; ?>
  <?php if (isset($test_err)): ?>
  <div class="alert alert-error">✗ Email test failed: <?php echo htmlspecialchars($test_err); ?></div>
  <?php endif; ?>

  <!-- ── EMAIL SETTINGS ───────────────────────────────────────────── -->
  <div class="card">
    <h2>📧 Email Settings (Gmail)</h2>
    <p class="desc">
      OmniShop sends automatic order confirmations and payment notices by email. You need a Gmail account and a <strong>Gmail App Password</strong>. <br>
      <small>How to get an App Password: Go to <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#0A9696">myaccount.google.com/apppasswords</a> → Create a new App Password → copy the 16-character code and paste it below.</small>
    </p>

    <form action="/admin/settings" method="POST">
      <input type="hidden" name="action" value="email">
      <div class="field-group">
        <div class="field">
          <label>Gmail Address (the "From" address)</label>
          <input type="email" name="gmail_address"
                 value="<?php echo htmlspecialchars($settings['gmail_address'] ?? ''); ?>"
                 placeholder="orders@omnispace3d.com">
          <span class="hint">Emails to clients will appear to come from this address</span>
        </div>
        <div class="field">
          <label>Gmail App Password</label>
          <input type="password" name="gmail_app_password"
                 value="<?php echo htmlspecialchars($settings['gmail_app_password'] ?? ''); ?>"
                 placeholder="16-character app password"
                 autocomplete="off">
          <span class="hint">This is a special password from Google — not your regular Gmail password</span>
        </div>
      </div>
      <div class="field-group">
        <div class="field">
          <label>Admin Notification Email</label>
          <input type="email" name="admin_notification_email"
                 value="<?php echo htmlspecialchars($settings['admin_notification_email'] ?? ''); ?>"
                 placeholder="solarandstoragelive@omnispace3d.com">
          <span class="hint">New order alerts are sent to this address</span>
        </div>
        <div class="field">
          <label>SMTP Host</label>
          <input type="text" name="smtp_host"
                 value="<?php echo htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com'); ?>"
                 placeholder="smtp.gmail.com">
          <span class="hint">Leave as smtp.gmail.com unless using Outlook or another provider</span>
        </div>
      </div>
      <div style="margin-top:20px">
        <button type="submit" class="btn btn-primary">💾 Save Email Settings</button>
      </div>
    </form>

    <hr class="separator">
    <p style="font-size:12px;color:#666;margin-bottom:12px">
      Once saved, click below to test your Gmail connection:
    </p>
    <form action="/admin/test-email" method="POST" style="display:inline">
      <button type="submit" class="btn btn-outline">🔌 Test Email Connection</button>
    </form>
  </div>

  <!-- ── COMPANY DETAILS ──────────────────────────────────────────── -->
  <div class="card">
    <h2>🏢 Company Details (for Tax Invoices)</h2>
    <p class="desc">
      These details appear on the tax invoices that are attached to order confirmation emails. Fill in your full legal company name, PIN, and address as they should appear on invoices.
    </p>

    <form action="/admin/settings" method="POST">
      <input type="hidden" name="action" value="company">
      <div class="field-group">
        <div class="field">
          <label>Company Name</label>
          <input type="text" name="company_name"
                 value="<?php echo htmlspecialchars($settings['company_name'] ?? 'OmniSpace 3D Events Ltd'); ?>">
        </div>
        <div class="field">
          <label>PIN / Tax Registration Number</label>
          <input type="text" name="company_pin"
                 value="<?php echo htmlspecialchars($settings['company_pin'] ?? ''); ?>"
                 placeholder="e.g. P051234567A">
        </div>
      </div>
      <div class="field-group">
        <div class="field">
          <label>VAT Registration Number [If different from PIN]</label>
          <input type="text" name="company_vat_no"
                 value="<?php echo htmlspecialchars($settings['company_vat_no'] ?? ''); ?>"
                 placeholder="Optional">
        </div>
        <div class="field">
          <label>Phone</label>
          <input type="text" name="company_phone"
                 value="<?php echo htmlspecialchars($settings['company_phone'] ?? '+254 204 489 504'); ?>">
        </div>
      </div>
      <div class="field-group">
        <div class="field">
          <label>WhatsApp Number [shown on invoices]</label>
          <input type="text" name="company_whatsapp"
                 value="<?php echo htmlspecialchars($settings['company_whatsapp'] ?? '+254 731 001 723'); ?>">
          <span class="hint">Displayed as WhatsApp on the invoice header</span>
        </div>
      </div>
      <div class="field-group">
        <div class="field">
          <label>Company Email (on invoices)</label>
          <input type="email" name="company_email"
                 value="<?php echo htmlspecialchars($settings['company_email'] ?? 'solarandstorage@omnispace3d.com'); ?>">
        </div>
        <div class="field">
          <label>Website</label>
          <input type="text" name="company_website"
                 value="<?php echo htmlspecialchars($settings['company_website'] ?? 'www.omnispace3d.com'); ?>">
        </div>
      </div>
      
      <div class="field" style="margin-bottom:20px">
        <label>Company Address (as shown on invoices)</label>
        <textarea name="company_address" placeholder="e.g. P.O. Box 12345, Nairobi, Kenya"><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
      </div>

      <div class="field" style="margin-bottom:20px">
        <label>Invoice Payment Note (shown at bottom of each invoice)</label>
        <textarea name="invoice_payment_note" placeholder="e.g. Please make payment within 14 days quoting your Invoice Number..."><?php echo htmlspecialchars($settings['invoice_payment_note'] ?? ''); ?></textarea>
      </div>

      <div class="field">
        <label>Terms & Conditions (printed on each invoice — one clause per line)</label>
        <textarea name="invoice_terms" placeholder="1. All prices are exclusive of VAT unless stated otherwise.&#10;2. Payment is due within 14 days of invoice date."><?php echo htmlspecialchars($settings['invoice_terms'] ?? ''); ?></textarea>
        <span class="hint">Each line becomes a numbered clause on the invoice. Leave blank to use the default T&C.</span>
      </div>

      <div style="margin-top:20px">
        <button type="submit" class="btn btn-primary">💾 Save Company Details</button>
      </div>
    </form>
  </div>

  <!-- ── CATALOG PASSWORDS ─────────────────────── -->
  <div class="card">
    <h2>🔒 Catalog Access Password</h2>
    <p class="desc">Visitors must enter this password to view the exhibitor catalog. Share it with your exhibitors when you send out event communications. Leave blank to make the catalog publicly accessible.</p>
    <form method="POST" action="/admin/settings">
      <input type="hidden" name="action" value="passwords">
      <div class="field-group">
        <div class="field">
          <label>Exhibitor Password</label>
          <input type="text" name="catalog_password_solarandstorage"
                 value="<?php echo htmlspecialchars($settings['catalog_password_solarandstorage'] ?? 'ssl2026'); ?>">
          <span class="hint">Share with all exhibitors. Current: <strong style="color:#0A9696"><?php echo htmlspecialchars($settings['catalog_password_solarandstorage'] ?? 'ssl2026'); ?></strong></span>
        </div>
        <div class="field">
          <label>Demo / Visitor Password</label>
          <input type="text" name="catalog_password_demo"
                 value="<?php echo htmlspecialchars($settings['catalog_password_demo'] ?? ''); ?>"
                 placeholder="e.g. DWTC2026">
          <span class="hint">For prospects & clients only — separate from exhibitor password. Leave blank to disable.</span>
        </div>
      </div>
      <p style="font-size:11px;color:#888;margin-top:15px">Catalog URL: <code>/solarandstorage</code> · Both passwords grant the same view-only access to the catalog.</p>
      <div style="margin-top:20px">
        <button type="submit" class="btn btn-primary">💾 Save Passwords</button>
      </div>
    </form>
  </div>
</div>
