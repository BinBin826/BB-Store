<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { redirect(SITE_URL . '/products.php'); }

$r = $conn->query("
    SELECT sp.*, ls.ten_loai, tk.so_luong as ton_kho, tk.gia_von,
           COALESCE(gb.tl_loi_nhuan, sp.tl_loi_nhuan) as tl_loi_nhuan
    FROM san_pham sp
    JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai
    LEFT JOIN ton_kho tk ON sp.ma_sp=tk.ma_sp
    LEFT JOIN gia_ban gb ON sp.ma_sp=gb.ma_sp
    WHERE sp.ma_sp=$id AND sp.trang_thai=1
");
if (!$r->num_rows) { redirect(SITE_URL . '/products.php'); }
$sp  = $r->fetch_assoc();
$gia = ($sp['gia_von'] ?? 0) * (1 + ($sp['tl_loi_nhuan'] ?? 20) / 100);
$ton = (int)($sp['ton_kho'] ?? 0);

// Handle add to cart
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cart'])) {
    if (!isLoggedIn()) {
        setFlash('warning','Vui lòng đăng nhập để thêm vào giỏ hàng.');
        redirect(SITE_URL . '/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
    $qty  = max(1, min($ton, (int)$_POST['qty']));
    $kh_id = (int)$_SESSION['kh_id'];
    // Check if already in cart
    $exist = $conn->query("SELECT id, so_luong FROM gio_hang WHERE ma_kh=$kh_id AND ma_sp=$id");
    if ($exist->num_rows) {
        $row = $exist->fetch_assoc();
        $new_qty = min($ton, $row['so_luong'] + $qty);
        $conn->query("UPDATE gio_hang SET so_luong=$new_qty WHERE id={$row['id']}");
    } else {
        $conn->query("INSERT INTO gio_hang (ma_kh,ma_sp,so_luong) VALUES ($kh_id,$id,$qty)");
    }
    setFlash('success','Đã thêm <strong>' . htmlspecialchars($sp['ten_sp']) . '</strong> vào giỏ hàng!');
    redirect(SITE_URL . '/product-detail.php?id=' . $id);
}

// Related products
$related = $conn->query("
    SELECT sp.*, ls.ten_loai, tk.gia_von, COALESCE(gb.tl_loi_nhuan, sp.tl_loi_nhuan) as tl_loi_nhuan
    FROM san_pham sp
    JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai
    LEFT JOIN ton_kho tk ON sp.ma_sp=tk.ma_sp
    LEFT JOIN gia_ban gb ON sp.ma_sp=gb.ma_sp
    WHERE sp.ma_loai={$sp['ma_loai']} AND sp.ma_sp!=$id AND sp.trang_thai=1
    LIMIT 4
");

$page_title = htmlspecialchars($sp['ten_sp']) . ' | Nội Thất SGN';
include __DIR__ . '/includes/header.php';
?>

<?= getFlash() ?>

<div class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Trang chủ</a><span>/</span>
            <a href="<?= SITE_URL ?>/products.php">Sản phẩm</a><span>/</span>
            <a href="<?= SITE_URL ?>/products.php?loai=<?= $sp['ma_loai'] ?>"><?= htmlspecialchars($sp['ten_loai']) ?></a><span>/</span>
            <span><?= htmlspecialchars($sp['ten_sp']) ?></span>
        </div>
    </div>
</div>

<section class="py-section" style="padding:60px 0;">
    <div class="container">
        <div class="product-detail-grid">
            <!-- Hình ảnh -->
            <div class="product-detail-img">
                <?php if ($sp['hinh'] && file_exists(UPLOAD_PATH . $sp['hinh'])): ?>
                    <img src="<?= UPLOAD_URL . $sp['hinh'] ?>" alt="<?= htmlspecialchars($sp['ten_sp']) ?>">
                <?php else: ?>
                    <span class="no-img-lg">🛋️</span>
                <?php endif; ?>
            </div>

            <!-- Thông tin -->
            <div class="product-detail-info">
                <div class="product-detail-category"><?= htmlspecialchars($sp['ten_loai']) ?></div>
                <h1><?= htmlspecialchars($sp['ten_sp']) ?></h1>
                <div class="product-detail-price"><?= formatMoney($gia) ?></div>

                <div class="product-detail-meta">
                    <p><span class="meta-label">Đơn vị tính:</span> <?= htmlspecialchars($sp['don_vi_tinh']) ?></p>
                    <p><span class="meta-label">Tình trạng:</span>
                        <?php if ($ton > 5): ?>
                            <span style="color:var(--green)">✓ Còn hàng (<?= $ton ?> <?= $sp['don_vi_tinh'] ?>)</span>
                        <?php elseif ($ton > 0): ?>
                            <span style="color:#E67E22">⚠ Sắp hết (còn <?= $ton ?> <?= $sp['don_vi_tinh'] ?>)</span>
                        <?php else: ?>
                            <span style="color:var(--red)">✕ Hết hàng</span>
                        <?php endif; ?>
                    </p>
                    <p><span class="meta-label">Danh mục:</span> <a href="<?= SITE_URL ?>/products.php?loai=<?= $sp['ma_loai'] ?>" class="text-walnut"><?= htmlspecialchars($sp['ten_loai']) ?></a></p>
                </div>

                <?php if ($sp['mo_ta']): ?>
                <p class="product-desc"><?= nl2br(htmlspecialchars($sp['mo_ta'])) ?></p>
                <?php endif; ?>

                <?php if ($ton > 0): ?>
                <form method="POST">
                    <label style="font-size:0.78rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--charcoal-mid);display:block;margin-bottom:8px;">Số lượng</label>
                    <div class="qty-wrapper" style="margin-bottom:20px;">
                        <button type="button" class="qty-btn qty-minus">−</button>
                        <input type="number" name="qty" class="qty-input" value="1" min="1" max="<?= $ton ?>" data-max="<?= $ton ?>">
                        <button type="button" class="qty-btn qty-plus">+</button>
                    </div>
                    <button type="submit" name="add_cart" class="btn-add-to-cart">🛒 Thêm Vào Giỏ Hàng</button>
                </form>
                <?php else: ?>
                <button class="btn-add-to-cart" disabled>Hết Hàng</button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sản phẩm liên quan -->
        <?php if ($related->num_rows): ?>
        <div style="margin-top:80px;">
            <div class="section-header"><h2>Sản Phẩm Tương Tự</h2></div>
            <div class="product-grid">
                <?php while ($rel = $related->fetch_assoc()):
                    $r_gia = ($rel['gia_von']??0)*(1+($rel['tl_loi_nhuan']??20)/100);
                ?>
                <div class="product-card">
                    <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $rel['ma_sp'] ?>" class="product-img">
                        <?php if ($rel['hinh']&&file_exists(UPLOAD_PATH.$rel['hinh'])): ?>
                            <img src="<?= UPLOAD_URL.$rel['hinh'] ?>" alt="<?= htmlspecialchars($rel['ten_sp']) ?>">
                        <?php else: ?><span class="no-img">🛋️</span><?php endif; ?>
                    </a>
                    <div class="product-info">
                        <div class="product-category"><?= htmlspecialchars($rel['ten_loai']) ?></div>
                        <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $rel['ma_sp'] ?>" class="product-name"><?= htmlspecialchars($rel['ten_sp']) ?></a>
                        <div class="product-price"><?= formatMoney($r_gia) ?></div>
                        <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $rel['ma_sp'] ?>" class="btn-cart" style="text-align:center;display:block;">Xem Chi Tiết</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
