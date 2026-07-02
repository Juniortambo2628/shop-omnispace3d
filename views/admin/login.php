<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - OmniShop</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/static/css/tokens.css">
    <link rel="stylesheet" href="/static/css/base.css">
    <link rel="stylesheet" href="/static/css/admin-login.css">
</head>
<body>
<div class="card">
    <img src="/static/images/omnispace-logo.jpg" alt="OmniSpace">
    <h1>OmniShop Admin</h1>
    <p>Sign in with your email and password</p>
    <?php if (isset($error) && $error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="POST" action="/admin/login">
        <input type="email" name="username" placeholder="Email address" required autofocus>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign In</button>
    </form>
</div>
</body>
</html>
