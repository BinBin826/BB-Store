<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// Stats
$total_sp     = $conn->query("SELECT COUNT(*) FROM san_pham WHERE trang_thai=1")->fetch_row()[0];
$total_kh     = $conn->query("SELECT COUNT(*) FROM khach_hang")->fetch_row()[0];
$total_dh     = $conn->query("SELECT COUNT(*) FROM don_hang")->fetch_row()[0];
$doanh_thu    = $conn->query("SELECT COALESCE(SUM(tong_tien),0) FROM don_hang WHERE trang_thai='da_giao'")->fetch_row()[0];
$don_moi      = $conn->query("SELECT COUNT(*) FROM don_hang WHERE trang_thai='moi_dat'")->fetch_row()[0];
$het_hang     = $conn->query("SELECT COUNT(*) FROM ton_kho WHERE so_luong=0")->fetch_row()[0];
$sap_het      = $conn->query("SELECT COUNT(*) FROM ton_kho WHERE so_luong>0 AND so_luong<=5")->fetch_row()[0];

// Recent orders
$recent_orders = $conn->query("
    SELECT dh.*, kh.ho_ten, kh.dien_thoai
    FROM don_hang dh JOIN khach_hang kh ON dh.ma_kh=kh.ma_kh
    ORDER BY dh.ngay_dat DESC LIMIT 8
");

// Low stock
$low_stock = $conn->query("
    SELECT sp.ten_sp, ls.ten_loai, tk.so_luong
    FROM ton_kho tk
    JOIN san_pham sp ON tk.ma_sp=sp.ma_sp
    JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai
    WHERE tk.so_luong <= 5 AND sp.trang_thai=1
    ORDER BY tk.so_luong ASC LIMIT 6
");

function formatM($n){ return number_format($n,0,',','.').' ₫'; }
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon brown">🛋️</div>
        <div class="stat-info">
            <small>Sản phẩm</small>
            <strong><?= number_format($total_sp) ?></strong>
            <span class="stat-sub"><?= $het_hang ?> hết hàng</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">👥</div>
        <div class="stat-info">
            <small>Khách hàng</small>
            <strong><?= number_format($total_kh) ?></strong>
            <span class="stat-sub">Tổng tài khoản</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">📝</div>
        <div class="stat-info">
            <small>Đơn hàng</small>
            <strong><?= number_format($total_dh) ?></strong>
            <span class="stat-sub"><?= $don_moi ?> đơn mới</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">💰</div>
        <div class="stat-info">
            <small>Doanh thu</small>
            <strong style="font-size:1.1rem;"><?= formatM($doanh_thu) ?></strong>
            <span class="stat-sub">Đơn đã giao</span>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
    <!-- Recent orders -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>📝 Đơn Hàng Gần Nhất</h3>
            <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-sm btn-secondary">Xem tất cả</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead><tr><th>#</th><th>Khách hàng</th><th>Tổng tiền</th><th>Tình trạng</th><th>Ngày</th></tr></thead>
                <tbody>
                    <?php while ($dh = $recent_orders->fetch_assoc()):
                        $tt = trangThaiDH($dh['trang_thai']);
                    ?>
                    <tr>
                        <td><a href="<?= SITE_URL ?>/admin/orders.php?view=<?= $dh['ma_dh'] ?>" style="color:var(--walnut);font-weight:600;">#<?= str_pad($dh['ma_dh'],4,'0',STR_PAD_LEFT) ?></a></td>
                        <td><?= htmlspecialchars($dh['ho_ten']) ?></td>
                        <td class="fw-600"><?= formatM($dh['tong_tien']) ?></td>
                        <td><span class="badge <?= $tt['class'] ?>"><?= $tt['label'] ?></span></td>
                        <td class="text-muted"><?= date('d/m/Y', strtotime($dh['ngay_dat'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low stock -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>⚠️ Sắp Hết Hàng</h3>
            <a href="<?= SITE_URL ?>/admin/inventory.php" class="btn btn-sm btn-secondary">Tồn kho</a>
        </div>
        <div style="padding:0 0 4px;">
            <?php if (!$low_stock->num_rows): ?>
            <p style="padding:20px;color:var(--gray-mid);text-align:center;font-size:0.88rem;">✅ Tất cả sản phẩm đang có hàng</p>
            <?php else: while ($sp = $low_stock->fetch_assoc()): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 20px;border-bottom:1px solid var(--cream-dark);">
                <div>
                    <div style="font-size:0.88rem;font-weight:500;"><?= htmlspecialchars(mb_strimwidth($sp['ten_sp'],0,30,'...','UTF-8')) ?></div>
                    <div style="font-size:0.75rem;color:var(--gray-mid);"><?= htmlspecialchars($sp['ten_loai']) ?></div>
                </div>
                <span class="badge <?= $sp['so_luong']==0?'badge-danger':'badge-warning' ?>">
                    <?= $sp['so_luong'] ?> còn
                </span>
            </div>
            <?php endwhile; endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
