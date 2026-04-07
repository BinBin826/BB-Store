<?php
$page_title = 'Sản Phẩm';
require_once __DIR__ . '/includes/header.php';

// --- 1. HÀM UPLOAD ẢNH (Đã tối ưu: Tự tạo thư mục, chống trùng tên) ---
function handleUpload($field) {
    if (empty($_FILES[$field]['name'])) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return false;

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return false;

    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0777, true);
    }

    $name = uniqid('sp_') . '.' . $ext;
    if (move_uploaded_file($_FILES[$field]['tmp_name'], UPLOAD_PATH . $name)) {
        return $name;
    }
    return false;
}

// --- 2. XỬ LÝ HÀNH ĐỘNG (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // THÊM SẢN PHẨM
    if ($action === 'add') {
        $ten   = trim($_POST['ten_sp'] ?? '');
        $loai  = (int)$_POST['ma_loai'];
        $mo_ta = trim($_POST['mo_ta'] ?? '');
        $dvt   = trim($_POST['don_vi_tinh'] ?? 'Cái');
        $tl    = (float)$_POST['tl_loi_nhuan'];
        $tt    = isset($_POST['trang_thai']) ? 1 : 0;
        $sl_bd = (int)($_POST['so_luong_ban_dau'] ?? 0);
        $gia_bd= (float)($_POST['gia_ban_dau'] ?? 0);

        if (!$ten || !$loai) { 
            setFlash('error','Vui lòng điền đầy đủ thông tin.'); 
            redirect(SITE_URL.'/admin/products.php?add=1'); 
        }

        $hinh = handleUpload('hinh');
        if ($hinh === false && !empty($_FILES['hinh']['name'])) {
            setFlash('error','Ảnh không hợp lệ hoặc lỗi lưu file.');
            redirect(SITE_URL.'/admin/products.php?add=1');
        }

        $t = $conn->real_escape_string($ten);
        $m = $conn->real_escape_string($mo_ta);
        $d = $conn->real_escape_string($dvt);
        $h = $hinh ? $conn->real_escape_string($hinh) : '';

        $conn->query("INSERT INTO san_pham (ma_loai,ten_sp,mo_ta,don_vi_tinh,hinh,tl_loi_nhuan,trang_thai) VALUES ($loai,'$t','$m','$d','$h',$tl,$tt)");
        $new_id = $conn->insert_id;
        $conn->query("INSERT INTO ton_kho (ma_sp,so_luong,gia_von) VALUES ($new_id,0,0)");
        $conn->query("INSERT INTO gia_ban (ma_sp,tl_loi_nhuan) VALUES ($new_id,$tl)");
        
        setFlash('success','Thêm sản phẩm thành công!');
        redirect(SITE_URL . '/admin/products.php');
    }

    // SỬA SẢN PHẨM
    if ($action === 'edit') {
        $id      = (int)$_POST['ma_sp'];
        $ten     = trim($_POST['ten_sp'] ?? '');
        $loai    = (int)$_POST['ma_loai'];
        $mo_ta   = trim($_POST['mo_ta'] ?? '');
        $dvt     = trim($_POST['don_vi_tinh'] ?? 'Cái');
        $tl      = (float)$_POST['tl_loi_nhuan'];
        $tt      = isset($_POST['trang_thai']) ? 1 : 0;
        $del_img = isset($_POST['delete_image']) ? 1 : 0;

        $t = $conn->real_escape_string($ten);
        $m = $conn->real_escape_string($mo_ta);
        $d = $conn->real_escape_string($dvt);

        $r = $conn->query("SELECT hinh FROM san_pham WHERE ma_sp=$id");
        $cur = $r->fetch_assoc();
        $hinh_sql = '';
        $new_hinh = handleUpload('hinh');

        if ($new_hinh) {
            if ($cur['hinh'] && file_exists(UPLOAD_PATH . $cur['hinh'])) @unlink(UPLOAD_PATH . $cur['hinh']);
            $hinh_sql = ",hinh='" . $conn->real_escape_string($new_hinh) . "'";
        } elseif ($del_img) {
            if ($cur['hinh'] && file_exists(UPLOAD_PATH . $cur['hinh'])) @unlink(UPLOAD_PATH . $cur['hinh']);
            $hinh_sql = ",hinh=''";
        }

        $conn->query("UPDATE san_pham SET ma_loai=$loai,ten_sp='$t',mo_ta='$m',don_vi_tinh='$d',tl_loi_nhuan=$tl,trang_thai=$tt $hinh_sql WHERE ma_sp=$id");
        $conn->query("INSERT INTO gia_ban (ma_sp,tl_loi_nhuan) VALUES ($id,$tl) ON DUPLICATE KEY UPDATE tl_loi_nhuan=$tl");
        
        setFlash('success','Cập nhật thành công!');
        redirect(SITE_URL . '/admin/products.php');
    }

    // XÓA SẢN PHẨM
    if ($action === 'delete') {
        $id = (int)$_POST['ma_sp'];
        $r  = $conn->query("SELECT hinh FROM san_pham WHERE ma_sp=$id");
        $sp = $r->fetch_assoc();
        $has_import = $conn->query("SELECT COUNT(*) FROM chitiet_nhap WHERE ma_sp=$id")->fetch_row()[0];
        
        if ($has_import > 0) {
            $conn->query("UPDATE san_pham SET trang_thai=0 WHERE ma_sp=$id");
            setFlash('warning','Đã ẩn sản phẩm (có lịch sử nhập hàng).');
        } else {
            if ($sp['hinh'] && file_exists(UPLOAD_PATH . $sp['hinh'])) @unlink(UPLOAD_PATH . $sp['hinh']);
$conn->query("DELETE FROM gio_hang WHERE ma_sp=$id");
$conn->query("DELETE FROM ton_kho WHERE ma_sp=$id");
$conn->query("DELETE FROM gia_ban WHERE ma_sp=$id");
$conn->query("DELETE FROM san_pham WHERE ma_sp=$id");
            setFlash('success','Đã xóa sản phẩm!');
        }
        redirect(SITE_URL . '/admin/products.php');
    }
}

