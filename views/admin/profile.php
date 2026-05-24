<?php $active_page = 'profile'; ?>

<div class="container">
  <div class="profile-hero">
    <span class="profile-hero__avatar" aria-hidden="true"><?php echo htmlspecialchars($profile_initials ?? 'A'); ?></span>
    <div>
      <div class="profile-hero__title"><?php echo htmlspecialchars($profile['display_name'] ?? ''); ?></div>
      <div class="profile-hero__meta"><?php echo htmlspecialchars($profile['email'] ?? ''); ?></div>
      <span class="profile-role-badge"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $profile['role'] ?? 'admin'))); ?></span>
    </div>
  </div>

  <?php if (! empty($profile_error)): ?>
    <div class="alert alert-error">⚠ <?php echo htmlspecialchars($profile_error); ?></div>
  <?php endif; ?>
  <?php if (! empty($_GET['saved'])): ?>
    <div class="alert alert-success">✓ Profile updated successfully.</div>
  <?php endif; ?>
  <?php if (! empty($_GET['password_saved'])): ?>
    <div class="alert alert-success">✓ Password changed successfully.</div>
  <?php endif; ?>

  <?php if (! empty($profile_readonly)): ?>
    <div class="profile-card">
      <h2>👤 Account</h2>
      <p class="desc">You are signed in with a legacy admin account. Profile changes are only available for database user accounts.</p>
      <div class="profile-field-group">
        <div class="profile-field">
          <label>Login</label>
          <input type="text" value="<?php echo htmlspecialchars($current_username ?? ''); ?>" disabled>
        </div>
        <div class="profile-field">
          <label>Role</label>
          <input type="text" value="<?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $current_role ?? 'admin'))); ?>" disabled>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="profile-card">
      <h2>👤 Account Details</h2>
      <p class="desc">Update how your name and email appear across the admin. Your email is also used to sign in.</p>

      <form method="POST" action="/admin/profile">
        <input type="hidden" name="action" value="profile">
        <div class="profile-field-group">
          <div class="profile-field">
            <label>Display Name</label>
            <input type="text" name="display_name" required
                   value="<?php echo htmlspecialchars($profile['display_name'] ?? ''); ?>"
                   placeholder="Your full name">
            <span class="hint">Shown in the admin header and account menu</span>
          </div>
          <div class="profile-field">
            <label>Email Address</label>
            <input type="email" name="email" required
                   value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>"
                   placeholder="you@company.com">
            <span class="hint">Used for login and account identification</span>
          </div>
        </div>
        <div class="profile-field-group">
          <div class="profile-field">
            <label>Role</label>
            <input type="text"
                   value="<?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $profile['role'] ?? 'admin'))); ?>"
                   disabled>
            <span class="hint">Contact a super admin if your role needs to change</span>
          </div>
          <div class="profile-field">
            <label>Member Since</label>
            <input type="text"
                   value="<?php echo ! empty($profile['created_at']) ? htmlspecialchars(date('j M Y', strtotime($profile['created_at']))) : '—'; ?>"
                   disabled>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">💾 Save Profile</button>
      </form>
    </div>

    <div class="profile-card">
      <h2>🔒 Change Password</h2>
      <p class="desc">Choose a strong password with at least 8 characters. You will stay signed in after updating.</p>

      <form method="POST" action="/admin/profile">
        <input type="hidden" name="action" value="password">
        <div class="profile-field-group">
          <div class="profile-field">
            <label>Current Password</label>
            <input type="password" name="current_password" required autocomplete="current-password">
          </div>
          <div class="profile-field">
            <label>New Password</label>
            <input type="password" name="new_password" required minlength="8" autocomplete="new-password">
            <span class="hint">Minimum 8 characters</span>
          </div>
        </div>
        <div class="profile-field-group">
          <div class="profile-field">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password">
          </div>
        </div>
        <button type="submit" class="btn btn-primary">🔑 Update Password</button>
      </form>
    </div>
  <?php endif; ?>
</div>
