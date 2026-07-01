<?php
$page_title = htmlspecialchars($event['short_name']) . ' — Catalog Access';
$body_class = 'catalog-login';
$show_header = false;

ob_start();
?>

<div class="card">
    <div class="card-top">
        <div class="logo-row">
            <img class="logo-omnispace" src="/static/images/omnispace-logo.jpg" alt="OmniSpace 3D Events">
            <img class="logo-event" src="<?php echo htmlspecialchars($event['logo']); ?>" alt="<?php echo htmlspecialchars($event['short_name']); ?>">
        </div>
        <div class="event-name"><?php echo htmlspecialchars($event["name"]); ?></div>
        <div class="event-dates"><?php echo htmlspecialchars($event["dates"]); ?> &nbsp;·&nbsp; <?php echo htmlspecialchars(explode(',', $event["venue"])[0]); ?></div>
    </div>

    <div class="card-body">
        <div class="lock-icon">🔒</div>
        <h2>Catalog Access</h2>
        <p>This catalog is available to registered exhibitors only.<br>Please enter your access password to continue.</p>

        <?php if ($error): ?>
        <div class="error-box">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/<?php echo htmlspecialchars($event_slug); ?>/login">
            <label for="password">Access Password</label>
            <div class="input-wrap">
                <span class="icon">🔑</span>
                <input type="password" id="password" name="password"
                       placeholder="Enter your access password"
                       autofocus autocomplete="off" required>
                <span class="toggle-pw" onclick="togglePw()" title="Show/hide password">👁</span>
            </div>
            <button type="submit" class="btn">Access Catalog →</button>
        </form>

        <div class="help-text">
            Don't have a password? Contact us at
            <a href="mailto:<?php echo htmlspecialchars($event['contact_email']); ?>"><?php echo htmlspecialchars($event["contact_email"]); ?></a>
        </div>
    </div>
</div>

<script>
function togglePw() {
    var inp = document.getElementById('password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>
<?php
$page_content = ob_get_clean();

$page_css = '<link rel="stylesheet" href="/static/css/catalog-login.css">';

include __DIR__ . '/storefront/_layout.php';
