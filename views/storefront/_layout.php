<?php
// _layout.php — storefront page wrapper
// Variables set by the page before including this:
//   $page_title   — <title> text
//   $page_css     — page-specific <style> block (raw HTML)
//   $body_class   — <body> class (default: 'storefront-portal')
//   $header_title — header h1 text
//   $header_right — header right-side HTML
//   $header_event_logo — optional event logo src
//   $header_center — array with 'title' and optional 'subtitle'
//   $is_catalog_page — if true, hides "Back to Catalog" link
//   $page_content — body HTML (or use ob_start/ob_get_clean pattern)

$page_title   = $page_title   ?? 'OmniShop';
$page_css     = $page_css     ?? '';
$body_class   = $body_class   ?? 'storefront-portal';
$page_content = $page_content ?? '';
$show_header  = $show_header  ?? true;
$header_title = $header_title ?? 'OmniShop';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include __DIR__ . '/_head.php'; ?>
<?php echo $page_css; ?>
</head>
<body class="<?php echo htmlspecialchars($body_class); ?>">
<?php if ($show_header): ?>
<?php include __DIR__ . '/_header.php'; ?>
<?php endif; ?>
<?php echo $page_content; ?>
<?php include __DIR__ . '/_footer.php'; ?>
<?php include __DIR__ . '/_toast.php'; ?>
</body>
</html>
