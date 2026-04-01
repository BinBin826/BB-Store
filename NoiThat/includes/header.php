<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
$loai_list  = getLoaiSP($conn);
$cart_count = getCartCount($conn);
$kh         = currentKH($conn);
$base       = SITE_URL;
$cur_path   = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Nội Thất Sài Gòn' ?></title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <div class="header-inner">
            <a href="<?= $base ?>/index.php" class="logo">Nội<span>Thất</span> SGN</a>

            <button class="menu-toggle" aria-label="Menu">☰</button>

            <nav class="nav-main">
                <a href="<?= $base ?>/index.php" <?= (strpos($cur_path,'index.php')!==false||$cur_path===$base.'/')?' class="active"':'' ?>>Trang Chủ</a>
                <div class="has-dropdown">
                    <a href="<?= $base ?>/products.php" <?= (strpos($cur_path,'products.php')!==false)?' class="active"':'' ?>>Sản Phẩm</a>
                    <div class="cat-dropdown">
                        <a href="<?= $base ?>/products.php">Tất Cả Sản Phẩm</a>
                        <?php
                        $loai_list->data_seek(0);
                        while ($loai = $loai_list->fetch_assoc()):
                        ?>
                        <a href="<?= $base ?>/products.php?loai=<?= $loai['ma_loai'] ?>"><?= htmlspecialchars($loai['ten_loai']) ?></a>
                        <?php endwhile; ?>
                    </div>
                </div>
                <a href="<?= $base ?>/search.php" <?= (strpos($cur_path,'search.php')!==false)?' class="active"':'' ?>>Tìm Kiếm</a>
            </nav>

            <div class="nav-actions">
                <a href="<?= $base ?>/cart.php" class="cart-btn">
                    🛒 Giỏ hàng
                    <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>

                <?php if ($kh): ?>
                <div class="user-menu">
                    <div class="user-menu-btn">
                        👤 <?= htmlspecialchars(explode(' ',$kh['ho_ten'])[0]) ?> ▾
                    </div>
                    <div class="user-dropdown">
                        <a href="<?= $base ?>/auth/profile.php">Thông tin cá nhân</a>
                        <a href="<?= $base ?>/orders.php">Đơn hàng của tôi</a>
                        <div class="divider"></div>
                        <a href="<?= $base ?>/auth/logout.php">Đăng xuất</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="user-menu">
                    <div class="user-menu-btn">👤 Tài khoản ▾</div>
                    <div class="user-dropdown">
                        <a href="<?= $base ?>/auth/login.php">Đăng nhập</a>
                        <a href="<?= $base ?>/auth/register.php">Đăng ký</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
