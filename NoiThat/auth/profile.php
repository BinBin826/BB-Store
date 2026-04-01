<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$kh_id = (int)$_SESSION['kh_id'];
$kh    = currentKH($conn);
$errors = []; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_info') {
        $ho_ten = trim($_POST['ho_ten'] ?? '');
        $dt     = trim($_POST['dien_thoai'] ?? '');
        $dc     = trim($_POST['dia_chi'] ?? '');
        $px     = trim($_POST['phuong_xa'] ?? '');
        $qh     = trim($_POST['quan_huyen'] ?? '');
        $tp     = trim($_POST['tinh_tp'] ?? '');

        if (!$ho_ten) $errors[] = 'Vui lòng nhập họ tên.';
        if ($dt && !preg_match('/^[0-9]{10,11}$/', $dt)) $errors[] = 'Số điện thoại không hợp lệ.';

        if (empty($errors)) {
            $ht2 = $conn->real_escape_string($ho_ten);
            $dt2 = $conn->real_escape_string($dt);
            $dc2 = $conn->real_escape_string($dc);
            $px2 = $conn->real_escape_string($px);
            $qh2 = $conn->real_escape_string($qh);
            $tp2 = $conn->real_escape_string($tp);
            $conn->query("UPDATE khach_hang SET ho_ten='$ht2',dien_thoai='$dt2',dia_chi='$dc2',phuong_xa='$px2',quan_huyen='$qh2',tinh_tp='$tp2' WHERE ma_kh=$kh_id");
            $_SESSION['kh_name'] = $ho_ten;
            setFlash('success', 'Cập nhật thông tin thành công!');
            redirect(SITE_URL . '/auth/profile.php');
        }
    }

    if ($action === 'change_pass') {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $cf  = $_POST['confirm_password'] ?? '';

        if (!password_verify($old, $kh['mat_khau'])) $errors[] = 'Mật khẩu cũ không đúng.';
        if (strlen($new) < 6) $errors[] = 'Mật khẩu mới phải ít nhất 6 ký tự.';
        if ($new !== $cf) $errors[] = 'Mật khẩu xác nhận không khớp.';

        if (empty($errors)) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $conn->query("UPDATE khach_hang SET mat_khau='$hash' WHERE ma_kh=$kh_id");
            setFlash('success', 'Đổi mật khẩu thành công!');
            redirect(SITE_URL . '/auth/profile.php');
        }
    }
    $kh = currentKH($conn);
}

$page_title = 'Thông Tin Cá Nhân | Nội Thất SGN';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Tài Khoản Của Tôi</h1>
        <div class="breadcrumb"><a href="<?= SITE_URL ?>">Trang chủ</a><span>/</span><span>Tài khoản</span></div>
    </div>
</div>

<section class="py-section" style="padding:48px 0 80px;">
    <div class="container">
        <?= getFlash() ?>
        <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= $e ?></div><?php endforeach; ?>

        <div class="profile-layout">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="avatar"><?= strtoupper(mb_substr($kh['ho_ten'],0,1,'UTF-8')) ?></div>
                <div class="user-name"><?= htmlspecialchars($kh['ho_ten']) ?></div>
                <div class="user-email"><?= htmlspecialchars($kh['email']) ?></div>
                <nav class="sidebar-nav">
                    <a href="<?= SITE_URL ?>/auth/profile.php" class="active">Thông tin cá nhân</a>
                    <a href="<?= SITE_URL ?>/orders.php">Đơn hàng của tôi</a>
                    <a href="<?= SITE_URL ?>/auth/logout.php">Đăng xuất</a>
                </nav>
            </div>

            <!-- Content -->
            <div>
                <!-- Thông tin cá nhân -->
                <div class="form-card mb-24">
                    <h2 style="font-size:1.4rem;margin-bottom:20px;">Thông Tin Cá Nhân</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_info">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Họ và tên *</label>
                                <input type="text" name="ho_ten" class="form-control"
                                    value="<?= htmlspecialchars($kh['ho_ten']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control"
                                    value="<?= htmlspecialchars($kh['email']) ?>" disabled
                                    style="background:var(--gray-light);cursor:not-allowed;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" name="dien_thoai" class="form-control"
                                value="<?= htmlspecialchars($kh['dien_thoai'] ?? '') ?>"
                                pattern="[0-9]{10,11}">
                        </div>
                        <hr style="border:none;border-top:1px solid var(--cream-dark);margin:18px 0;">
                        <p style="font-size:0.78rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--gray-mid);margin-bottom:14px;">📍 Địa Chỉ Giao Hàng</p>
                        <div class="form-group">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="dia_chi" class="form-control"
                                value="<?= htmlspecialchars($kh['dia_chi'] ?? '') ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Phường/Xã</label>
                                <input type="text" name="phuong_xa" class="form-control"
                                    value="<?= htmlspecialchars($kh['phuong_xa'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Quận/Huyện</label>
                                <input type="text" name="quan_huyen" class="form-control"
                                    value="<?= htmlspecialchars($kh['quan_huyen'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tỉnh/Thành phố</label>
                            <input type="text" name="tinh_tp" class="form-control"
                                value="<?= htmlspecialchars($kh['tinh_tp'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn-submit mt-16">Lưu Thay Đổi</button>
                    </form>
                </div>

                <!-- Đổi mật khẩu -->
                <div class="form-card">
                    <h2 style="font-size:1.4rem;margin-bottom:20px;">Đổi Mật Khẩu</h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="change_pass">
                        <div class="form-group">
                            <label class="form-label">Mật khẩu hiện tại *</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Mật khẩu mới *</label>
                                <input type="password" name="new_password" class="form-control" minlength="6" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Xác nhận mật khẩu *</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-submit mt-16">Đổi Mật Khẩu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
