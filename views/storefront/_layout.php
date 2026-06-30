<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo $head_content ?? ''; ?>
</head>
<body class="<?php echo $body_class ?? 'storefront-portal'; ?>">
<?php include __DIR__ . '/_header.php'; ?>
<?php echo $page_content ?? ''; ?>
<?php include __DIR__ . '/_footer.php'; ?>
<?php include __DIR__ . '/_toast.php'; ?>
</body>
</html>
