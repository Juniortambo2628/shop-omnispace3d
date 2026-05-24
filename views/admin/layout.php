<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'OmniShop Admin'; ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/static/images/square-logos/Omnispace3D-Teal-bg.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/static/css/admin-shared.css">
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <style>
        /* ── GLOBAL ADMIN STYLES (PRESERVING EXACT DESIGN) ── */
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Montserrat',Arial,sans-serif;background:#f5f7fa;color:#333;min-height:100vh}
        
        .topbar{background:linear-gradient(135deg,#0A9696,#088080);color:#fff;padding:0 28px;height:56px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:1000}
        .topbar h1{font-size:17px;font-weight:700}
        .topbar .topbar-link{color:#fff;text-decoration:none;font-size:13px;opacity:.85}
        .topbar .topbar-link:hover{opacity:1}

        .nav{background:#fff;border-bottom:1px solid #e5e7eb;padding:0 28px;display:flex;gap:4px;overflow-x:auto;position:sticky;top:56px;z-index:999}
        .nav a{display:inline-block;padding:14px 16px;font-size:13px;font-weight:600;color:#666;text-decoration:none;border-bottom:3px solid transparent;white-space:nowrap}
        .nav a:hover{color:#0A9696}
        .nav a.active{color:#0A9696;border-bottom-color:#0A9696}

        .container{max-width:1300px;margin:0 auto;padding:28px 24px}
        
        /* Skeleton Loading */
        @keyframes skeleton-pulse {
            0% { background-color: rgba(165, 165, 165, 0.1); }
            50% { background-color: rgba(165, 165, 165, 0.3); }
            100% { background-color: rgba(165, 165, 165, 0.1); }
        }
        .skeleton {
            animation: skeleton-pulse 1.5s infinite ease-in-out;
            border-radius: 4px;
            display: inline-block;
            min-height: 1em;
            width: 100%;
        }

        /* Progress Bar for HTMX — see admin-shared.css */

        /* Reusable UI Components (Preserved) */
        .btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:7px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;text-decoration:none;border:none;transition:all .2s}
        .btn-primary{background:#0A9696;color:#fff}
        .btn-outline{background:#fff;color:#0A9696;border:1.5px solid #0A9696}
        .btn-sm{padding:6px 12px;font-size:12px}
        
        /* Reused in multiple pages */
        .alert{padding:12px 18px;border-radius:8px;margin-bottom:20px;font-size:13px;display:flex;align-items:center;gap:10px}
        .alert-success{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
        .alert-error{background:#fee2e2;color:#991b1b;border:1px solid #fecaca}
        .btn-danger{background:#dc2626;color:#fff;padding:6px 12px;font-size:12px}

        /* SortableJS */
        .sortable-ghost{opacity:0.4;background:#D6F0EF}
        .sortable-chosen{box-shadow:0 4px 14px rgba(10,150,150,.2)}
        .drag-handle{cursor:grab;color:#ccc;font-size:16px;padding:0 6px}
        .drag-handle:active{cursor:grabbing}

        /* Skeleton Loading */
        .skeleton{background:#eee;background:linear-gradient(110deg,#ececec 8%,#f5f5f5 18%,#ececec 33%);border-radius:5px;background-size:200% 100%;animation:1.5s shine linear infinite}
        @keyframes shine{to{background-position-x:-200%}}

        /* Global Table Styles */
        .table-wrap{background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);overflow:hidden;margin-bottom:24px}
        table{width:100%;border-collapse:collapse;font-size:13px}
        thead th{background:#0A9696;color:#fff;padding:12px 14px;text-align:left;font-weight:600;text-transform:uppercase;font-size:11px;letter-spacing:.5px}
        tbody td{padding:12px 14px;border-bottom:1px solid #f0f0f0;vertical-align:middle}
        tbody tr:hover td{background:#f9fdfd}
        tbody tr:last-child td{border-bottom:none}

        /* Alpine.js Cloak */
        [x-cloak]{display:none!important}
    </style>
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