// --- 3. LẤY DỮ LIỆU ĐỂ HIỂN THỊ ---
$edit = null;
if (isset($_GET['edit'])) {
    $r = $conn->query("SELECT sp.*, tk.so_luong as ton_kho, tk.gia_von, COALESCE(gb.tl_loi_nhuan,sp.tl_loi_nhuan) as tl_gb
                        FROM san_pham sp
                        LEFT JOIN ton_kho tk ON sp.ma_sp=tk.ma_sp
                        LEFT JOIN gia_ban gb ON sp.ma_sp=gb.ma_sp
                        WHERE sp.ma_sp=" . (int)$_GET['edit']);
    $edit = $r->fetch_assoc();
}

$f_loai = (int)($_GET['loai'] ?? 0);
$f_key  = trim($_GET['q'] ?? '');
$where  = '1=1';
if ($f_loai) $where .= " AND sp.ma_loai=$f_loai";
if ($f_key)  $where .= " AND sp.ten_sp LIKE '%" . $conn->real_escape_string($f_key) . "%'";

$san_phams = $conn->query("
    SELECT sp.*, ls.ten_loai, tk.so_luong as ton_kho, tk.gia_von,
           COALESCE(gb.tl_loi_nhuan,sp.tl_loi_nhuan) as tl_gb
    FROM san_pham sp
    JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai
    LEFT JOIN ton_kho tk ON sp.ma_sp=tk.ma_sp
    LEFT JOIN gia_ban gb ON sp.ma_sp=gb.ma_sp
    WHERE $where ORDER BY sp.created_at DESC
");

$loai_list = $conn->query("SELECT * FROM loai_sp ORDER BY ten_loai");
?>

<?= getFlash() ?>

<div class="d-flex justify-between align-center mb-20">
    <div><h2 class="page-title">Sản Phẩm</h2><p class="page-sub">Quản lý danh mục sản phẩm</p></div>
    <a href="?add=1" class="btn btn-primary">+ Thêm Sản Phẩm</a>
</div>

<?php if (isset($_GET['add']) || $edit): ?>
<div class="admin-card mb-20">
    <div class="admin-card-header"><h3><?= $edit ? '✏️ Sửa: '.htmlspecialchars($edit['ten_sp']) : '➕ Thêm Sản Phẩm' ?></h3></div>
    <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
            <?php if ($edit): ?><input type="hidden" name="ma_sp" value="<?= $edit['ma_sp'] ?>"><?php endif; ?>
            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label">Tên sản phẩm *</label>
                    <input type="text" name="ten_sp" class="form-control" value="<?= htmlspecialchars($edit['ten_sp'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Loại sản phẩm *</label>
                    <select name="ma_loai" class="form-control" required>
                        <option value="">-- Chọn loại --</option>
                        <?php $loai_list->data_seek(0); while ($l = $loai_list->fetch_assoc()): ?>
                        <option value="<?= $l['ma_loai'] ?>" <?= ($edit && $edit['ma_loai']==$l['ma_loai'])?'selected':'' ?>><?= htmlspecialchars($l['ten_loai']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Đơn vị tính</label>
                    <input type="text" name="don_vi_tinh" class="form-control" value="<?= htmlspecialchars($edit['don_vi_tinh'] ?? 'Cái') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">% Lợi nhuận mặc định</label>
                    <input type="number" name="tl_loi_nhuan" class="form-control" value="<?= $edit['tl_gb'] ?? 20 ?>" step="0.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Trạng thái hiển thị</label>
                    <label class="form-check" style="margin-top:10px;">
                        <input type="checkbox" name="trang_thai" value="1" <?= (!$edit || $edit['trang_thai'])?'checked':'' ?>> Đang bán
                    </label>
                </div>
               
                <div class="form-group full">
                    <label class="form-label">Mô tả sản phẩm</label>
                    <textarea name="mo_ta" class="form-control" rows="3"><?= htmlspecialchars($edit['mo_ta'] ?? '') ?></textarea>
                </div>
                <div class="form-group full">
                    <label class="form-label">Hình ảnh sản phẩm</label>
                    <?php if ($edit && $edit['hinh'] && file_exists(UPLOAD_PATH . $edit['hinh'])): ?>
                    <div style="margin-bottom:8px;display:flex;align-items:center;gap:12px;">
                        <img src="<?= UPLOAD_URL . $edit['hinh'] ?>" style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
                        <label class="form-check"><input type="checkbox" name="delete_image" value="1"> Xóa hình hiện tại</label>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="hinh" class="form-control" accept="image/*">
                    <small class="form-hint">Định dạng: JPG, PNG, WebP. Tối đa 2MB.</small>
                </div>
            </div>
            <div class="d-flex gap-8 mt-12">
                <button type="submit" class="btn btn-primary"><?= $edit ? 'Cập Nhật' : 'Thêm Sản Phẩm' ?></button>
                <a href="products.php" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<form method="GET" class="admin-filter">
    <div class="filter-item flex-1">
        <label>Tìm theo tên</label>
        <input type="text" name="q" placeholder="Tên sản phẩm..." value="<?= htmlspecialchars($f_key) ?>">
    </div>
    <div class="filter-item">
        <label>Loại sản phẩm</label>
        <select name="loai">
            <option value="0">Tất cả</option>
            <?php $loai_list->data_seek(0); while ($l = $loai_list->fetch_assoc()): ?>
            <option value="<?= $l['ma_loai'] ?>" <?= $f_loai==$l['ma_loai']?'selected':'' ?>><?= htmlspecialchars($l['ten_loai']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
    <a href="products.php" class="btn btn-secondary btn-sm">Reset</a>
</form>

<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr><th>Hình</th><th>Mã</th><th>Tên sản phẩm</th><th>Loại</th><th>Giá bán</th><th>Tồn kho</th><th>TT</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
                <?php if (!$san_phams->num_rows): ?>
                <tr class="empty-row"><td colspan="8">Không có sản phẩm nào</td></tr>
                <?php else: while ($sp = $san_phams->fetch_assoc()):
                    $gia = ($sp['gia_von']??0) * (1 + ($sp['tl_gb']??20)/100);
                    $ton = (int)($sp['ton_kho']??0);
                ?>
                <tr>
                    <td>
                        <?php if ($sp['hinh'] && file_exists(UPLOAD_PATH.$sp['hinh'])): ?>
                        <img src="<?= UPLOAD_URL.$sp['hinh'] ?>" class="product-thumb" style="width:45px;height:45px;object-fit:cover;">
                        <?php else: ?><div class="no-thumb">🛋️</div><?php endif; ?>
                    </td>
                    <td class="text-muted">#<?= $sp['ma_sp'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($sp['ten_sp']) ?></strong>
                        <div style="font-size:0.75rem;color:#888;"><?= htmlspecialchars(mb_strimwidth($sp['mo_ta']??'',0,40,'...','UTF-8')) ?></div>
                    </td>
                    <td><?= htmlspecialchars($sp['ten_loai']) ?></td>
                    <td><strong><?= number_format($gia,0,',','.') ?> ₫</strong></td>
                    <td>
                        <?php if ($ton <= 0): ?><span class="badge badge-danger">Hết hàng</span>
                        <?php elseif($ton<=5): ?><span class="badge badge-warning">Sắp hết (<?= $ton ?>)</span>
                        <?php else: ?><span class="badge badge-success"><?= $ton ?></span><?php endif; ?>
                    </td>
                    <td><span class="badge <?= $sp['trang_thai'] ? 'badge-success' : 'badge-secondary' ?>"><?= $sp['trang_thai'] ? 'Đang bán' : 'Đã ẩn' ?></span></td>
                    <td>
                        <div class="action-group">
                            <a href="?edit=<?= $sp['ma_sp'] ?>" class="btn btn-xs btn-info">Sửa</a>
                            <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $sp['ma_sp'] ?>" target="_blank" class="btn btn-xs btn-secondary">Xem</a>
                            
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa hoặc ẩn sản phẩm này?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="ma_sp" value="<?= $sp['ma_sp'] ?>">
                                <button type="submit" class="btn btn-xs btn-danger">Xóa/Ẩn</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>