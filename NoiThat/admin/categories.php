<?php
$page_title = 'Loại Sản Phẩm';
require_once __DIR__ . '/includes/header.php';

$msg = '';
// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add' || $action === 'edit') {
        $ten = trim($_POST['ten_loai'] ?? '');
        $mo_ta = trim($_POST['mo_ta'] ?? '');
        $tt = isset($_POST['trang_thai']) ? 1 : 0;
        if (!$ten) { $msg = '<div class="alert alert-error">Vui lòng nhập tên loại sản phẩm.</div>'; }
        else {
            $t = $conn->real_escape_string($ten);
            $m = $conn->real_escape_string($mo_ta);
            if ($action === 'add') {
                $conn->query("INSERT INTO loai_sp (ten_loai,mo_ta,trang_thai) VALUES ('$t','$m',$tt)");
                setFlash('success','Thêm loại sản phẩm thành công!');
            } else {
                $id = (int)$_POST['ma_loai'];
                $conn->query("UPDATE loai_sp SET ten_loai='$t',mo_ta='$m',trang_thai=$tt WHERE ma_loai=$id");
                setFlash('success','Cập nhật thành công!');
            }
            redirect(SITE_URL . '/admin/categories.php');
        }
    }
    if ($action === 'delete') {
        $id = (int)$_POST['ma_loai'];
        $used = $conn->query("SELECT COUNT(*) FROM san_pham WHERE ma_loai=$id")->fetch_row()[0];
        if ($used > 0) { setFlash('error','Không thể xóa! Loại này đang có sản phẩm.'); }
        else { $conn->query("DELETE FROM loai_sp WHERE ma_loai=$id"); setFlash('success','Đã xóa!'); }
        redirect(SITE_URL . '/admin/categories.php');
    }
    if ($action === 'toggle') {
        $id  = (int)$_POST['ma_loai'];
        $cur = (int)$_POST['cur_status'];
        $conn->query("UPDATE loai_sp SET trang_thai=" . ($cur ? 0 : 1) . " WHERE ma_loai=$id");
        redirect(SITE_URL . '/admin/categories.php');
    }
}

// Lấy dữ liệu edit
$edit = null;
if (isset($_GET['edit'])) {
    $r = $conn->query("SELECT * FROM loai_sp WHERE ma_loai=" . (int)$_GET['edit']);
    $edit = $r->fetch_assoc();
}

$loais = $conn->query("SELECT ls.*, (SELECT COUNT(*) FROM san_pham WHERE ma_loai=ls.ma_loai) as sp_count FROM loai_sp ls ORDER BY ls.ma_loai DESC");
?>

<?= getFlash() ?>
<?= $msg ?>

<div class="d-flex justify-between align-center mb-20">
    <div><h2 class="page-title">Loại Sản Phẩm</h2><p class="page-sub">Quản lý danh mục sản phẩm</p></div>
    <a href="?add=1" class="btn btn-primary">+ Thêm Loại Mới</a>
</div>

<!-- Form thêm/sửa -->
<?php if (isset($_GET['add']) || $edit): ?>
<div class="admin-card mb-20">
    <div class="admin-card-header">
        <h3><?= $edit ? '✏️ Sửa Loại: ' . htmlspecialchars($edit['ten_loai']) : '➕ Thêm Loại Mới' ?></h3>
    </div>
    <div class="admin-card-body">
        <form method="POST">
            <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
            <?php if ($edit): ?><input type="hidden" name="ma_loai" value="<?= $edit['ma_loai'] ?>"><?php endif; ?>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tên Loại *</label>
                    <input type="text" name="ten_loai" class="form-control"
                        value="<?= htmlspecialchars($edit['ten_loai'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Trạng Thái</label>
                    <label class="form-check" style="padding-top:10px;">
                        <input type="checkbox" name="trang_thai" value="1"
                            <?= (!$edit || $edit['trang_thai']) ? 'checked' : '' ?>>
                        Hiển thị (đang bán)
                    </label>
                </div>
                <div class="form-group full">
                    <label class="form-label">Mô tả</label>
                    <textarea name="mo_ta" class="form-control" rows="2"><?= htmlspecialchars($edit['mo_ta'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="d-flex gap-8 mt-12">
                <button type="submit" class="btn btn-primary"><?= $edit ? 'Cập Nhật' : 'Thêm Mới' ?></button>
                <a href="<?= SITE_URL ?>/admin/categories.php" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Table -->
<div class="admin-card">
    <div class="admin-card-header"><h3>Danh Sách Loại Sản Phẩm</h3></div>
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Tên loại</th><th>Mô tả</th><th>Số SP</th><th>Trạng thái</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
                <?php while ($l = $loais->fetch_assoc()): ?>
                <tr>
                    <td><?= $l['ma_loai'] ?></td>
                    <td><strong><?= htmlspecialchars($l['ten_loai']) ?></strong></td>
                    <td class="text-muted" style="max-width:250px;"><?= htmlspecialchars(mb_strimwidth($l['mo_ta']??'',0,60,'...','UTF-8')) ?></td>
                    <td><a href="<?= SITE_URL ?>/admin/products.php?loai=<?= $l['ma_loai'] ?>" style="color:var(--walnut);"><?= $l['sp_count'] ?> SP</a></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="ma_loai" value="<?= $l['ma_loai'] ?>">
                            <input type="hidden" name="cur_status" value="<?= $l['trang_thai'] ?>">
                            <button type="submit" class="badge <?= $l['trang_thai'] ? 'badge-success' : 'badge-secondary' ?>" style="border:none;cursor:pointer;">
                                <?= $l['trang_thai'] ? 'Hiển thị' : 'Đã ẩn' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="?edit=<?= $l['ma_loai'] ?>" class="btn btn-xs btn-info">Sửa</a>
                            <?php if ($l['sp_count'] == 0): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="ma_loai" value="<?= $l['ma_loai'] ?>">
                                <button type="submit" class="btn btn-xs btn-danger confirm-delete">Xóa</button>
                            </form>
                            <?php else: ?>
                            <button class="btn btn-xs btn-secondary" disabled title="Đang có sản phẩm">Xóa</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
