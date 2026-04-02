<?php
$page_title = 'Quản Lý Đơn Hàng';
require_once __DIR__ . '/includes/header.php';

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_dh = (int)$_POST['ma_dh'];
    $tt    = $conn->real_escape_string($_POST['trang_thai'] ?? '');
    $allowed = ['moi_dat','da_xac_nhan','da_giao','da_huy'];
    if (in_array($tt, $allowed)) {
        $conn->query("UPDATE don_hang SET trang_thai='$tt' WHERE ma_dh=$ma_dh");
        // Nếu hủy -> hoàn tồn kho
        if ($tt === 'da_huy') {
            $items = $conn->query("SELECT * FROM chitiet_dh WHERE ma_dh=$ma_dh");
            while ($it = $items->fetch_assoc()) {
                $conn->query("UPDATE ton_kho SET so_luong=so_luong+{$it['so_luong']} WHERE ma_sp={$it['ma_sp']}");
            }
        }
        setFlash('success','Cập nhật trạng thái đơn hàng thành công!');
    }
    redirect(SITE_URL . '/admin/orders.php' . (isset($_POST['view_back']) ? '?view='.$ma_dh : ''));
}

// View detail
$view = (int)($_GET['view'] ?? 0);
$order = null; $order_items = null; $order_kh = null;
if ($view) {
    $r = $conn->query("SELECT dh.*, kh.ho_ten, kh.email, kh.dien_thoai FROM don_hang dh JOIN khach_hang kh ON dh.ma_kh=kh.ma_kh WHERE dh.ma_dh=$view");
    if ($r->num_rows) {
        $order = $r->fetch_assoc();
        $order_items = $conn->query("SELECT cd.*, sp.hinh FROM chitiet_dh cd LEFT JOIN san_pham sp ON cd.ma_sp=sp.ma_sp WHERE cd.ma_dh=$view");
    }
}

// Filter
$f_from    = $_GET['from'] ?? '';
$f_to      = $_GET['to']   ?? '';
$f_status  = $_GET['status'] ?? '';
$f_phuong  = trim($_GET['phuong'] ?? '');
$where     = '1=1';
if ($f_from)   $where .= " AND DATE(dh.ngay_dat)>='$f_from'";
if ($f_to)     $where .= " AND DATE(dh.ngay_dat)<='$f_to'";
if ($f_status) $where .= " AND dh.trang_thai='" . $conn->real_escape_string($f_status) . "'";
if ($f_phuong) $where .= " AND dh.phuong_xa LIKE '%" . $conn->real_escape_string($f_phuong) . "%'";

$sort = ($f_status) ? 'dh.phuong_xa ASC, dh.ngay_dat DESC' : 'dh.ngay_dat DESC';
$orders = $conn->query("SELECT dh.*, kh.ho_ten FROM don_hang dh JOIN khach_hang kh ON dh.ma_kh=kh.ma_kh WHERE $where ORDER BY $sort");

$statuses = [
    'moi_dat'     => 'Mới đặt',
    'da_xac_nhan' => 'Đã xác nhận',
    'da_giao'     => 'Đã giao',
    'da_huy'      => 'Đã hủy',
];
?>

<?= getFlash() ?>
<div class="mb-20">
    <h2 class="page-title">Quản Lý Đơn Hàng</h2>
    <p class="page-sub">Theo dõi và xử lý đơn hàng khách hàng</p>
</div>

