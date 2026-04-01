<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        if (!isLoggedIn()) {
            setFlash('warning','Vui lòng đăng nhập để thêm vào giỏ hàng.');
            redirect(SITE_URL . '/auth/login.php?redirect=' . urlencode(SITE_URL . '/cart.php'));
        }
        $ma_sp  = (int)$_POST['ma_sp'];
        $kh_id  = (int)$_SESSION['kh_id'];
        $ton    = getTonKho($conn, $ma_sp);
        if ($ton <= 0) { setFlash('error','Sản phẩm đã hết hàng.'); redirect(SITE_URL . '/cart.php'); }
        $exist = $conn->query("SELECT id,so_luong FROM gio_hang WHERE ma_kh=$kh_id AND ma_sp=$ma_sp");
        if ($exist->num_rows) {
            $row = $exist->fetch_assoc();
            $nq  = min($ton, $row['so_luong']+1);
            $conn->query("UPDATE gio_hang SET so_luong=$nq WHERE id={$row['id']}");
        } else {
            $conn->query("INSERT INTO gio_hang (ma_kh,ma_sp,so_luong) VALUES ($kh_id,$ma_sp,1)");
        }
        setFlash('success','Đã thêm sản phẩm vào giỏ hàng!');
        redirect(SITE_URL . '/cart.php');
    }

    if ($action === 'update' && isLoggedIn()) {
        $kh_id = (int)$_SESSION['kh_id'];
        $ma_sp = (int)$_POST['ma_sp'];
        $qty   = (int)$_POST['qty'];
        if ($qty <= 0) {
            $conn->query("DELETE FROM gio_hang WHERE ma_kh=$kh_id AND ma_sp=$ma_sp");
        } else {
            $ton = getTonKho($conn, $ma_sp);
            $qty = min($ton, $qty);
            $conn->query("UPDATE gio_hang SET so_luong=$qty WHERE ma_kh=$kh_id AND ma_sp=$ma_sp");
        }
        redirect(SITE_URL . '/cart.php');
    }

    if ($action === 'remove' && isLoggedIn()) {
        $kh_id = (int)$_SESSION['kh_id'];
        $ma_sp = (int)$_POST['ma_sp'];
        $conn->query("DELETE FROM gio_hang WHERE ma_kh=$kh_id AND ma_sp=$ma_sp");
        setFlash('success','Đã xóa sản phẩm khỏi giỏ hàng.');
        redirect(SITE_URL . '/cart.php');
    }
}

