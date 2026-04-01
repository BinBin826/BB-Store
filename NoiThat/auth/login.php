<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) redirect(SITE_URL . '/index.php');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$email) $errors[] = 'Vui lòng nhập email.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (!$pass) $errors[] = 'Vui lòng nhập mật khẩu.';

    if (empty($errors)) {
        $em = $conn->real_escape_string($email);
        $r  = $conn->query("SELECT * FROM khach_hang WHERE email='$em'");
        if ($row = $r->fetch_assoc()) {
            if ($row['trang_thai'] == 0) {
                $errors[] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.';
            } elseif (password_verify($pass, $row['mat_khau'])) {
                $_SESSION['kh_id']   = $row['ma_kh'];
                $_SESSION['kh_name'] = $row['ho_ten'];
                setFlash('success', 'Chào mừng trở lại, <strong>' . htmlspecialchars($row['ho_ten']) . '</strong>!');
                $redirect = $_GET['redirect'] ?? SITE_URL . '/index.php';
                redirect($redirect);
            } else {
                $errors[] = 'Email hoặc mật khẩu không đúng.';
            }
        } else {
            $errors[] = 'Email hoặc mật khẩu không đúng.';
        }
    }
}

$page_title = 'Đăng Nhập | Nội Thất SGN';
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
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-logo"><a href="<?= SITE_URL ?>">Nội<span>Thất</span> SGN</a></div>

        <div class="form-card">
            <h2>Đăng Nhập</h2>
            <p class="form-subtitle">Chào mừng bạn trở lại! Đăng nhập để tiếp tục mua sắm.</p>

            <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= $e ?></div><?php endforeach; ?>
            <?= getFlash() ?>

            <form method="POST" novalidate>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                        placeholder="example@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="••••••••" required minlength="6">
                </div>
                <div class="form-actions" style="flex-direction:column;gap:12px;">
                    <button type="submit" class="btn-submit" style="width:100%;">Đăng Nhập</button>
                    <p style="text-align:center;font-size:0.88rem;color:var(--gray-mid);">
                        Chưa có tài khoản?
                        <a href="<?= SITE_URL ?>/auth/register.php" style="color:var(--walnut);">Đăng ký ngay</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
