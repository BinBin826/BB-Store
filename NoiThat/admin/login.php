<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) redirect(SITE_URL . '/admin/index.php');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$email || !$pass) { $errors[] = 'Vui lòng nhập đầy đủ thông tin.'; }
    else {
        $em = $conn->real_escape_string($email);
        $r  = $conn->query("SELECT * FROM admin WHERE email='$em'");
        if ($row = $r->fetch_assoc()) {
            if (password_verify($pass, $row['mat_khau'])) {
                $_SESSION['admin_id']   = $row['ma_admin'];
                $_SESSION['admin_name'] = $row['ten_admin'];
                redirect(SITE_URL . '/admin/index.php');
            } else { $errors[] = 'Email hoặc mật khẩu không đúng.'; }
        } else { $errors[] = 'Email hoặc mật khẩu không đúng.'; }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Đăng Nhập Quản Trị | Nội Thất SGN</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
    <style>
        body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:linear-gradient(135deg,#1E1A16,#3A2E24);}
        .login-box{background:#fff;border-radius:12px;padding:48px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,0.3);}
        .login-logo{font-family:'Cormorant Garamond',Georgia,serif;font-size:1.8rem;text-align:center;margin-bottom:8px;color:#1A1A1A;}
        .login-logo span{color:#7C5C3A;}
        .login-sub{text-align:center;color:#C8C0B5;font-size:0.82rem;margin-bottom:32px;letter-spacing:0.05em;text-transform:uppercase;}
        .login-box .form-control{background:#F7F2E9;}
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-logo">Nội<span>Thất</span> SGN</div>
    <div class="login-sub">Cổng Quản Trị Hệ Thống</div>

    <?php foreach ($errors as $e): ?><div class="alert alert-error"><?= $e ?></div><?php endforeach; ?>

    <form method="POST">
        <div class="form-group mb-16">
            <label class="form-label">Email Quản Trị</label>
            <input type="email" name="email" class="form-control"
                placeholder="admin@noithat.vn"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="form-group mb-20">
            <label class="form-label">Mật Khẩu</label>
            <input type="password" name="password" class="form-control"
                placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary w-100" style="width:100%;justify-content:center;padding:12px;">
            Đăng Nhập
        </button>
    </form>
    <p style="text-align:center;margin-top:20px;font-size:0.82rem;color:#C8C0B5;">
        <a href="<?= SITE_URL ?>/index.php" style="color:#7C5C3A;">← Về trang chủ</a>
    </p>
</div>
</body>
</html>
