<?php
$page_title = 'Quản Lý Khách Hàng';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ma_kh  = (int)$_POST['ma_kh'];

    if ($action === 'toggle_lock') {
        $cur = (int)$_POST['cur_status'];
        $conn->query("UPDATE khach_hang SET trang_thai=" . ($cur ? 0 : 1) . " WHERE ma_kh=$ma_kh");
        setFlash('success', $cur ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.');
        redirect(SITE_URL . '/admin/users.php');
    }

    if ($action === 'reset_pass') {
        $new_pass = 'ngt' . $ma_kh . rand(1000,9999);
        $hash     = password_hash($new_pass, PASSWORD_DEFAULT);
        $conn->query("UPDATE khach_hang SET mat_khau='$hash' WHERE ma_kh=$ma_kh");
        setFlash('success', "Đã reset mật khẩu. Mật khẩu mới: <strong>$new_pass</strong>");
        redirect(SITE_URL . '/admin/users.php');
    }

    if ($action === 'add_admin_account') {
        $ten   = trim($_POST['ten_admin'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if ($ten && filter_var($email,FILTER_VALIDATE_EMAIL) && strlen($pass)>=6) {
            $t = $conn->real_escape_string($ten);
            $e = $conn->real_escape_string($email);
            $h = password_hash($pass, PASSWORD_DEFAULT);
            $exist = $conn->query("SELECT ma_kh FROM khach_hang WHERE email='$e'");
            if ($exist->num_rows) { setFlash('error','Email đã tồn tại.'); }
            else {
                $conn->query("INSERT INTO khach_hang (ho_ten,email,mat_khau,trang_thai) VALUES ('$t','$e','$h',1)");
                setFlash('success','Tạo tài khoản khách hàng thành công!');
            }
        } else { setFlash('error','Vui lòng điền đầy đủ và đúng thông tin.'); }
        redirect(SITE_URL . '/admin/users.php');
    }
}

$f_key    = trim($_GET['q'] ?? '');
$f_status = $_GET['status'] ?? '';
$where    = '1=1';
if ($f_key)    $where .= " AND (ho_ten LIKE '%" . $conn->real_escape_string($f_key) . "%' OR email LIKE '%" . $conn->real_escape_string($f_key) . "%')";
if ($f_status !== '') $where .= " AND trang_thai=" . (int)$f_status;

$users   = $conn->query("SELECT kh.*, (SELECT COUNT(*) FROM don_hang WHERE ma_kh=kh.ma_kh) as so_dh FROM khach_hang kh WHERE $where ORDER BY kh.created_at DESC");
$total   = $conn->query("SELECT COUNT(*) FROM khach_hang")->fetch_row()[0];
$locked  = $conn->query("SELECT COUNT(*) FROM khach_hang WHERE trang_thai=0")->fetch_row()[0];
?>

<?= getFlash() ?>
<div class="mb-20">
    <h2 class="page-title">Quản Lý Khách Hàng</h2>
    <p class="page-sub">Tổng <?= $total ?> tài khoản · <?= $locked ?> bị khóa</p>
</div>

<!-- Tạo tài khoản mới -->
<div class="admin-card mb-20">
    <div class="admin-card-header"><h3>➕ Tạo Tài Khoản Khách Hàng</h3></div>
    <div class="admin-card-body">
        <form method="POST">
            <input type="hidden" name="action" value="add_admin_account">
            <div class="form-grid cols-3">
                <div class="form-group">
                    <label class="form-label">Họ và tên</label>
                    <input type="text" name="ten_admin" class="form-control" placeholder="Nguyễn Văn A" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mật khẩu khởi tạo</label>
                    <input type="text" name="password" class="form-control" placeholder="Ít nhất 6 ký tự" minlength="6" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-12">Tạo Tài Khoản</button>
        </form>
    </div>
</div>

<!-- Filter -->
<form method="GET" class="admin-filter">
    <div class="filter-item flex-1"><label>Tìm tên / email</label><input type="text" name="q" placeholder="Tên hoặc email..." value="<?= htmlspecialchars($f_key) ?>"></div>
    <div class="filter-item">
        <label>Trạng thái</label>
        <select name="status">
            <option value="">Tất cả</option>
            <option value="1" <?= $f_status==='1'?'selected':'' ?>>Đang hoạt động</option>
            <option value="0" <?= $f_status==='0'?'selected':'' ?>>Đã khóa</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
    <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-secondary btn-sm">Reset</a>
</form>

<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="data-table">
            <thead><tr><th>#</th><th>Họ tên</th><th>Email</th><th>SĐT</th><th>Địa chỉ</th><th>Đơn hàng</th><th>Đăng ký</th><th>TT</th><th>Thao tác</th></tr></thead>
            <tbody>
                <?php if (!$users->num_rows): ?>
                <tr class="empty-row"><td colspan="9">Không tìm thấy khách hàng nào</td></tr>
                <?php else: while ($kh = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $kh['ma_kh'] ?></td>
                    <td><strong><?= htmlspecialchars($kh['ho_ten']) ?></strong></td>
                    <td><?= htmlspecialchars($kh['email']) ?></td>
                    <td><?= htmlspecialchars($kh['dien_thoai'] ?? '—') ?></td>
                    <td style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($kh['dia_chi']??'') ?>
                        <?php if ($kh['tinh_tp']): ?>, <?= htmlspecialchars($kh['tinh_tp']) ?><?php endif; ?>
                    </td>
                    <td><a href="<?= SITE_URL ?>/admin/orders.php?kh=<?= $kh['ma_kh'] ?>" style="color:var(--walnut);"><?= $kh['so_dh'] ?> đơn</a></td>
                    <td class="text-muted"><?= date('d/m/Y', strtotime($kh['created_at'])) ?></td>
                    <td>
                        <span class="badge <?= $kh['trang_thai'] ? 'badge-success' : 'badge-danger' ?>">
                            <?= $kh['trang_thai'] ? 'Hoạt động' : 'Đã khóa' ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-group">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="toggle_lock">
                                <input type="hidden" name="ma_kh" value="<?= $kh['ma_kh'] ?>">
                                <input type="hidden" name="cur_status" value="<?= $kh['trang_thai'] ?>">
                                <button type="submit" class="btn btn-xs <?= $kh['trang_thai'] ? 'btn-warning' : 'btn-success' ?>">
                                    <?= $kh['trang_thai'] ? 'Khóa' : 'Mở khóa' ?>
                                </button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="reset_pass">
                                <input type="hidden" name="ma_kh" value="<?= $kh['ma_kh'] ?>">
                                <button type="submit" class="btn btn-xs btn-info confirm-delete">Reset MK</button>
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