// Load cart
$cart_items = [];
$tong = 0;
if (isLoggedIn()) {
    $kh_id = (int)$_SESSION['kh_id'];
    $cart_r = $conn->query("
        SELECT gh.*, sp.ten_sp, sp.hinh, sp.don_vi_tinh, ls.ten_loai,
               tk.gia_von, tk.so_luong as ton_kho,
               COALESCE(gb.tl_loi_nhuan, sp.tl_loi_nhuan) as tl_loi_nhuan
        FROM gio_hang gh
        JOIN san_pham sp ON gh.ma_sp=sp.ma_sp
        JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai
        LEFT JOIN ton_kho tk ON gh.ma_sp=tk.ma_sp
        LEFT JOIN gia_ban gb ON gh.ma_sp=gb.ma_sp
        WHERE gh.ma_kh=$kh_id AND sp.trang_thai=1
        ORDER BY gh.created_at DESC
    ");
    while ($item = $cart_r->fetch_assoc()) {
        $item['gia_ban'] = ($item['gia_von']??0) * (1 + ($item['tl_loi_nhuan']??20)/100);
        $item['thanh_tien'] = $item['gia_ban'] * $item['so_luong'];
        $tong += $item['thanh_tien'];
        $cart_items[] = $item;
    }
}

$page_title = 'Giỏ Hàng | Nội Thất SGN';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Giỏ Hàng</h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Trang chủ</a><span>/</span><span>Giỏ hàng</span>
        </div>
    </div>
</div>

<section class="py-section" style="padding:48px 0 80px;">
    <div class="container">
        <?= getFlash() ?>

        <?php if (!isLoggedIn()): ?>
        <div class="empty-state">
            <div class="empty-icon">🛒</div>
            <h3>Vui lòng đăng nhập</h3>
            <p>Bạn cần đăng nhập để sử dụng chức năng giỏ hàng.</p>
            <a href="<?= SITE_URL ?>/auth/login.php?redirect=<?= urlencode(SITE_URL.'/cart.php') ?>" class="btn-primary">Đăng Nhập Ngay</a>
        </div>

        <?php elseif (empty($cart_items)): ?>
        <div class="empty-state">
            <div class="empty-icon">🛒</div>
            <h3>Giỏ hàng trống</h3>
            <p>Bạn chưa có sản phẩm nào trong giỏ hàng.</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn-primary">Tiếp Tục Mua Hàng</a>
        </div>

        <?php else: ?>
        <div class="cart-layout">
            <!-- Cart items -->
            <div>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <div class="cart-product">
                                    <div class="cart-product-img">
                                        <?php if ($item['hinh']&&file_exists(UPLOAD_PATH.$item['hinh'])): ?>
                                            <img src="<?= UPLOAD_URL.$item['hinh'] ?>" alt="<?= htmlspecialchars($item['ten_sp']) ?>">
                                        <?php else: ?>
                                            <div style="height:100%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;">🛋️</div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $item['ma_sp'] ?>" class="cart-product-name"><?= htmlspecialchars($item['ten_sp']) ?></a>
                                        <div style="font-size:0.78rem;color:var(--gray-mid);margin-top:2px;"><?= htmlspecialchars($item['ten_loai']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= formatMoney($item['gia_ban']) ?></td>
                            <td>
                                <form method="POST" style="display:flex;align-items:center;gap:0;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="ma_sp" value="<?= $item['ma_sp'] ?>">
                                    <div class="cart-qty">
                                        <button type="submit" name="qty" value="<?= $item['so_luong']-1 ?>" class="cart-qty-btn">−</button>
                                        <span class="cart-qty-num"><?= $item['so_luong'] ?></span>
                                        <button type="submit" name="qty" value="<?= min($item['ton_kho'], $item['so_luong']+1) ?>" class="cart-qty-btn"
                                            <?= $item['so_luong']>=$item['ton_kho']?'disabled':'' ?>>+</button>
                                    </div>
                                </form>
                            </td>
                            <td><strong class="text-walnut"><?= formatMoney($item['thanh_tien']) ?></strong></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="ma_sp" value="<?= $item['ma_sp'] ?>">
                                    <button type="submit" class="btn-remove" title="Xóa">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top:16px;">
                    <a href="<?= SITE_URL ?>/products.php" style="color:var(--walnut);font-size:0.88rem;">← Tiếp tục mua hàng</a>
                </div>
            </div>

            <!-- Order summary -->
            <div class="cart-summary">
                <h3>Tóm Tắt Đơn Hàng</h3>
                <?php foreach ($cart_items as $item): ?>
                <div class="summary-row">
                    <span><?= htmlspecialchars($item['ten_sp']) ?> × <?= $item['so_luong'] ?></span>
                    <span><?= formatMoney($item['thanh_tien']) ?></span>
                </div>
                <?php endforeach; ?>
                <div class="summary-total">
                    <span>Tổng cộng</span>
                    <span><?= formatMoney($tong) ?></span>
                </div>
                <a href="<?= SITE_URL ?>/checkout.php" class="btn-checkout">Tiến Hành Đặt Hàng →</a>
                <p style="font-size:0.78rem;color:var(--gray-mid);text-align:center;margin-top:12px;">
                    🔒 Thanh toán an toàn & bảo mật
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
