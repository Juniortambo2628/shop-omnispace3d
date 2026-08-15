<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'OmniShop Admin'; ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/static/images/square-logos/Omnispace3D-Teal-bg.png">
    <link rel="stylesheet" href="/static/css/tokens.css">
    <link rel="stylesheet" href="/static/css/base.css">
    <link rel="stylesheet" href="/static/css/admin.css">
    <link rel="stylesheet" href="/static/css/admin-shared.css">
    <link rel="stylesheet" href="/static/css/orders.css">
    <link rel="stylesheet" href="/static/css/stock.css">
    <link rel="stylesheet" href="/static/css/images.css">
    <link rel="stylesheet" href="/static/css/packing.css">
    <link rel="stylesheet" href="/static/css/settings.css">
    <link rel="stylesheet" href="/static/css/users.css">
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</head>
<body hx-boost="false">

    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px">
            <img src="/static/images/omnispace-logo-white.png" alt="" style="height:24px">
            <h1 style="font-size:18px;letter-spacing:-0.2px">OmniShop Admin</h1>
        </div>
        <div style="display:flex;gap:24px;align-items:center;font-size:13px">
            <a href="/" target="_blank" class="topbar-link" style="display:flex;align-items:center;gap:4px">View Catalog ↗</a>
            <?php
                $display_user = $current_username ?? '';
                $user_initials = strtoupper(substr($display_user ?: 'A', 0, 1));
                if ($display_user !== '' && str_contains($display_user, '@')) {
                    $local = explode('@', $display_user, 2)[0];
                    $parts = preg_split('/[._\-+]+/', $local, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    $user_initials = count($parts) >= 2
                        ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
                        : strtoupper(substr($local, 0, 1));
                }
                $role_label = ucwords(str_replace('_', ' ', $current_role ?? 'admin'));
                $is_super_admin = ($current_role ?? '') === 'super_admin';
                $is_product_editor = ($current_role ?? '') === 'product_editor';
            ?>
            <div x-data="{ open: false }" style="position:relative">
                <button type="button" @click="open = !open" @click.away="open = false" class="profile-trigger">
                    <span class="profile-avatar" aria-hidden="true"><?php echo htmlspecialchars($user_initials); ?></span>
                    <span class="profile-trigger__label"><?php echo htmlspecialchars($display_user); ?></span>
                    <svg class="profile-trigger__chevron" x-bind:style="open ? 'transform:rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak x-transition.origin.top.right class="profile-menu">
                    <div class="profile-menu__header">
                        <span class="profile-avatar" aria-hidden="true"><?php echo htmlspecialchars($user_initials); ?></span>
                        <div>
                            <div class="profile-menu__name"><?php echo htmlspecialchars($display_user); ?></div>
                            <div class="profile-menu__role"><?php echo htmlspecialchars($role_label); ?></div>
                        </div>
                    </div>
                    <a href="/admin/profile"
                       hx-get="/admin/profile"
                       hx-target="#admin-content"
                       hx-push-url="true"
                       @click="open = false"
                       class="profile-menu__link">👤 My Profile</a>
                    <?php if ($is_super_admin): ?>
                    <a href="/admin/settings"
                       hx-get="/admin/settings"
                       hx-target="#admin-content"
                       hx-push-url="true"
                       @click="open = false"
                       class="profile-menu__link">⚙️ Settings</a>
                    <?php endif; ?>
                    <a href="/admin/logout"
                       hx-boost="false"
                       @click="open = false"
                       class="profile-menu__link profile-menu__link--danger">🚪 Log Out</a>
                </div>
            </div>
        </div>
    </div>

    <div class="nav">
        <?php 
        $active_page = $active_page ?? '';
        $nav_attr = 'hx-get="%s" hx-target="#admin-content" hx-push-url="true"';
        $is_super_admin = $is_super_admin ?? (($current_role ?? '') === 'super_admin');
        $is_product_editor = $is_product_editor ?? (($current_role ?? '') === 'product_editor');
        ?>
        <?php if (! $is_product_editor): ?>
        <a href="/admin/orders" 
           <?php printf($nav_attr, "/admin/orders"); ?>
           class="<?php echo ($active_page == 'orders' || $active_page == 'dashboard') ? 'active' : ''; ?>">Orders</a>
        <?php endif; ?>

        <a href="/admin/products" 
           <?php printf($nav_attr, "/admin/products"); ?>
           class="<?php echo ($active_page == 'products') ? 'active' : ''; ?>">Products</a>

        <?php if (! $is_product_editor): ?>
        <a href="/admin/stock" 
           <?php printf($nav_attr, "/admin/stock"); ?>
           class="<?php echo ($active_page == 'stock') ? 'active' : ''; ?>">Stock Levels</a>
           
        <a href="/admin/packing/category" 
           <?php printf($nav_attr, "/admin/packing/category"); ?>
           class="<?php echo ($active_page == 'packing_category') ? 'active' : ''; ?>">Packing (Category)</a>
           
        <a href="/admin/packing/stand" 
           <?php printf($nav_attr, "/admin/packing/stand"); ?>
           class="<?php echo ($active_page == 'packing_stand') ? 'active' : ''; ?>">Packing (Stand)</a>
        <?php endif; ?>
           
        <a href="/admin/images" 
           <?php printf($nav_attr, "/admin/images"); ?>
           class="<?php echo ($active_page == 'images') ? 'active' : ''; ?>">Images</a>

        <?php if (($current_role ?? '') === 'super_admin'): ?>
        <a href="/admin/users" 
           <?php printf($nav_attr, "/admin/users"); ?>
           class="<?php echo ($active_page == 'users') ? 'active' : ''; ?>">Users</a>
           
        <a href="/admin/settings" 
           <?php printf($nav_attr, "/admin/settings"); ?>
           class="<?php echo ($active_page == 'settings') ? 'active' : ''; ?>">Settings</a>
        <?php endif; ?>
    </div>

    <main id="admin-content">
        <?php echo $content; ?>
    </main>

    <div id="loading-bar" aria-hidden="true"></div>

    <script src="/static/js/admin-shared.js"></script>
    <script src="/static/js/image-upload-compress.js"></script>
</body>
</html>