<?php if ($order): ?>
<!-- Chi tiết đơn hàng -->
<div style="margin-bottom:16px;"><a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-secondary btn-sm">← Danh sách</a></div>
<div class="admin-card mb-20">
    <div class="admin-card-header">
        <h3>Đơn Hàng #<?= str_pad($order['ma_dh'],6,'0',STR_PAD_LEFT) ?>
            <?php $tt = trangThaiDH($order['trang_thai']); ?>
            <span class="badge <?= $tt['class'] ?>"><?= $tt['label'] ?></span>
        </h3>
        <span class="text-muted" style="font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($order['ngay_dat'])) ?></span>
    </div>
    <div class="admin-card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
            <div>
                <p class="form-hint mb-12">👤 THÔNG TIN KHÁCH HÀNG</p>
                <p><strong><?= htmlspecialchars($order['ho_ten']) ?></strong></p>
                <p><?= htmlspecialchars($order['email']) ?></p>
                <p><?= htmlspecialchars($order['dien_thoai']) ?></p>
            </div>
            <div>
                <p class="form-hint mb-12">📍 ĐỊA CHỈ GIAO HÀNG</p>
                <p><strong><?= htmlspecialchars($order['ho_ten_giao']) ?></strong> — <?= htmlspecialchars($order['dien_thoai_giao']??'') ?></p>
                <p><?= htmlspecialchars($order['dia_chi_giao']) ?></p>
                <p><?= htmlspecialchars($order['phuong_xa']??'') ?>, <?= htmlspecialchars($order['quan_huyen']??'') ?>, <?= htmlspecialchars($order['tinh_tp']??'') ?></p>
            </div>
        </div>
        <p class="mb-12"><strong>Thanh toán:</strong> <?= hinhThucTT($order['hinh_thuc_tt']) ?></p>
        <table class="data-table mb-16">
            <thead><tr><th>Hình</th><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
            <tbody>
                <?php while ($it = $order_items->fetch_assoc()): ?>
                <tr>
                    <td><?php if ($it['hinh']&&file_exists(UPLOAD_PATH.$it['hinh'])): ?><img src="<?= UPLOAD_URL.$it['hinh'] ?>" class="product-thumb"><?php else: ?><div class="no-thumb">🛋️</div><?php endif; ?></td>
                    <td><?= htmlspecialchars($it['ten_sp']) ?></td>
                    <td><?= $it['so_luong'] ?></td>
                    <td><?= number_format($it['gia_ban'],0,',','.') ?> ₫</td>
                    <td class="fw-600"><?= number_format($it['gia_ban']*$it['so_luong'],0,',','.') ?> ₫</td>
                </tr>
                <?php endwhile; ?>
                <tr><td colspan="4" style="text-align:right;font-weight:600;">Tổng cộng:</td>
                    <td class="fw-600 text-walnut"><?= number_format($order['tong_tien'],0,',','.') ?> ₫</td></tr>
            </tbody>
        </table>
        <!-- Update status -->
        <form method="POST" class="d-flex gap-8 align-center">
            <input type="hidden" name="ma_dh" value="<?= $order['ma_dh'] ?>">
            <input type="hidden" name="view_back" value="1">
            <label class="form-label">Cập nhật tình trạng:</label>
            <select name="trang_thai" class="form-control" style="width:auto;">
                <?php foreach ($statuses as $val => $label): ?>
                <option value="<?= $val ?>" <?= $order['trang_thai']===$val?'selected':'' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Cập Nhật</button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- Filter & List -->
<form method="GET" class="admin-filter">
    <div class="filter-item"><label>Từ ngày</label><input type="date" name="from" value="<?= $f_from ?>"></div>
    <div class="filter-item"><label>Đến ngày</label><input type="date" name="to" value="<?= $f_to ?>"></div>
    <div class="filter-item">
        <label>Tình trạng</label>
        <select name="status">
            <option value="">Tất cả</option>
            <?php foreach ($statuses as $v => $l): ?>
            <option value="<?= $v ?>" <?= $f_status===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-item">
        <label>Phường/Xã giao hàng</label>
        <input type="text" name="phuong" placeholder="Tìm theo phường..." value="<?= htmlspecialchars($f_phuong) ?>">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
    <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-secondary btn-sm">Reset</a>
</form>
<?php if ($f_status): ?>
<p class="form-hint mb-12">* Đơn hàng theo tình trạng được sắp xếp theo địa chỉ phường/xã giao hàng</p>
<?php endif; ?>

<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead><tr><th>#</th><th>Khách hàng</th><th>Địa chỉ giao</th><th>Phường</th><th>Tổng tiền</th><th>T.Toán</th><th>Ngày đặt</th><th>Tình trạng</th><th>Xem</th></tr></thead>
            <tbody>
                <?php if (!$orders->num_rows): ?>
                <tr class="empty-row"><td colspan="9">Không có đơn hàng nào</td></tr>
                <?php else: while ($dh = $orders->fetch_assoc()):
                    $tt = trangThaiDH($dh['trang_thai']);
                ?>
                <tr>
                    <td><strong>#<?= str_pad($dh['ma_dh'],4,'0',STR_PAD_LEFT) ?></strong></td>
                    <td><?= htmlspecialchars($dh['ho_ten']) ?></td>
                    <td style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($dh['dia_chi_giao']) ?></td>
                    <td><?= htmlspecialchars($dh['phuong_xa']??'') ?></td>
                    <td class="fw-600"><?= number_format($dh['tong_tien'],0,',','.') ?> ₫</td>
                    <td style="font-size:0.78rem;"><?= hinhThucTT($dh['hinh_thuc_tt']) ?></td>
                    <td><?= date('d/m/Y', strtotime($dh['ngay_dat'])) ?></td>
                    <td><span class="badge <?= $tt['class'] ?>"><?= $tt['label'] ?></span></td>
                    <td><a href="?view=<?= $dh['ma_dh'] ?>" class="btn btn-xs btn-info">Chi tiết</a></td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
