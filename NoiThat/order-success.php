<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$ma_dh = (int)($_GET['id'] ?? 0);
$kh_id = (int)$_SESSION['kh_id'];

$r = $conn->query("SELECT * FROM don_hang WHERE ma_dh=$ma_dh AND ma_kh=$kh_id");
if (!$r->num_rows) redirect(SITE_URL . '/index.php');
$dh = $r->fetch_assoc();

$chi_tiet = $conn->query("SELECT * FROM chitiet_dh WHERE ma_dh=$ma_dh");

$page_title = 'Đặt Hàng Thành Công | Nội Thất SGN';
include __DIR__ . '/includes/header.php';
?>

<section class="py-section">
    <div class="container" style="max-width:700px;">
        <div class="success-card">
            <div class="success-icon">🎉</div>
            <h2>Đặt Hàng Thành Công!</h2>
            <p class="text-muted mt-8">Cảm ơn bạn đã mua hàng tại Nội Thất SGN. Chúng tôi sẽ liên hệ xác nhận đơn hàng sớm nhất.</p>

            <div class="order-info-box">
                <div class="info-row"><span>Mã đơn hàng</span><strong>#<?= str_pad($dh['ma_dh'],6,'0',STR_PAD_LEFT) ?></strong></div>
                <div class="info-row"><span>Ngày đặt</span><span><?= date('d/m/Y H:i', strtotime($dh['ngay_dat'])) ?></span></div>
                <div class="info-row"><span>Giao đến</span><span><?= htmlspecialchars($dh['ho_ten_giao']) ?> — <?= htmlspecialchars($dh['dia_chi_giao']) ?>, <?= htmlspecialchars($dh['phuong_xa']??'') ?>, <?= htmlspecialchars($dh['quan_huyen']??'') ?>, <?= htmlspecialchars($dh['tinh_tp']??'') ?></span></div>
                <div class="info-row"><span>Thanh toán</span><span><?= hinhThucTT($dh['hinh_thuc_tt']) ?></span></div>
                <div class="info-row"><span>Tình trạng</span>
                    <?php $tt = trangThaiDH($dh['trang_thai']); ?>
                    <span class="badge <?= $tt['class'] ?>"><?= $tt['label'] ?></span>
                </div>
            </div>

            <h3 style="font-family:var(--font-display);font-weight:400;margin-bottom:16px;">Chi Tiết Đơn Hàng</h3>
            <table class="orders-table" style="margin-bottom:20px;">
                <thead><tr><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
                <tbody>
                    <?php while ($ct = $chi_tiet->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($ct['ten_sp']) ?></td>
                        <td><?= $ct['so_luong'] ?></td>
                        <td><?= formatMoney($ct['gia_ban']) ?></td>
                        <td><?= formatMoney($ct['gia_ban'] * $ct['so_luong']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div style="text-align:right;font-size:1.1rem;font-weight:600;color:var(--walnut);margin-bottom:28px;">
                Tổng cộng: <?= formatMoney($dh['tong_tien']) ?>
            </div>

            <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                <a href="<?= SITE_URL ?>/orders.php" class="btn-primary">Xem Đơn Hàng Của Tôi</a>
                <a href="<?= SITE_URL ?>/products.php" class="btn-outline" style="background:var(--walnut);color:var(--white);">Tiếp Tục Mua Hàng</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
