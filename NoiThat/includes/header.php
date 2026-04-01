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
    
    <style>
        /* --- CSS Fix cho Dropdown Tài Khoản --- */
        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-menu-btn {
            cursor: pointer;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 5px;
            user-select: none;
        }

        .user-dropdown {
            display: none; /* Ẩn mặc định */
            position: absolute;
            top: 100%;
            right: 0;
            background-color: #ffffff;
            min-width: 200px;
            border: 1px solid #ececec;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            z-index: 9999; /* Luôn nằm trên cùng */
            border-radius: 4px;
            padding: 8px 0;
            margin-top: 5px;
        }

        /* Hiển thị khi hover (dành cho Máy tính) */
        .user-menu:hover .user-dropdown {
            display: block;
        }

        /* Lớp đệm giả để chuột di chuyển không bị mất hover */
        .user-dropdown::before {
            content: "";
            position: absolute;
            top: -10px;
            left: 0;
            width: 100%;
            height: 10px;
        }

        .user-dropdown a {
            display: block;
            padding: 10px 20px;
            color: #333;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .user-dropdown a:hover {
            background-color: #f8f9fa;
            color: #006b6b; /* Màu chủ đạo của bạn */
            padding-left: 25px;
        }

        .user-dropdown .divider {
            height: 1px;
            background: #eee;
            margin: 8px 0;
        }

        /* CSS bổ trợ cho Mobile khi nhấn vào */
        .user-menu.active .user-dropdown {
            display: block;
        }
    </style>
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
                        <a href="<?= $base ?>/auth/logout.php" style="color: #e74c3c;">Đăng xuất</a>
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

<script>
    // Xử lý click cho thiết bị di động
    document.addEventListener('DOMContentLoaded', function() {
        const userBtn = document.querySelector('.user-menu-btn');
        const userMenu = document.querySelector('.user-menu');

        if (userBtn) {
            userBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userMenu.classList.toggle('active');
            });
        }

        // Click ra ngoài thì đóng menu
        document.addEventListener('click', function() {
            if (userMenu && userMenu.classList.contains('active')) {
                userMenu.classList.remove('active');
            }
        });
    });
</script>