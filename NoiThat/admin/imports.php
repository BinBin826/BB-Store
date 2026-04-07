<?php
$page_title = 'Nhập Hàng';
require_once __DIR__ . '/includes/header.php';

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_phieu') {
        $ngay   = $_POST['ngay_nhap'] ?? date('Y-m-d');
        $ghi_chu= trim($_POST['ghi_chu'] ?? '');
        $g = $conn->real_escape_string($ghi_chu);
        $conn->query("INSERT INTO phieu_nhap (ngay_nhap,ghi_chu) VALUES ('$ngay','$g')");
        $id = $conn->insert_id;
        setFlash('success','Tạo phiếu nhập #' . $id . ' thành công! Thêm sản phẩm vào phiếu.');
        redirect(SITE_URL . '/admin/imports.php?edit=' . $id);
    }

    if ($action === 'add_item') {
        $ma_phieu = (int)$_POST['ma_phieu'];
        $ma_sp    = (int)$_POST['ma_sp'];
        $sl       = (int)$_POST['so_luong'];
        $gia      = (float)$_POST['gia_nhap'];
        
        $r = $conn->query("SELECT trang_thai FROM phieu_nhap WHERE ma_phieu=$ma_phieu");
        $p = $r->fetch_assoc();
        if ($p['trang_thai']) { 
            setFlash('error','Không thể sửa phiếu đã hoàn thành.'); 
        } else {
            if ($sl <= 0 || $gia <= 0) { 
                setFlash('error','Số lượng và giá nhập phải lớn hơn 0.'); 
            } else {
                $exist = $conn->query("SELECT id FROM chitiet_nhap WHERE ma_phieu=$ma_phieu AND ma_sp=$ma_sp");
                if ($exist->num_rows) {
                    $eid = $exist->fetch_assoc()['id'];
                    $conn->query("UPDATE chitiet_nhap SET so_luong=$sl,gia_nhap=$gia WHERE id=$eid");
                } else {
                    $conn->query("INSERT INTO chitiet_nhap (ma_phieu,ma_sp,so_luong,gia_nhap) VALUES ($ma_phieu,$ma_sp,$sl,$gia)");
                }
                setFlash('success','Đã thêm sản phẩm vào phiếu nhập.');
            }
        }
        redirect(SITE_URL . '/admin/imports.php?edit=' . $ma_phieu);
    }

    if ($action === 'remove_item') {
        $id = (int)$_POST['item_id'];
        $ma_phieu = (int)$_POST['ma_phieu'];
        $conn->query("DELETE FROM chitiet_nhap WHERE id=$id");
        setFlash('success','Đã xóa dòng khỏi phiếu nhập.');
        redirect(SITE_URL . '/admin/imports.php?edit=' . $ma_phieu);
    }

    if ($action === 'complete') {
        $ma_phieu = (int)$_POST['ma_phieu'];
        $r = $conn->query("SELECT * FROM phieu_nhap WHERE ma_phieu=$ma_phieu");
        $p = $r->fetch_assoc();
        if ($p['trang_thai']) { 
            setFlash('error','Phiếu đã hoàn thành rồi.'); 
        } else {
            $items = $conn->query("SELECT * FROM chitiet_nhap WHERE ma_phieu=$ma_phieu");
            if (!$items->num_rows) { 
                setFlash('error','Phiếu không có sản phẩm nào.'); 
            } else {
                $conn->begin_transaction();
                try {
                    while ($it = $items->fetch_assoc()) {
                        updateTonKhoBinhQuan($conn, $it['ma_sp'], $it['so_luong'], $it['gia_nhap']);
                    }
                    $conn->query("UPDATE phieu_nhap SET trang_thai=1 WHERE ma_phieu=$ma_phieu");
                    $conn->commit();
                    setFlash('success','Hoàn thành phiếu nhập! Tồn kho đã được cập nhật.');
                } catch(Exception $e) {
                    $conn->rollback();
                    setFlash('error','Lỗi khi hoàn thành phiếu.');
                }
            }
        }
        redirect(SITE_URL . '/admin/imports.php');
    }

    if ($action === 'delete_phieu') {
        $ma_phieu = (int)$_POST['ma_phieu'];
        $r = $conn->query("SELECT trang_thai FROM phieu_nhap WHERE ma_phieu=$ma_phieu");
        $p = $r->fetch_assoc();
        if ($p['trang_thai']) { 
            setFlash('error','Không thể xóa phiếu đã hoàn thành.'); 
        } else {
            $conn->query("DELETE FROM chitiet_nhap WHERE ma_phieu=$ma_phieu");
            $conn->query("DELETE FROM phieu_nhap WHERE ma_phieu=$ma_phieu");
            setFlash('success','Đã xóa phiếu nhập.');
        }
        redirect(SITE_URL . '/admin/imports.php');
    }
}

