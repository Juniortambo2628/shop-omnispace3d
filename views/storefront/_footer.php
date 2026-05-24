<footer class="footer">
    <img src="/static/images/omnispace-logo-white.png" alt="OmniSpace">
    <p>Official Furnishing &amp; Services Partner</p>
    <?php if (!empty($config)): ?>
    <p><?php echo htmlspecialchars($config["contact_email"] ?? ''); ?> | <?php echo htmlspecialchars($config["contact_phone"] ?? ''); ?></p>
    <p><?php echo htmlspecialchars($config["website"] ?? ''); ?></p>
    <?php endif; ?>
    <p class="tagline">&copy; <?php echo date('Y'); ?> OmniSpace 3D Events Ltd. All rights reserved.</p>
</footer>
