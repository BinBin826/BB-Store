<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$per_page = 12;
$page     = max(1, (int)($_GET['page'] ?? 1));
$loai     = (int)($_GET['loai'] ?? 0);
$offset   = ($page - 1) * $per_page;

$where = "sp.trang_thai=1 AND ls.trang_thai=1";
if ($loai > 0) $where .= " AND sp.ma_loai=$loai";

$count_r = $conn->query("SELECT COUNT(*) FROM san_pham sp JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai WHERE $where");
$total   = (int)$count_r->fetch_row()[0];

$san_phams = $conn->query("
    SELECT sp.*, ls.ten_loai, tk.so_luong as ton_kho, tk.gia_von,
           COALESCE(gb.tl_loi_nhuan, sp.tl_loi_nhuan) as tl_loi_nhuan
    FROM san_pham sp
    JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai
    LEFT JOIN ton_kho tk ON sp.ma_sp=tk.ma_sp
    LEFT JOIN gia_ban gb ON sp.ma_sp=gb.ma_sp
    WHERE $where ORDER BY sp.created_at DESC
    LIMIT $per_page OFFSET $offset
");

$loai_info = null;
if ($loai > 0) {
    $r = $conn->query("SELECT * FROM loai_sp WHERE ma_loai=$loai");
    $loai_info = $r->fetch_assoc();
}

$url_pattern = SITE_URL . '/products.php?' . ($loai > 0 ? "loai=$loai&" : '') . 'page=%d';
$page_title  = ($loai_info ? $loai_info['ten_loai'] . ' - ' : '') . 'Sản Phẩm | Nội Thất SGN';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><?= $loai_info ? htmlspecialchars($loai_info['ten_loai']) : 'Tất Cả Sản Phẩm' ?></h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Trang chủ</a>
            <span>/</span>
            <?php if ($loai_info): ?>
            <a href="<?= SITE_URL ?>/products.php">Sản phẩm</a>
            <span>/</span>
            <span><?= htmlspecialchars($loai_info['ten_loai']) ?></span>
            <?php else: ?>
            <span>Sản phẩm</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="py-section" style="padding:40px 0 80px;">
    <div class="container">

        <!-- Filter categories -->
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:28px;">
            <a href="<?= SITE_URL ?>/products.php"
               class="page-btn <?= $loai===0?'active':'' ?>"
               style="padding:0 16px;min-width:auto;width:auto;">Tất Cả</a>
            <?php
            $cats = $conn->query("SELECT * FROM loai_sp WHERE trang_thai=1 ORDER BY ma_loai");
            while ($cat = $cats->fetch_assoc()):
            ?>
            <a href="<?= SITE_URL ?>/products.php?loai=<?= $cat['ma_loai'] ?>"
               class="page-btn <?= $loai==$cat['ma_loai']?'active':'' ?>"
               style="padding:0 16px;min-width:auto;width:auto;">
               <?= htmlspecialchars($cat['ten_loai']) ?>
            </a>
            <?php endwhile; ?>
        </div>

        <p class="text-muted mb-24" style="font-size:0.88rem;">
            Hiển thị <?= min($offset+1,$total) ?>–<?= min($offset+$per_page,$total) ?> / <?= $total ?> sản phẩm
        </p>

        <?php if ($total === 0): ?>
        <div class="empty-state">
            <div class="empty-icon">🛋️</div>
            <h3>Không có sản phẩm nào</h3>
            <p>Chưa có sản phẩm trong danh mục này.</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn-primary">Xem tất cả sản phẩm</a>
        </div>
        <?php else: ?>
        <div class="product-grid">
            <?php while ($sp = $san_phams->fetch_assoc()):
                $gia_ban = ($sp['gia_von'] ?? 0) * (1 + ($sp['tl_loi_nhuan'] ?? 20) / 100);
                $ton     = (int)($sp['ton_kho'] ?? 0);
            ?>
            <div class="product-card">
                <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $sp['ma_sp'] ?>" class="product-img">
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
                </a>
                <div class="product-info">
                    <div class="product-category"><?= htmlspecialchars($sp['ten_loai']) ?></div>
                    <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $sp['ma_sp'] ?>" class="product-name"><?= htmlspecialchars($sp['ten_sp']) ?></a>
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
                        <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $sp['ma_sp'] ?>" class="btn-detail">🔍</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?= paginate($total, $page, $per_page, $url_pattern) ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
