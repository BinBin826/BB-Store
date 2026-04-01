<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Nội Thất Sài Gòn - Đẳng Cấp Trong Từng Không Gian';

// Lấy sản phẩm nổi bật (trang chủ)
$sp_query = "
    SELECT sp.*, ls.ten_loai, tk.so_luong as ton_kho, tk.gia_von,
           COALESCE(gb.tl_loi_nhuan, sp.tl_loi_nhuan) as tl_loi_nhuan
    FROM san_pham sp
    JOIN loai_sp ls ON sp.ma_loai = ls.ma_loai
    LEFT JOIN ton_kho tk ON sp.ma_sp = tk.ma_sp
    LEFT JOIN gia_ban gb ON sp.ma_sp = gb.ma_sp
    WHERE sp.trang_thai = 1 AND ls.trang_thai = 1
    ORDER BY sp.created_at DESC LIMIT 8
";
$san_phams = $conn->query($sp_query);

// Loại sản phẩm
$loai_result = $conn->query("SELECT * FROM loai_sp WHERE trang_thai=1 ORDER BY ma_loai");
$cat_icons = ['🛋️','🛏️','🍽️','💼','🍳'];

include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-pattern"></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-eyebrow">Bộ Sưu Tập 2025</div>
            <h1>Nội Thất <em>Tinh Tế</em><br>Cho Mọi Không Gian</h1>
            <p>Chúng tôi mang đến những sản phẩm nội thất được chọn lọc kỹ lưỡng từ gỗ tự nhiên và vật liệu cao cấp, tạo nên không gian sống sang trọng và ấm áp.</p>
            <div class="hero-actions">
                <a href="<?= SITE_URL ?>/products.php" class="btn-primary">Khám Phá Ngay →</a>
                <a href="<?= SITE_URL ?>/search.php" class="btn-outline">Tìm Kiếm Sản Phẩm</a>
            </div>
        </div>
    </div>
</section>

<!-- DANH MỤC -->
<section class="py-section" style="padding:60px 0 40px;">
    <div class="container">
        <div class="section-header">
            <h2>Khám Phá Theo Không Gian</h2>
            <a href="<?= SITE_URL ?>/products.php" class="view-all">Xem tất cả</a>
        </div>
        <div class="cat-grid">
            <?php
            $i = 0;
            while ($loai = $loai_result->fetch_assoc()):
                $icon = $cat_icons[$i % count($cat_icons)];
            ?>
            <a href="<?= SITE_URL ?>/products.php?loai=<?= $loai['ma_loai'] ?>" class="cat-card">
                <span class="cat-icon"><?= $icon ?></span>
                <span class="cat-name"><?= htmlspecialchars($loai['ten_loai']) ?></span>
            </a>
            <?php $i++; endwhile; ?>
        </div>
    </div>
</section>

<!-- SẢN PHẨM NỔI BẬT -->
<section class="py-section" style="background: var(--white);">
    <div class="container">
        <div class="section-header">
            <h2>Sản Phẩm Mới Nhất</h2>
            <a href="<?= SITE_URL ?>/products.php" class="view-all">Xem tất cả</a>
        </div>
        <div class="product-grid">
            <?php while ($sp = $san_phams->fetch_assoc()):
                $gia_ban = $sp['gia_von'] * (1 + $sp['tl_loi_nhuan'] / 100);
                $ton = (int)$sp['ton_kho'];
            ?>
            <div class="product-card">
                <div class="product-img">
                    <?php if ($sp['hinh'] && file_exists(UPLOAD_PATH . $sp['hinh'])): ?>
                        <img src="<?= UPLOAD_URL . $sp['hinh'] ?>" alt="<?= htmlspecialchars($sp['ten_sp']) ?>">
                    <?php else: ?>
                        <span class="no-img">🛋️</span>
                    <?php endif; ?>
                    <?php if ($ton <= 0): ?>
                        <span class="out-of-stock-badge">Hết hàng</span>
                    <?php elseif ($ton <= 3): ?>
                        <span class="low-stock-badge">Sắp hết</span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <div class="product-category"><?= htmlspecialchars($sp['ten_loai']) ?></div>
                    <div class="product-name"><?= htmlspecialchars($sp['ten_sp']) ?></div>
                    <div class="product-price"><?= formatMoney($gia_ban) ?></div>
                    <div class="product-actions">
                        <?php if ($ton > 0): ?>
                        <form method="POST" action="<?= SITE_URL ?>/cart.php" style="flex:1">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="ma_sp" value="<?= $sp['ma_sp'] ?>">
                            <button type="submit" class="btn-cart">+ Giỏ Hàng</button>
                        </form>
                        <?php else: ?>
                        <button class="btn-cart" disabled style="flex:1">Hết Hàng</button>
                        <?php endif; ?>
                        <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $sp['ma_sp'] ?>" class="btn-detail" title="Chi tiết">🔍</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="py-section">
    <div class="container">
        <div class="grid-3" style="gap:32px;">
            <div class="card text-center">
                <div style="font-size:2.5rem;margin-bottom:12px;">🚚</div>
                <h3>Giao Hàng Toàn Quốc</h3>
                <p class="text-muted mt-8">Miễn phí vận chuyển cho đơn hàng trên 5 triệu đồng trong TP.HCM.</p>
            </div>
            <div class="card text-center">
                <div style="font-size:2.5rem;margin-bottom:12px;">🔧</div>
                <h3>Lắp Đặt Chuyên Nghiệp</h3>
                <p class="text-muted mt-8">Đội ngũ kỹ thuật viên tận tâm, lắp đặt tận nhà, bảo hành 2 năm.</p>
            </div>
            <div class="card text-center">
                <div style="font-size:2.5rem;margin-bottom:12px;">✅</div>
                <h3>Chất Lượng Cam Kết</h3>
                <p class="text-muted mt-8">100% vật liệu có nguồn gốc rõ ràng, kiểm định chất lượng nghiêm ngặt.</p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
