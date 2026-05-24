<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - OmniShop</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', Arial, sans-serif; background: linear-gradient(135deg, #0A9696 0%, #066 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); padding: 48px 40px; width: 400px; max-width: 90vw; text-align: center; }
        .card img { height: 48px; margin-bottom: 24px; }
        .card h1 { font-size: 22px; font-weight: 700; color: #333; margin-bottom: 8px; }
        .card p { font-size: 13px; color: #888; margin-bottom: 28px; }
        .error { background: #FEE2E2; color: #991B1B; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 16px; }
        input[type="email"], input[type="password"] { width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; font-family: inherit; margin-bottom: 16px; }
        input[type="email"]:focus, input[type="password"]:focus { outline: none; border-color: #0A9696; box-shadow: 0 0 0 3px rgba(10,150,150,0.15); }
        button { width: 100%; padding: 14px; background: #0A9696; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; font-family: inherit; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #088080; }
    </style>
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
