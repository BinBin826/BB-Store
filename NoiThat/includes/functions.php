<?php
// ============================================================
// Hàm tiện ích chung
// ============================================================

function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// ---- Khách hàng ----
function isLoggedIn() {
    return isset($_SESSION['kh_id']) && $_SESSION['kh_id'] > 0;
}
function requireLogin() {
    if (!isLoggedIn()) redirect(SITE_URL . '/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}
function currentKH($conn) {
    if (!isLoggedIn()) return null;
    $id = (int)$_SESSION['kh_id'];
    $r  = $conn->query("SELECT * FROM khach_hang WHERE ma_kh=$id");
    return $r->fetch_assoc();
}

// ---- Admin ----
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}
function requireAdmin() {
    if (!isAdminLoggedIn()) redirect(SITE_URL . '/admin/login.php');
}

// ---- Sản phẩm ----
function getGiaBan($conn, $ma_sp) {
    $r = $conn->query("SELECT tk.gia_von, gb.tl_loi_nhuan
                        FROM ton_kho tk
                        LEFT JOIN gia_ban gb ON tk.ma_sp = gb.ma_sp
                        WHERE tk.ma_sp = " . (int)$ma_sp);
    if ($row = $r->fetch_assoc()) {
        $gia_von = $row['gia_von'];
        $tl      = $row['tl_loi_nhuan'] ?? 20;
        return $gia_von * (1 + $tl / 100);
    }
    return 0;
}

function getTonKho($conn, $ma_sp) {
    $r = $conn->query("SELECT so_luong FROM ton_kho WHERE ma_sp=" . (int)$ma_sp);
    $row = $r->fetch_assoc();
    return $row ? (int)$row['so_luong'] : 0;
}

function getCartCount($conn) {
    if (!isLoggedIn()) return 0;
    $id = (int)$_SESSION['kh_id'];
    $r  = $conn->query("SELECT SUM(so_luong) as total FROM gio_hang WHERE ma_kh=$id");
    $row = $r->fetch_assoc();
    return (int)($row['total'] ?? 0);
}

function getLoaiSP($conn) {
    return $conn->query("SELECT * FROM loai_sp WHERE trang_thai=1 ORDER BY ten_loai");
}

function productImage($hinh) {
    if ($hinh && file_exists(UPLOAD_PATH . $hinh)) {
        return UPLOAD_URL . $hinh;
    }
    return SITE_URL . '/assets/images/no-image.png';
}

// ---- Cập nhật tồn kho bình quân ----
function updateTonKhoBinhQuan($conn, $ma_sp, $sl_nhap, $gia_nhap) {
    $r = $conn->query("SELECT so_luong, gia_von FROM ton_kho WHERE ma_sp=" . (int)$ma_sp);
    if ($row = $r->fetch_assoc()) {
        $sl_cu   = $row['so_luong'];
        $gia_cu  = $row['gia_von'];
        $sl_moi  = $sl_cu + $sl_nhap;
        $gia_bq  = ($sl_moi > 0) ? (($sl_cu * $gia_cu + $sl_nhap * $gia_nhap) / $sl_moi) : $gia_nhap;
        $conn->query("UPDATE ton_kho SET so_luong=$sl_moi, gia_von=$gia_bq WHERE ma_sp=" . (int)$ma_sp);
    } else {
        $conn->query("INSERT INTO ton_kho (ma_sp, so_luong, gia_von) VALUES (" . (int)$ma_sp . ", $sl_nhap, $gia_nhap)");
    }
}

// ---- Pagination ----
function paginate($total, $page, $per_page, $url_pattern) {
    $total_pages = ceil($total / $per_page);
    if ($total_pages <= 1) return '';
    $html = '<nav class="pagination">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $page) ? ' active' : '';
        $html .= "<a href=\"" . sprintf($url_pattern, $i) . "\" class=\"page-btn$active\">$i</a>";
    }
    $html .= '</nav>';
    return $html;
}

// ---- Flash messages ----
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function getFlash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return "<div class=\"alert alert-{$f['type']}\">{$f['msg']}</div>";
    }
    return '';
}

// ---- Trạng thái đơn hàng ----
function trangThaiDH($status) {
    $map = [
        'moi_dat'     => ['label' => 'Mới đặt',     'class' => 'badge-warning'],
        'da_xac_nhan' => ['label' => 'Đã xác nhận', 'class' => 'badge-info'],
        'da_giao'     => ['label' => 'Đã giao',     'class' => 'badge-success'],
        'da_huy'      => ['label' => 'Đã hủy',      'class' => 'badge-danger'],
    ];
    return $map[$status] ?? ['label' => $status, 'class' => 'badge-secondary'];
}

// ---- Hình thức thanh toán ----
function hinhThucTT($ht) {
    $map = [
        'tien_mat'    => 'Tiền mặt khi nhận hàng',
        'chuyen_khoan'=> 'Chuyển khoản ngân hàng',
        'truc_tuyen'  => 'Thanh toán trực tuyến',
    ];
    return $map[$ht] ?? $ht;
}
