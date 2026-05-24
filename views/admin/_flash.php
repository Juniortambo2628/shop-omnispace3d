<?php
$flash_messages = $flash_messages ?? [];

if (isset($_GET['added'])) {
    $flash_messages[] = ['type' => 'success', 'text' => '✓ Product added successfully.'];
}
if (isset($_GET['saved'])) {
    $flash_messages[] = ['type' => 'success', 'text' => '✓ Saved successfully.'];
}
if (isset($_GET['deleted'])) {
    $flash_messages[] = ['type' => 'success', 'text' => '✓ Product removed.'];
}
if (isset($_GET['error'])) {
    $flash_messages[] = ['type' => 'error', 'text' => '⚠ ' . htmlspecialchars((string) $_GET['error'])];
}

foreach ($flash_messages as $flash):
    $class = ($flash['type'] ?? 'success') === 'error' ? 'alert-error' : 'alert-success';
?>
<div class="alert <?php echo $class; ?> admin-reveal"><?php echo $flash['text']; ?></div>
<?php endforeach; ?>
