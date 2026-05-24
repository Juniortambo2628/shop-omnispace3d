<style>
    .two-col{display:grid;grid-template-columns:1fr 380px;gap:28px;align-items:start}
    .card{background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.07);padding:28px}
    h2{font-size:18px;font-weight:700;color:#1a1a1a;margin-bottom:20px}
    table{width:100%;border-collapse:collapse}
    thead{background:#f9fafb}
    th{padding:10px 14px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid #e5e7eb}
    td{padding:12px 14px;font-size:13px;border-bottom:1px solid #f3f4f6;vertical-align:middle}
    .badge{display:inline-block;padding:3px 10px;border-radius:10px;font-size:11px;font-weight:600}
    .badge-super{background:#dcfce7;color:#166534}
    .badge-editor{background:#dbeafe;color:#1d4ed8}
    .badge-order{background:#f3f4f6;color:#6b7280}
    .form-group{margin-bottom:14px}
    label{display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:4px;text-transform:uppercase;letter-spacing:.4px}
    input,select{width:100%}
    input:focus,select:focus{outline:none;border-color:#0A9696;box-shadow:0 0 0 3px rgba(10,150,150,.1)}
    .hint{font-size:11px;color:#9ca3af;margin-top:3px}
    .role-info{background:#f0fdfd;border-radius:8px;padding:16px;margin-top:20px}
    .role-info h3{font-size:13px;font-weight:700;color:#0A9696;margin-bottom:10px}
    .role-row{margin-bottom:8px;font-size:12px;color:#555}
</style>

<div class="container">
  <?php include __DIR__ . '/_header.php'; ?>

  <?php if (isset($_GET['added'])): ?>
  <div class="alert alert-success">✓ New user added successfully. Make sure to share their password securely.</div>
  <?php endif; ?>
  <?php if (isset($_GET['saved'])): ?>
  <div class="alert alert-success">✓ Changes saved successfully.</div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
  <div class="alert alert-error">⚠ <?php echo htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>
  <?php if (isset($error)): ?>
  <div class="alert alert-error">⚠ <?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div class="two-col">
    <!-- Current users table -->
    <div class="card">
      <h2>Current Users (<?php echo count($users); ?>)</h2>
      <table style="margin-bottom:20px">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email / Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td style="font-weight:600"><?php echo htmlspecialchars($u['display_name']); ?></td>
            <td style="color:#555;font-size:12px"><?php echo htmlspecialchars($u['username']); ?></td>
            <td>
              <?php if ($u['role'] == 'super_admin'): ?>
              <span class="badge badge-super">Super Admin</span>
              <?php elseif ($u['role'] == 'product_editor'): ?>
              <span class="badge badge-editor">Product Editor</span>
              <?php else: ?>
              <span class="badge badge-order">Order Manager</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($u['active']): ?>
              <span style="color:#16a34a;font-size:12px;font-weight:600">● Active</span>
              <?php else: ?>
              <span style="color:#9ca3af;font-size:12px">○ Inactive</span>
              <?php endif; ?>
            </td>
            <td style="white-space:nowrap">
              <a href="/admin/users/<?php echo (int) $u['id']; ?>/edit" class="btn btn-outline btn-sm">✏️ Edit</a>
              <?php if ((int) $u['id'] !== (int) ($current_user_id ?? 0)): ?>
              <form method="POST" action="/admin/users/<?php echo (int) $u['id']; ?>/toggle-active" style="display:inline;margin-left:6px"
                    onsubmit="return confirm('<?php echo $u['active'] ? 'Deactivate' : 'Activate'; ?> this user?');">
                <button type="submit" class="btn btn-sm" style="background:<?php echo $u['active'] ? '#fee2e2' : '#dcfce7'; ?>;color:<?php echo $u['active'] ? '#991b1b' : '#166534'; ?>">
                  <?php echo $u['active'] ? 'Deactivate' : 'Activate'; ?>
                </button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="role-info">
        <h3>Role Permissions</h3>
        <div class="role-row"><strong>Super Admin</strong> — Full access: orders, products (add/edit/delete/pricing), images, users, settings</div>
        <div class="role-row"><strong>Product Editor</strong> — Can edit product variants, colours, descriptions &amp; upload images. Cannot add/delete products or change prices.</div>
        <div class="role-row"><strong>Order Manager</strong> — Orders and packing lists only. No product or settings access.</div>
      </div>
    </div>

    <!-- Add user form -->
    <div class="card">
      <h2>Add New User</h2>
      <form method="POST" action="/admin/users">
        <div class="form-group">
          <label>Display Name</label>
          <input type="text" name="display_name" placeholder="e.g. Jane Smith" required>
        </div>
        <div class="form-group">
          <label>Email (used as login)</label>
          <input type="email" name="username" placeholder="jane@company.com" required>
        </div>
        <div class="form-group">
          <label>Temporary Password</label>
          <input type="text" name="password" placeholder="Set a temporary password" required>
          <div class="hint">Share this securely. The user cannot change their password themselves.</div>
        </div>
        <div class="form-group">
          <label>Role</label>
          <select name="role" required>
            <option value="order_manager">Order Manager</option>
            <option value="product_editor">Product Editor</option>
            <option value="super_admin">Super Admin</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px">Add User</button>
      </form>
    </div>
  </div>
</div>
