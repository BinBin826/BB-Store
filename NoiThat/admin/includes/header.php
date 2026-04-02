<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$base = SITE_URL;
$cur = $_SERVER['REQUEST_URI'];
function isActive($path){ return strpos($_SERVER['REQUEST_URI'],$path)!==false?' class="active"':''; }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= $page_title ?? 'Quản Trị' ?> | Nội Thất SGN</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            Nội<span>Thất</span> SGN
            <small>Quản Trị Hệ Thống</small>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">Tổng Quan</div>
            <a href="<?= $base ?>/admin/index.php"<?= isActive('/admin/index') ?>><span class="nav-icon">📊</span> Dashboard</a>

            <div class="nav-section">Danh Mục</div>
            <a href="<?= $base ?>/admin/categories.php"<?= isActive('categories') ?>><span class="nav-icon">📂</span> Loại Sản Phẩm</a>
            <a href="<?= $base ?>/admin/products.php"<?= isActive('products') ?>><span class="nav-icon">🛋️</span> Sản Phẩm</a>

            <div class="nav-section">Kho Hàng</div>
            <a href="<?= $base ?>/admin/imports.php"<?= isActive('imports') ?>><span class="nav-icon">📦</span> Nhập Hàng</a>
            <a href="<?= $base ?>/admin/pricing.php"<?= isActive('pricing') ?>><span class="nav-icon">💰</span> Giá Bán</a>
            <a href="<?= $base ?>/admin/inventory.php"<?= isActive('inventory') ?>><span class="nav-icon">📋</span> Tồn Kho</a>

            <div class="nav-section">Bán Hàng</div>
            <a href="<?= $base ?>/admin/orders.php"<?= isActive('orders') ?>><span class="nav-icon">📝</span> Đơn Hàng</a>

            <div class="nav-section">Quản Trị</div>
            <a href="<?= $base ?>/admin/users.php"<?= isActive('users') ?>><span class="nav-icon">👥</span> Khách Hàng</a>
            <a href="<?= $base ?>/index.php" target="_blank"><span class="nav-icon">🌐</span> Xem Website</a>
            <a href="<?= $base ?>/admin/logout.php"><span class="nav-icon">🚪</span> Đăng Xuất</a>
        </nav>
        <div class="sidebar-footer">Nội Thất SGN © 2025</div>
    </aside>

    <!-- MAIN -->
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-title"><?= $page_title ?? 'Quản Trị' ?></div>
            <div class="topbar-actions">
                <div class="topbar-admin">
                    <div class="avatar"><?= strtoupper(mb_substr($admin_name,0,1,'UTF-8')) ?></div>
                    <span><?= htmlspecialchars($admin_name) ?></span>
                </div>
            </div>
        </header>
        <div class="admin-content">
