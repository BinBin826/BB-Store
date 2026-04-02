<?php
// ============================================================
// Cấu hình kết nối CSDL
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'noithat');
define('SITE_URL', 'http://localhost/NoiThat');
define('UPLOAD_PATH', __DIR__ . '/../uploads/products/');
define('UPLOAD_URL', SITE_URL . '/uploads/products/');
define('LOW_STOCK_DEFAULT', 5);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('<div style="padding:20px;background:#fee;color:#c00;font-family:sans-serif">Lỗi kết nối CSDL: ' . $conn->connect_error . '</div>');
}
$conn->set_charset('utf8mb4');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