// Edit phiếu
$edit_phieu = null; $edit_items = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $r = $conn->query("SELECT * FROM phieu_nhap WHERE ma_phieu=$eid");
    $edit_phieu = $r->fetch_assoc();
    $edit_items = $conn->query("SELECT ci.*, sp.ten_sp, ls.ten_loai FROM chitiet_nhap ci
        JOIN san_pham sp ON ci.ma_sp=sp.ma_sp
        JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai
        WHERE ci.ma_phieu=$eid");
}

// Filter
$f_from = $_GET['from'] ?? '';
$f_to   = $_GET['to'] ?? '';
$where  = '1=1';
if ($f_from) $where .= " AND ngay_nhap>='$f_from'";
if ($f_to)   $where .= " AND ngay_nhap<='$f_to'";

$phieus = $conn->query("
    SELECT pn.*, 
    (SELECT COUNT(*) FROM chitiet_nhap WHERE ma_phieu=pn.ma_phieu) as so_sp,
    (SELECT SUM(so_luong * gia_nhap) FROM chitiet_nhap WHERE ma_phieu=pn.ma_phieu) as tong_tien
    FROM phieu_nhap pn 
    WHERE $where 
    ORDER BY pn.ma_phieu DESC
");

$sp_list = $conn->query("SELECT sp.ma_sp, sp.ten_sp, ls.ten_loai, tk.so_luong FROM san_pham sp JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai LEFT JOIN ton_kho tk ON sp.ma_sp=tk.ma_sp WHERE sp.trang_thai=1 ORDER BY ls.ten_loai,sp.ten_sp");
?>

<?= getFlash() ?>

<div class="d-flex justify-between align-center mb-20">
    <div>
        <h2 class="page-title">Quản Lý Nhập Hàng</h2>
        <p class="page-sub">Phiếu nhập hàng & cập nhật tồn kho bình quân</p>
    </div>
    <?php if (!$edit_phieu): ?>
    <form method="POST" style="display:flex;gap:8px;align-items:center;">
        <input type="hidden" name="action" value="create_phieu">
        <input type="date" name="ngay_nhap" class="form-control" value="<?= date('Y-m-d') ?>" style="width:170px;">
        <input type="text" name="ghi_chu" class="form-control" placeholder="Ghi chú..." style="width:200px;">
        <button type="submit" class="btn btn-primary">+ Tạo Phiếu Nhập</button>
    </form>
    <?php endif; ?>
</div>

<?php if ($edit_phieu): ?>
<div class="admin-card mb-20">
    <div class="admin-card-header">
        <h3>📦 Phiếu Nhập #<?= $edit_phieu['ma_phieu'] ?> — <?= date('d/m/Y', strtotime($edit_phieu['ngay_nhap'])) ?>
            <?= $edit_phieu['trang_thai'] ? '<span class="badge badge-success ml-8">Đã hoàn thành</span>' : '<span class="badge badge-warning ml-8">Chưa hoàn thành</span>' ?>
        </h3>
        <a href="<?= SITE_URL ?>/admin/imports.php" class="btn btn-sm btn-secondary">← Danh sách</a>
    </div>
    <div class="admin-card-body">
        <?php if (!$edit_phieu['trang_thai']): ?>
        <form method="POST" class="d-flex gap-8 align-center flex-wrap mb-20" style="background:var(--gray-light);padding:16px;border-radius:6px;">
            <input type="hidden" name="action" value="add_item">
            <input type="hidden" name="ma_phieu" value="<?= $edit_phieu['ma_phieu'] ?>">
            
            <div class="filter-item flex-1">
                <label>Sản phẩm</label>
                <select name="ma_sp" class="form-control" required>
                    <option value="">-- Chọn sản phẩm --</option>
                    <?php while ($sp = $sp_list->fetch_assoc()): ?>
                    <option value="<?= $sp['ma_sp'] ?>"><?= htmlspecialchars($sp['ten_loai']) ?> — <?= htmlspecialchars($sp['ten_sp']) ?> (Tồn: <?= $sp['so_luong'] ?? 0 ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="filter-item" style="min-width:120px;">
                <label>Số lượng</label>
                <input type="number" id="so_luong_input" name="so_luong" class="form-control" min="1" placeholder="SL" required>
            </div>
            
            <div class="filter-item" style="min-width:160px;">
                <label>Giá nhập (₫)</label>
                <input type="number" id="gia_nhap_input" name="gia_nhap" class="form-control" min="1" step="any" placeholder="Nhập giá..." required>
            </div>
            
            <div class="filter-item" style="min-width:160px;">
                <label>Thành tiền</label>
                <input type="text" id="thanh_tien_preview" class="form-control" placeholder="Tự động tính..." readonly style="background-color: #e9ecef; font-weight: bold;">
            </div>

            <div style="padding-top:20px;">
                <button type="submit" class="btn btn-success" style="color: #ffffff;">+ Thêm</button>
            </div>
        </form>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Loại</th>
                    <th>SL nhập</th>
                    <th>Giá nhập</th>
                    <th>Thành tiền</th>
                    <?= !$edit_phieu['trang_thai'] ? '<th>Thao tác</th>' : '' ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $tong_phieu = 0;
                while ($it = $edit_items->fetch_assoc()):
                    $tt = $it['so_luong'] * $it['gia_nhap'];
                    $tong_phieu += $tt;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($it['ten_sp']) ?></strong></td>
                    <td class="text-muted"><?= htmlspecialchars($it['ten_loai']) ?></td>
                    <td><?= $it['so_luong'] ?></td>
                    <td><?= number_format($it['gia_nhap'],0,',','.') ?> ₫</td>
                    <td class="fw-600"><?= number_format($tt,0,',','.') ?> ₫</td>
                    <?php if (!$edit_phieu['trang_thai']): ?>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="remove_item">
                            <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                            <input type="hidden" name="ma_phieu" value="<?= $edit_phieu['ma_phieu'] ?>">
                            <button type="submit" class="btn btn-xs btn-danger confirm-delete" style="color: #ffffff;">Xóa</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
                <tr style="background:#f9f9f9;">
                    <td colspan="4" style="text-align:right;font-weight:600;">Tổng cộng phiếu:</td>
                    <td colspan="2" class="fw-600 text-primary" style="font-size:1.1em;">
                        <?= $tong_phieu > 0 ? number_format($tong_phieu,0,',','.') . ' ₫' : '<span class="text-muted">Chưa có SP</span>' ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php if (!$edit_phieu['trang_thai']): ?>
        <div class="d-flex gap-8 mt-16">
            <form method="POST">
                <input type="hidden" name="action" value="complete">
                <input type="hidden" name="ma_phieu" value="<?= $edit_phieu['ma_phieu'] ?>">
                <button type="submit" class="btn btn-success confirm-complete" style="color: #ffffff;">✓ Hoàn Thành & Cập Nhật Kho</button>
            </form>
            <form method="POST">
                <input type="hidden" name="action" value="delete_phieu">
                <input type="hidden" name="ma_phieu" value="<?= $edit_phieu['ma_phieu'] ?>">
                <button type="submit" class="btn btn-danger confirm-delete" style="color: #ffffff;">Hủy Phiếu</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!$edit_phieu): ?>
<form method="GET" class="admin-filter">
    <div class="filter-item"><label>Từ ngày</label><input type="date" name="from" value="<?= $f_from ?>"></div>
    <div class="filter-item"><label>Đến ngày</label><input type="date" name="to" value="<?= $f_to ?>"></div>
    <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
    <a href="imports.php" class="btn btn-secondary btn-sm">Reset</a>
</form>

<div class="admin-card">
    <table class="data-table">
        <thead><tr><th>#</th><th>Ngày nhập</th><th>Số SP</th><th>Tổng tiền</th><th>Ghi chú</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
        <tbody>
            <?php if (!$phieus->num_rows): ?>
            <tr><td colspan="7" class="text-center">Chưa có dữ liệu</td></tr>
            <?php else: while ($p = $phieus->fetch_assoc()): ?>
            <tr>
                <td><strong>#<?= $p['ma_phieu'] ?></strong></td>
                <td><?= date('d/m/Y', strtotime($p['ngay_nhap'])) ?></td>
                <td><?= $p['so_sp'] ?> SP</td>
                <td class="fw-600 text-walnut">
                    <?= $p['tong_tien'] > 0 ? number_format($p['tong_tien'], 0, ',', '.') . ' ₫' : '---' ?>
                </td>
                <td class="text-muted"><?= htmlspecialchars($p['ghi_chu'] ?? '') ?></td>
                <td><span class="badge <?= $p['trang_thai'] ? 'badge-success' : 'badge-warning' ?>"><?= $p['trang_thai'] ? 'Hoàn thành' : 'Đang soạn' ?></span></td>
                <td>
                    <a href="?edit=<?= $p['ma_phieu'] ?>" class="btn btn-xs btn-info"><?= $p['trang_thai'] ? 'Xem' : 'Sửa' ?></a>
                </td>
            </tr>
            <?php endwhile; endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const slInput = document.getElementById("so_luong_input");
    const giaInput = document.getElementById("gia_nhap_input");
    const ttPreview = document.getElementById("thanh_tien_preview");

    function calculatePreview() {
        if (slInput && giaInput && ttPreview) {
            let slVal = slInput.value;
            let giaVal = giaInput.value;

            if (slVal && giaVal && parseFloat(slVal) > 0 && parseFloat(giaVal) > 0) {
                let tt = parseFloat(slVal) * parseFloat(giaVal);
                ttPreview.value = tt.toLocaleString("vi-VN") + " ₫";
            } else {
                ttPreview.value = "";
            }
        }
    }

    if (slInput) slInput.addEventListener("input", calculatePreview);
    if (giaInput) giaInput.addEventListener("input", calculatePreview);
    
    calculatePreview();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>