<?php

class Auth {
    public static function login($username, $role, $id = null) {
        $_SESSION['admin_user'] = [
            'id' => $id,
            'username' => $username,
            'role' => $role
        ];
    }

    public static function logout() {
        unset($_SESSION['admin_user']);
    }

    public static function check() {
        return isset($_SESSION['admin_user']);
    }

    public static function user() {
        return $_SESSION['admin_user'] ?? null;
    }

    public static function requireAdmin() {
        if (!self::check()) {
            header("Location: /admin/login");
            exit;
        }
    }

    public static function requireSuperAdmin() {
        self::requireAdmin();
        if (!self::isSuperAdmin()) {
            header("Location: /admin/orders?error=Unauthorized");
            exit;
        }
    }

    public static function isSuperAdmin() {
        return (self::user()['role'] ?? '') === 'super_admin';
    }

    public static function isProductEditor() {
        return (self::user()['role'] ?? '') === 'product_editor';
    }

    public static function canManageOrders() {
        $role = self::user()['role'] ?? '';

        return in_array($role, ['super_admin', 'order_manager'], true);
    }

    public static function defaultLandingPath() {
        if (self::isProductEditor()) {
            return '/admin/products';
        }

        return '/admin/orders';
    }
}
