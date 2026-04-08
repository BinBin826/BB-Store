<?php
$page_title = 'Quản Lý Giá Bán';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_price') {
        $ma_sp = (int)$_POST['ma_sp'];
        $tl    = (float)$_POST['tl_loi_nhuan'];
        $conn->query("INSERT INTO gia_ban (ma_sp,tl_loi_nhuan) VALUES ($ma_sp,$tl) ON DUPLICATE KEY UPDATE tl_loi_nhuan=$tl");
        setFlash('success','Đã cập nhật tỷ lệ lợi nhuận!');
        redirect(SITE_URL . '/admin/pricing.php');
    }
    if ($action === 'bulk_update') {
        $loai  = (int)$_POST['ma_loai'];
        $tl    = (float)$_POST['tl_bulk'];
        $sps   = $conn->query("SELECT ma_sp FROM san_pham WHERE ma_loai=$loai");
        while ($sp = $sps->fetch_assoc()) {
            $id = $sp['ma_sp'];
            $conn->query("INSERT INTO gia_ban (ma_sp,tl_loi_nhuan) VALUES ($id,$tl) ON DUPLICATE KEY UPDATE tl_loi_nhuan=$tl");
        }
        setFlash('success','Đã cập nhật hàng loạt theo loại sản phẩm!');
        redirect(SITE_URL . '/admin/pricing.php');
    }
}

$f_loai = (int)($_GET['loai'] ?? 0);
$where  = 'sp.trang_thai=1';
if ($f_loai) $where .= " AND sp.ma_loai=$f_loai";

$san_phams = $conn->query("
    SELECT sp.ma_sp, sp.ten_sp, ls.ma_loai, ls.ten_loai,
           tk.so_luong, tk.gia_von,
           COALESCE(gb.tl_loi_nhuan, sp.tl_loi_nhuan) as tl_loi_nhuan
    FROM san_pham sp
    JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai
    LEFT JOIN ton_kho tk ON sp.ma_sp=tk.ma_sp
    LEFT JOIN gia_ban gb ON sp.ma_sp=gb.ma_sp
    WHERE $where ORDER BY ls.ten_loai, sp.ten_sp
");

$loai_list = $conn->query("SELECT * FROM loai_sp WHERE trang_thai=1 ORDER BY ten_loai");
?>

<?= getFlash() ?>
<div class="d-flex justify-between align-center mb-20">
    <div><h2 class="page-title">Quản Lý Giá Bán</h2><p class="page-sub">Giá bán = Giá nhập bình quân × (100% + Tỷ lệ lợi nhuận)</p></div>
</div>

<!-- Bulk update by category -->
<div class="admin-card mb-20">
    <div class="admin-card-header"><h3>🔄 Cập Nhật Hàng Loạt Theo Loại</h3></div>
    <div class="admin-card-body">
        <form method="POST" class="d-flex gap-8 align-center flex-wrap">
            <input type="hidden" name="action" value="bulk_update">
            <div class="filter-item flex-1">
                <label>Loại sản phẩm</label>
                <select name="ma_loai" class="form-control" required>
                    <?php while ($l = $loai_list->fetch_assoc()): ?>
                    <option value="<?= $l['ma_loai'] ?>"><?= htmlspecialchars($l['ten_loai']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="filter-item" style="min-width:160px;">
                <label>Tỷ lệ lợi nhuận (%)</label>
                <input type="number" name="tl_bulk" class="form-control" min="0" max="500" step="0.5" value="25" required>
            </div>
            <div style="padding-top:20px;">
                <button type="submit" class="btn btn-warning">Áp Dụng Cho Cả Loại</button>
            </div>
        </form>
    </div>
</div>

<!-- Filter -->
<form method="GET" class="admin-filter">
    <div class="filter-item">
        <label>Lọc theo loại</label>
        <select name="loai">
            <option value="0">Tất cả</option>
            <?php
            $loai_list->data_seek(0);
            while ($l = $loai_list->fetch_assoc()):
            ?>
            <option value="<?= $l['ma_loai'] ?>" <?= $f_loai==$l['ma_loai']?'selected':'' ?>><?= htmlspecialchars($l['ten_loai']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
    <a href="<?= SITE_URL ?>/admin/pricing.php" class="btn btn-secondary btn-sm">Reset</a>
</form>

<!-- Table -->
<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead><tr><th>Sản phẩm</th><th>Loại</th><th>Giá vốn (BQ)</th><th>% Lợi nhuận</th><th>Giá bán</th><th>Cập nhật</th></tr></thead>
            <tbody>
                <?php while ($sp = $san_phams->fetch_assoc()):
                    $gia_von = (float)($sp['gia_von'] ?? 0);
                    $tl      = (float)($sp['tl_loi_nhuan'] ?? 20);
                    $gia_ban = $gia_von * (1 + $tl/100);
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($sp['ten_sp']) ?></strong></td>
                    <td class="text-muted"><?= htmlspecialchars($sp['ten_loai']) ?></td>
                    <td><?= number_format($gia_von,0,',','.') ?> ₫</td>
                    <td>
                        <form method="POST" class="d-flex gap-8 align-center">
                            <input type="hidden" name="action" value="update_price">
                            <input type="hidden" name="ma_sp" value="<?= $sp['ma_sp'] ?>">
                            <input type="number" name="tl_loi_nhuan" value="<?= $tl ?>" min="0" max="500" step="0.5"
                                style="width:80px;" class="form-control">
                            <span>%</span>
                    </td>
                    <td class="fw-600 text-walnut"><?= number_format($gia_ban,0,',','.') ?> ₫</td>
                    <td>
                            <button type="submit" class="btn btn-xs btn-primary">Lưu</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
