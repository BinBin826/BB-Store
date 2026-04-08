<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$kh_id = (int)$_SESSION['kh_id'];
$kh    = currentKH($conn);

// Load cart
$cart_items = []; $tong = 0;
$cart_r = $conn->query("
    SELECT gh.*, sp.ten_sp, sp.hinh, tk.gia_von, tk.so_luong as ton_kho,
           COALESCE(gb.tl_loi_nhuan, sp.tl_loi_nhuan) as tl_loi_nhuan
    FROM gio_hang gh
    JOIN san_pham sp ON gh.ma_sp=sp.ma_sp
    LEFT JOIN ton_kho tk ON gh.ma_sp=tk.ma_sp
    LEFT JOIN gia_ban gb ON gh.ma_sp=gb.ma_sp
    WHERE gh.ma_kh=$kh_id AND sp.trang_thai=1
");
while ($item = $cart_r->fetch_assoc()) {
    $item['gia_ban']    = ($item['gia_von']??0) * (1 + ($item['tl_loi_nhuan']??20)/100);
    $item['thanh_tien'] = $item['gia_ban'] * $item['so_luong'];
    $tong += $item['thanh_tien'];
    $cart_items[] = $item;
}
if (empty($cart_items)) { redirect(SITE_URL . '/cart.php'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addr_type   = $_POST['addr_type'] ?? 'existing';
    $hinh_thuc   = in_array($_POST['hinh_thuc_tt']??'',['tien_mat','chuyen_khoan','truc_tuyen'])
                    ? $_POST['hinh_thuc_tt'] : 'tien_mat';

    if ($addr_type === 'existing') {
        $ho_ten_g  = $kh['ho_ten'];
        $dien_thoai_g = $kh['dien_thoai'] ?? '';
        $dia_chi_g = $kh['dia_chi'] ?? '';
        $phuong    = $kh['phuong_xa'] ?? '';
        $quan      = $kh['quan_huyen'] ?? '';
        $tinh      = $kh['tinh_tp'] ?? '';
    } else {
        $ho_ten_g  = trim($_POST['ho_ten_giao'] ?? '');
        $dien_thoai_g = trim($_POST['dien_thoai_giao'] ?? '');
        $dia_chi_g = trim($_POST['dia_chi_giao'] ?? '');
        $phuong    = trim($_POST['phuong_xa'] ?? '');
        $quan      = trim($_POST['quan_huyen'] ?? '');
        $tinh      = trim($_POST['tinh_tp'] ?? '');

        if (!$ho_ten_g) $errors[] = 'Vui lòng nhập họ tên người nhận.';
        if (!$dien_thoai_g) $errors[] = 'Vui lòng nhập số điện thoại.';
        if (!$dia_chi_g) $errors[] = 'Vui lòng nhập địa chỉ giao hàng.';
        if (!$tinh) $errors[] = 'Vui lòng nhập tỉnh/thành phố.';
    }

    if (empty($errors)) {
        $conn->begin_transaction();
        try {
            $ht = $conn->real_escape_string($hinh_thuc);
            $htn= $conn->real_escape_string($ho_ten_g);
            $dt = $conn->real_escape_string($dien_thoai_g);
            $dc = $conn->real_escape_string($dia_chi_g);
            $px = $conn->real_escape_string($phuong);
            $qh = $conn->real_escape_string($quan);
            $tp = $conn->real_escape_string($tinh);

            $conn->query("INSERT INTO don_hang 
                (ma_kh, ho_ten_giao, dien_thoai_giao, dia_chi_giao, phuong_xa, quan_huyen, tinh_tp, 
                 hinh_thuc_tt, tong_tien, trang_thai) 
                VALUES 
                ($kh_id, '$htn', '$dt', '$dc', '$px', '$qh', '$tp', '$ht', $tong, 'moi_dat')");

            $ma_dh = $conn->insert_id;

            foreach ($cart_items as $item) {
                $ten = $conn->real_escape_string($item['ten_sp']);
                $conn->query("INSERT INTO chitiet_dh 
                    (ma_dh, ma_sp, ten_sp, so_luong, gia_ban) 
                    VALUES 
                    ($ma_dh, {$item['ma_sp']}, '$ten', {$item['so_luong']}, {$item['gia_ban']})");
                
                // === ĐÃ XÓA PHẦN TRỪ TỒN KHO ===
            }

            // Xóa giỏ hàng
            $conn->query("DELETE FROM gio_hang WHERE ma_kh=$kh_id");
            
            $conn->commit();
            redirect(SITE_URL . '/order-success.php?id=' . $ma_dh);

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}

$page_title = 'Đặt Hàng | Nội Thất SGN';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Đặt Hàng</h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Trang chủ</a><span>/</span>
            <a href="<?= SITE_URL ?>/cart.php">Giỏ hàng</a><span>/</span>
            <span>Đặt hàng</span>
        </div>
    </div>
</div>

<section class="py-section" style="padding:48px 0 80px;">
    <div class="container">
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-error"><?= $e ?></div>
        <?php endforeach; ?>

        <form method="POST">
        <div class="cart-layout">
            <!-- Shipping info -->
            <div>
                <div class="form-card mb-24">
                    <h2 style="margin-bottom:20px;">Địa Chỉ Giao Hàng</h2>

                    <!-- Chọn địa chỉ -->
                    <div style="display:flex;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
                        <label class="payment-option" style="flex:1;min-width:180px;">
                            <input type="radio" name="addr_type" value="existing" id="addr_existing" <?= (!isset($_POST['addr_type'])||$_POST['addr_type']==='existing')?'checked':'' ?>>
                            <span class="pay-icon">👤</span>
                            <div class="pay-info">
                                <strong>Địa chỉ tài khoản</strong>
                                <small><?= htmlspecialchars($kh['dia_chi']??'Chưa có địa chỉ') ?></small>
                            </div>
                        </label>
                        <label class="payment-option" style="flex:1;min-width:180px;">
                            <input type="radio" name="addr_type" value="new" id="addr_new" <?= (isset($_POST['addr_type'])&&$_POST['addr_type']==='new')?'checked':'' ?>>
                            <span class="pay-icon">📍</span>
                            <div class="pay-info">
                                <strong>Địa chỉ mới</strong>
                                <small>Nhập địa chỉ giao hàng khác</small>
                            </div>
                        </label>
                    </div>

                    <!-- Existing address display -->
                    <div id="existingFields">
                        <?php if ($kh['dia_chi']): ?>
                        <div class="bank-info">
                            <strong><?= htmlspecialchars($kh['ho_ten']) ?></strong> — <?= htmlspecialchars($kh['dien_thoai']??'') ?><br>
                            <?= htmlspecialchars($kh['dia_chi']??'') ?>,
                            <?= htmlspecialchars($kh['phuong_xa']??'') ?>,
                            <?= htmlspecialchars($kh['quan_huyen']??'') ?>,
                            <?= htmlspecialchars($kh['tinh_tp']??'') ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">Tài khoản chưa có địa chỉ. Vui lòng chọn "Địa chỉ mới".</div>
                        <?php endif; ?>
                    </div>

                    <!-- New address fields -->
                    <div id="newFields" style="display:none;">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="ho_ten_giao">Họ tên người nhận *</label>
                                <input type="text" id="ho_ten_giao" name="ho_ten_giao" class="form-control"
                                    value="<?= htmlspecialchars($_POST['ho_ten_giao']??$kh['ho_ten']) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="dien_thoai_giao">Số điện thoại *</label>
                                <input type="tel" id="dien_thoai_giao" name="dien_thoai_giao" class="form-control"
                                    value="<?= htmlspecialchars($_POST['dien_thoai_giao']??$kh['dien_thoai']??'') ?>"
                                    pattern="[0-9]{10,11}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="dia_chi_giao">Địa chỉ *</label>
                            <input type="text" id="dia_chi_giao" name="dia_chi_giao" class="form-control"
                                placeholder="Số nhà, tên đường"
                                value="<?= htmlspecialchars($_POST['dia_chi_giao']??$kh['dia_chi']??'') ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="phuong_xa">Phường/Xã</label>
                                <input type="text" id="phuong_xa" name="phuong_xa" class="form-control"
                                    value="<?= htmlspecialchars($_POST['phuong_xa']??$kh['phuong_xa']??'') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="quan_huyen">Quận/Huyện</label>
                                <input type="text" id="quan_huyen" name="quan_huyen" class="form-control"
                                    value="<?= htmlspecialchars($_POST['quan_huyen']??$kh['quan_huyen']??'') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="tinh_tp">Tỉnh/Thành phố *</label>
                            <input type="text" id="tinh_tp" name="tinh_tp" class="form-control"
                                value="<?= htmlspecialchars($_POST['tinh_tp']??$kh['tinh_tp']??'') ?>">
                        </div>
                    </div>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="form-card">
                    <h2 style="margin-bottom:20px;">Phương Thức Thanh Toán</h2>
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="hinh_thuc_tt" value="tien_mat" checked>
                            <span class="pay-icon">💵</span>
                            <div class="pay-info">
                                <strong>Tiền mặt khi nhận hàng (COD)</strong>
                                <small>Thanh toán khi nhận được hàng</small>
                            </div>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="hinh_thuc_tt" value="chuyen_khoan">
                            <span class="pay-icon">🏦</span>
                            <div class="pay-info">
                                <strong>Chuyển khoản ngân hàng</strong>
                                <small>Chuyển khoản trước khi giao hàng</small>
                            </div>
                        </label>
                        <div id="bankInfo" style="display:none;">
                            <div class="bank-info">
                                <strong>Thông tin chuyển khoản:</strong><br>
                                Ngân hàng: <strong>Vietcombank</strong><br>
                                Số tài khoản: <strong>1234567890</strong><br>
                                Chủ tài khoản: <strong>CONG TY TNHH NOI THAT SGN</strong><br>
                                Nội dung: <strong>NOITHAT [Tên của bạn] [SĐT]</strong>
                            </div>
                        </div>
                        <label class="payment-option">
                            <input type="radio" name="hinh_thuc_tt" value="truc_tuyen">
                            <span class="pay-icon">💳</span>
                            <div class="pay-info">
                                <strong>Thanh toán trực tuyến</strong>
                                <small>Visa, Mastercard, MoMo, ZaloPay...</small>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Order summary -->
            <div class="cart-summary">
                <h3>Đơn Hàng Của Bạn</h3>
                <?php foreach ($cart_items as $item): ?>
                <div class="summary-row">
                    <span><?= htmlspecialchars($item['ten_sp']) ?> × <?= $item['so_luong'] ?></span>
                    <span><?= formatMoney($item['thanh_tien']) ?></span>
                </div>
                <?php endforeach; ?>
                <div class="summary-total">
                    <span>Tổng đơn hàng</span>
                    <span><?= formatMoney($tong) ?></span>
                </div>
                <button type="submit" class="btn-checkout" onclick="return validateForm()">
                    ✓ Xác Nhận Đặt Hàng
                </button>
                <p style="font-size:0.75rem;color:var(--gray-mid);text-align:center;margin-top:12px;">
                    Bằng cách đặt hàng, bạn đồng ý với điều khoản dịch vụ của chúng tôi.
                </p>
            </div>
        </div>
        </form>
    </div>
</section>

<script>
function validateForm() {
    const addrType = document.querySelector('input[name="addr_type"]:checked').value;
    if (addrType === 'new') {
        const fields = ['ho_ten_giao','dien_thoai_giao','dia_chi_giao','tinh_tp'];
        for (const f of fields) {
            const el = document.getElementById(f);
            if (!el || !el.value.trim()) {
                alert('Vui lòng điền đầy đủ thông tin địa chỉ giao hàng.');
                el && el.focus();
                return false;
            }
        }
        const phone = document.getElementById('dien_thoai_giao').value;
        if (!/^[0-9]{10,11}$/.test(phone)) {
            alert('Số điện thoại không hợp lệ (10-11 chữ số).');
            return false;
        }
    }
    return true;
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
