<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) redirect(SITE_URL . '/index.php');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten    = trim($_POST['ho_ten'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $pass      = $_POST['password'] ?? '';
    $pass2     = $_POST['password2'] ?? '';
    $dien_thoai= trim($_POST['dien_thoai'] ?? '');
    $dia_chi   = trim($_POST['dia_chi'] ?? '');
    $phuong    = trim($_POST['phuong_xa'] ?? '');
    $quan      = trim($_POST['quan_huyen'] ?? '');
    $tinh      = trim($_POST['tinh_tp'] ?? '');

    if (!$ho_ten) $errors[] = 'Vui lòng nhập họ tên.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (strlen($pass) < 6) $errors[] = 'Mật khẩu phải ít nhất 6 ký tự.';
    if ($pass !== $pass2) $errors[] = 'Mật khẩu xác nhận không khớp.';
    if (!$dien_thoai || !preg_match('/^[0-9]{10,11}$/', $dien_thoai)) $errors[] = 'Số điện thoại không hợp lệ (10-11 chữ số).';
    if (!$dia_chi) $errors[] = 'Vui lòng nhập địa chỉ.';
    if (!$tinh)   $errors[] = 'Vui lòng nhập tỉnh/thành phố.';

    if (empty($errors)) {
        $em = $conn->real_escape_string($email);
        $exists = $conn->query("SELECT ma_kh FROM khach_hang WHERE email='$em'");
        if ($exists->num_rows) {
            $errors[] = 'Email này đã được đăng ký. <a href="' . SITE_URL . '/auth/login.php">Đăng nhập?</a>';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ht   = $conn->real_escape_string($ho_ten);
            $dt   = $conn->real_escape_string($dien_thoai);
            $dc   = $conn->real_escape_string($dia_chi);
            $px   = $conn->real_escape_string($phuong);
            $qh   = $conn->real_escape_string($quan);
            $tp   = $conn->real_escape_string($tinh);
            $conn->query("INSERT INTO khach_hang (ho_ten,email,mat_khau,dien_thoai,dia_chi,phuong_xa,quan_huyen,tinh_tp)
                VALUES ('$ht','$em','$hash','$dt','$dc','$px','$qh','$tp')");
            $new_id = $conn->insert_id;
            $_SESSION['kh_id']   = $new_id;
            $_SESSION['kh_name'] = $ho_ten;
            setFlash('success', 'Đăng ký thành công! Chào mừng bạn đến với Nội Thất SGN.');
            redirect(SITE_URL . '/index.php');
        }
    }
}

$page_title = 'Đăng Ký | Nội Thất SGN';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-page" style="align-items:flex-start;padding:40px 0;">
    <div class="auth-container" style="max-width:600px;">
        <div class="auth-logo"><a href="<?= SITE_URL ?>">Nội<span>Thất</span> SGN</a></div>

        <div class="form-card">
            <h2>Tạo Tài Khoản</h2>
            <p class="form-subtitle">Đăng ký để mua hàng và theo dõi đơn hàng của bạn.</p>

            <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= $e ?></div><?php endforeach; ?>

            <form method="POST" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Họ và tên *</label>
                        <input type="text" name="ho_ten" class="form-control"
                            placeholder="Nguyễn Văn A"
                            value="<?= htmlspecialchars($_POST['ho_ten'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Số điện thoại *</label>
                        <input type="tel" name="dien_thoai" class="form-control"
                            placeholder="09xxxxxxxx"
                            value="<?= htmlspecialchars($_POST['dien_thoai'] ?? '') ?>"
                            pattern="[0-9]{10,11}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control"
                        placeholder="email@example.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Mật khẩu *</label>
                        <input type="password" name="password" class="form-control"
                            placeholder="Ít nhất 6 ký tự" minlength="6" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Xác nhận mật khẩu *</label>
                        <input type="password" name="password2" class="form-control"
                            placeholder="Nhập lại mật khẩu" required>
                    </div>
                </div>

                <hr style="border:none;border-top:1px solid var(--cream-dark);margin:20px 0;">
                <p style="font-size:0.8rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--gray-mid);margin-bottom:14px;">
                    📍 Địa Chỉ Giao Hàng
                </p>

                <div class="form-group">
                    <label class="form-label">Địa chỉ (số nhà, tên đường) *</label>
                    <input type="text" name="dia_chi" class="form-control"
                        placeholder="123 Đường Nguyễn Huệ"
                        value="<?= htmlspecialchars($_POST['dia_chi'] ?? '') ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Phường/Xã</label>
                        <input type="text" name="phuong_xa" class="form-control"
                            placeholder="Phường Bến Nghé"
                            value="<?= htmlspecialchars($_POST['phuong_xa'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quận/Huyện</label>
                        <input type="text" name="quan_huyen" class="form-control"
                            placeholder="Quận 1"
                            value="<?= htmlspecialchars($_POST['quan_huyen'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tỉnh/Thành phố *</label>
                    <input type="text" name="tinh_tp" class="form-control"
                        placeholder="TP. Hồ Chí Minh"
                        value="<?= htmlspecialchars($_POST['tinh_tp'] ?? '') ?>" required>
                </div>

                <div class="form-actions" style="flex-direction:column;gap:12px;margin-top:24px;">
                    <button type="submit" class="btn-submit" style="width:100%;" onclick="return validateRegister(this.form)">Đăng Ký</button>
                    <p style="text-align:center;font-size:0.88rem;color:var(--gray-mid);">
                        Đã có tài khoản? <a href="<?= SITE_URL ?>/auth/login.php" style="color:var(--walnut);">Đăng nhập</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function validateRegister(form) {
    const phone = form.dien_thoai.value;
    if (!/^[0-9]{10,11}$/.test(phone)) { alert('Số điện thoại không hợp lệ!'); return false; }
    if (form.password.value.length < 6) { alert('Mật khẩu phải ít nhất 6 ký tự!'); return false; }
    if (form.password.value !== form.password2.value) { alert('Mật khẩu xác nhận không khớp!'); return false; }
    return true;
}
</script>
</body>
</html>
