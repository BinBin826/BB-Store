<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$kh_id = (int)$_SESSION['kh_id'];

// View order detail
$view_id = (int)($_GET['view'] ?? 0);
$order_detail = null; $order_items = null;
if ($view_id) {
    $r = $conn->query("SELECT * FROM don_hang WHERE ma_dh=$view_id AND ma_kh=$kh_id");
    if ($r->num_rows) {
        $order_detail = $r->fetch_assoc();
        $order_items  = $conn->query("SELECT * FROM chitiet_dh WHERE ma_dh=$view_id");
    }
}

// Order list - gần nhất hiển thị trên cùng
$orders = $conn->query("SELECT * FROM don_hang WHERE ma_kh=$kh_id ORDER BY ngay_dat DESC");

$page_title = 'Đơn Hàng Của Tôi | Nội Thất SGN';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Đơn Hàng Của Tôi</h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Trang chủ</a><span>/</span>
            <span>Đơn hàng</span>
        </div>
    </div>
</div>

<section class="py-section" style="padding:48px 0 80px;">
    <div class="container">
        <?= getFlash() ?>

        <?php if ($order_detail): ?>
        <!-- Chi tiết đơn hàng -->
        <div style="margin-bottom:24px;">
            <a href="<?= SITE_URL ?>/orders.php" style="color:var(--walnut);font-size:0.88rem;">← Quay lại danh sách</a>
        </div>
        <div class="form-card">
            <h2 style="font-family:var(--font-display);font-weight:400;margin-bottom:20px;">
                Chi Tiết Đơn Hàng #<?= str_pad($order_detail['ma_dh'],6,'0',STR_PAD_LEFT) ?>
            </h2>
            <div class="order-info-box mb-24">
                <div class="info-row"><span>Ngày đặt</span><span><?= date('d/m/Y H:i', strtotime($order_detail['ngay_dat'])) ?></span></div>
                <div class="info-row"><span>Giao đến</span><span><?= htmlspecialchars($order_detail['ho_ten_giao']) ?> — <?= htmlspecialchars($order_detail['dia_chi_giao']) ?>, <?= htmlspecialchars($order_detail['phuong_xa']??'') ?>, <?= htmlspecialchars($order_detail['quan_huyen']??'') ?>, <?= htmlspecialchars($order_detail['tinh_tp']??'') ?></span></div>
                <div class="info-row"><span>Điện thoại</span><span><?= htmlspecialchars($order_detail['dien_thoai_giao']??'') ?></span></div>
                <div class="info-row"><span>Thanh toán</span><span><?= hinhThucTT($order_detail['hinh_thuc_tt']) ?></span></div>
                <div class="info-row"><span>Tình trạng</span>
                    <?php $tt = trangThaiDH($order_detail['trang_thai']); ?>
                    <span class="badge <?= $tt['class'] ?>"><?= $tt['label'] ?></span>
                </div>
            </div>
            <table class="orders-table">
                <thead><tr><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
                <tbody>
                    <?php while ($ct = $order_items->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($ct['ten_sp']) ?></td>
                        <td><?= $ct['so_luong'] ?></td>
                        <td><?= formatMoney($ct['gia_ban']) ?></td>
                        <td><?= formatMoney($ct['gia_ban']*$ct['so_luong']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <div style="text-align:right;margin-top:16px;font-size:1.1rem;font-weight:600;color:var(--walnut);">
                Tổng cộng: <?= formatMoney($order_detail['tong_tien']) ?>
            </div>
        </div>

        <?php elseif (!$orders->num_rows): ?>
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h3>Chưa có đơn hàng nào</h3>
            <p>Bạn chưa đặt hàng nào. Hãy bắt đầu mua sắm ngay!</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn-primary">Mua Sắm Ngay</a>
        </div>

        <?php else: ?>
        <div class="card" style="padding:0;overflow:hidden;">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Ngày đặt</th>
                        <th>Địa chỉ giao</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Tình trạng</th>
                        <th>Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($dh = $orders->fetch_assoc()):
                        $tt = trangThaiDH($dh['trang_thai']);
                    ?>
                    <tr>
                        <td><strong>#<?= str_pad($dh['ma_dh'],6,'0',STR_PAD_LEFT) ?></strong></td>
                        <td><?= date('d/m/Y H:i', strtotime($dh['ngay_dat'])) ?></td>
                        <td><?= htmlspecialchars(mb_strimwidth($dh['dia_chi_giao']??'',0,40,'...','UTF-8')) ?></td>
                        <td><strong class="text-walnut"><?= formatMoney($dh['tong_tien']) ?></strong></td>
                        <td><?= hinhThucTT($dh['hinh_thuc_tt']) ?></td>
                        <td><span class="badge <?= $tt['class'] ?>"><?= $tt['label'] ?></span></td>
                        <td><a href="?view=<?= $dh['ma_dh'] ?>" class="btn-primary" style="padding:6px 14px;font-size:0.8rem;">Xem</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
