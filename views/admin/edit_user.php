<?php $active_page = 'users'; ?>
<style>
    .checkbox-row{display:flex;align-items:center;gap:8px}
    .checkbox-row input{width:auto}
    .checkbox-row label{margin:0;text-transform:none;font-size:13px;font-weight:600;color:#374151}
</style>

<div class="container" style="max-width:800px">
  <?php
  $email = $user['email'] ?? $user['username'] ?? '';
  $role = $user['role'] ?? 'order_manager';
  ?>

  <?php include __DIR__ . '/_header.php'; ?>
  <?php include __DIR__ . '/_admin_hero.php'; ?>

  <?php include __DIR__ . '/_flash.php'; ?>

  <?php if (! empty($error)): ?>
  <div class="alert alert-error">⚠ <?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div class="form-card form-card--narrow">
    <form method="POST" action="/admin/users/<?php echo (int) ($user['id'] ?? 0); ?>/edit">
      <div class="form-group">
        <label>Display Name</label>
        <input type="text" name="display_name" value="<?php echo htmlspecialchars($user['display_name'] ?? ''); ?>" required>
      </div>

      <div class="form-group">
        <label>Email (login)</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
      </div>

      <div class="form-group">
        <label>Role</label>
        <select name="role" <?php if (! empty($is_self)) echo 'disabled'; ?>>
          <option value="order_manager" <?php if ($role === 'order_manager') echo 'selected'; ?>>Order Manager</option>
          <option value="product_editor" <?php if ($role === 'product_editor') echo 'selected'; ?>>Product Editor</option>
          <option value="super_admin" <?php if ($role === 'super_admin') echo 'selected'; ?>>Super Admin</option>
        </select>
        <?php if (! empty($is_self)): ?>
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
        <div class="hint">You cannot change your own role.</div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <div class="checkbox-row">
          <input type="checkbox" name="active" id="user_active" value="1"
                 <?php if (! empty($user['active'])) echo 'checked'; ?>
                 <?php if (! empty($is_self)) echo 'disabled'; ?>>
          <label for="user_active">Account is active</label>
        </div>
        <?php if (! empty($is_self)): ?>
        <input type="hidden" name="active" value="1">
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label>Reset Password</label>
        <input type="text" name="new_password" placeholder="Leave blank to keep current password" autocomplete="new-password">
        <div class="hint">Minimum 8 characters. Share the new password securely with the user.</div>
      </div>

      <div class="footer-actions">
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
        <a href="/admin/users" class="btn btn-outline">Cancel</a>
      </div>
    </form>
  </div>
</div>
