<?php
$page_title = 'Tồn Kho & Báo Cáo';
require_once __DIR__ . '/includes/header.php';

// Threshold setting
$threshold = (int)($_POST['threshold'] ?? $_GET['threshold'] ?? 5);

// Filter by category and time
$f_loai   = (int)($_GET['loai'] ?? 0);
$f_from   = $_GET['from'] ?? '';
$f_to     = $_GET['to']   ?? '';
$f_date   = $_GET['date'] ?? date('Y-m-d');

// ===== TỒN KHO THEO LOẠI TẠI THỜI ĐIỂM =====
// Công thức: Tồn tại ngày = Tồn hiện tại - Nhập sau ngày đó + Xuất sau ngày đó
$loai_filter = $f_loai ? "AND sp.ma_loai=$f_loai" : '';
$ton_query = "
    SELECT sp.ma_sp, sp.ten_sp, ls.ten_loai, sp.don_vi_tinh,
           COALESCE(SUM(CASE WHEN pn.trang_thai=1 AND pn.ngay_nhap<='$f_date' THEN cn.so_luong ELSE 0 END), 0) as tong_nhap,
           COALESCE((SELECT SUM(cd.so_luong) FROM chitiet_dh cd JOIN don_hang dh ON cd.ma_dh=dh.ma_dh WHERE cd.ma_sp=sp.ma_sp AND dh.trang_thai='da_giao' AND DATE(dh.ngay_dat)<='$f_date'), 0) as tong_ban,
           COALESCE(tk.so_luong, 0) as ton_hien_tai,
           (
               COALESCE(tk.so_luong, 0)
               - COALESCE(SUM(CASE WHEN pn.trang_thai=1 AND pn.ngay_nhap>'$f_date' THEN cn.so_luong ELSE 0 END), 0)
               + COALESCE((SELECT SUM(cd2.so_luong) FROM chitiet_dh cd2 JOIN don_hang dh2 ON cd2.ma_dh=dh2.ma_dh WHERE cd2.ma_sp=sp.ma_sp AND dh2.trang_thai='da_giao' AND DATE(dh2.ngay_dat)>'$f_date'), 0)
           ) as ton_tai_ngay
    FROM san_pham sp
    JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai
    LEFT JOIN ton_kho tk ON sp.ma_sp=tk.ma_sp
    LEFT JOIN chitiet_nhap cn ON sp.ma_sp=cn.ma_sp
    LEFT JOIN phieu_nhap pn ON cn.ma_phieu=pn.ma_phieu
    WHERE sp.trang_thai=1 $loai_filter
    GROUP BY sp.ma_sp
    ORDER BY ls.ten_loai, sp.ten_sp
";
$ton_result = $conn->query($ton_query);
$ton_data = [];
while ($row = $ton_result->fetch_assoc()) {
    $ton_data[] = $row;
}

// ===== BÁO CÁO NHẬP-XUẤT THEO KHOẢNG THỜI GIAN =====
$bao_cao = null;
if ($f_from && $f_to) {
    $bao_cao = $conn->query("
        SELECT sp.ma_sp, sp.ten_sp, ls.ten_loai, sp.don_vi_tinh,
            COALESCE((SELECT SUM(cn.so_luong) FROM chitiet_nhap cn JOIN phieu_nhap pn ON cn.ma_phieu=pn.ma_phieu WHERE cn.ma_sp=sp.ma_sp AND pn.trang_thai=1 AND pn.ngay_nhap BETWEEN '$f_from' AND '$f_to'), 0) as tong_nhap_kc,
            COALESCE((SELECT SUM(cd.so_luong) FROM chitiet_dh cd JOIN don_hang dh ON cd.ma_dh=dh.ma_dh WHERE cd.ma_sp=sp.ma_sp AND dh.trang_thai='da_giao' AND DATE(dh.ngay_dat) BETWEEN '$f_from' AND '$f_to'), 0) as tong_xuat_kc,
            tk.so_luong as ton_hien_tai, tk.gia_von,
            COALESCE(gb.tl_loi_nhuan, sp.tl_loi_nhuan) as tl_loi_nhuan,
            (
                COALESCE(tk.so_luong, 0)
                - COALESCE((SELECT SUM(cn2.so_luong) FROM chitiet_nhap cn2 JOIN phieu_nhap pn2 ON cn2.ma_phieu=pn2.ma_phieu WHERE cn2.ma_sp=sp.ma_sp AND pn2.trang_thai=1 AND pn2.ngay_nhap BETWEEN '$f_from' AND '$f_to'), 0)
                + COALESCE((SELECT SUM(cd2.so_luong) FROM chitiet_dh cd2 JOIN don_hang dh2 ON cd2.ma_dh=dh2.ma_dh WHERE cd2.ma_sp=sp.ma_sp AND dh2.trang_thai='da_giao' AND DATE(dh2.ngay_dat) BETWEEN '$f_from' AND '$f_to'), 0)
            ) as ton_dau_ky
        FROM san_pham sp
        JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai
        LEFT JOIN ton_kho tk ON sp.ma_sp=tk.ma_sp
        LEFT JOIN gia_ban gb ON sp.ma_sp=gb.ma_sp
        WHERE sp.trang_thai=1 $loai_filter
        ORDER BY ls.ten_loai, sp.ten_sp
    ");
}

$loai_list = $conn->query("SELECT * FROM loai_sp WHERE trang_thai=1 ORDER BY ten_loai");
?>

<?= getFlash() ?>
<div class="mb-20">
    <h2 class="page-title">Tồn Kho & Báo Cáo</h2>
    <p class="page-sub">Tra cứu tồn kho và báo cáo nhập–xuất theo thời gian</p>
</div>

<!-- TRA CỨU TỒN KHO -->
<div class="admin-card mb-24">
    <div class="admin-card-header"><h3>📋 Tra Cứu Tồn Kho Tại Thời Điểm</h3></div>
    <div class="admin-card-body">
        <form method="GET" class="d-flex gap-8 align-center flex-wrap mb-16">
            <div class="filter-item">
                <label>Thời điểm tra cứu</label>
                <input type="date" name="date" value="<?= $f_date ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="filter-item">
                <label>Theo loại</label>
                <select name="loai">
                    <option value="0">Tất cả</option>
                    <?php while ($l = $loai_list->fetch_assoc()): ?>
                    <option value="<?= $l['ma_loai'] ?>" <?= $f_loai==$l['ma_loai']?'selected':'' ?>><?= htmlspecialchars($l['ten_loai']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="padding-top:20px;display:flex;gap:8px;align-items:center;">
                <label style="font-size:0.78rem;white-space:nowrap;">Cảnh báo khi ≤</label>
                <input type="number" name="threshold" value="<?= $threshold ?>" min="1" style="width:70px;" class="form-control">
                <span style="font-size:0.82rem;">đvt</span>
            </div>
            <div style="padding-top:20px;">
                <button type="submit" class="btn btn-primary btn-sm">Tra Cứu</button>
            </div>
        </form>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th><th>Loại</th><th>ĐVT</th>
                    <!--
                    <th>Tổng nhập (≤<?= date('d/m/Y',strtotime($f_date)) ?>)</th>
                    <th>Tổng xuất (≤<?= date('d/m/Y',strtotime($f_date)) ?>)</th>
                    -->
                    <th>Tồn tại ngày</th><th>Cảnh báo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ton_data as $row): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['ten_sp']) ?></strong></td>
                    <td class="text-muted"><?= htmlspecialchars($row['ten_loai']) ?></td>
                    <td><?= htmlspecialchars($row['don_vi_tinh']) ?></td>
                    <!--
                    <td><?= number_format($row['tong_nhap']) ?></td>
                    <td><?= number_format($row['tong_ban']) ?></td>
                    -->
                    <td>
                        <?php $ton = $row['ton_tai_ngay']; ?>
                        <?php if ($ton <= 0): ?><span class="out-stock">0 — Hết hàng</span>
                        <?php elseif ($ton <= $threshold): ?><span class="low-stock"><?= $ton ?></span>
                        <?php else: ?><span class="in-stock"><?= $ton ?></span><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($ton <= 0): ?><span class="badge badge-danger">🚨 Hết hàng</span>
                        <?php elseif ($ton <= $threshold): ?><span class="badge badge-warning">⚠ Sắp hết</span>
                        <?php else: ?><span class="badge badge-success">✓ Đủ hàng</span><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- BÁO CÁO NHẬP-XUẤT THEO KHOẢNG THỜI GIAN -->
<div class="admin-card">
    <div class="admin-card-header"><h3>📊 Báo Cáo Nhập–Xuất Theo Khoảng Thời Gian</h3></div>
    <div class="admin-card-body">
        <form method="GET" class="d-flex gap-8 align-center flex-wrap mb-16">
            <input type="hidden" name="date" value="<?= $f_date ?>">
            <input type="hidden" name="threshold" value="<?= $threshold ?>">
            <input type="hidden" name="loai" value="<?= $f_loai ?>">
            <div class="filter-item"><label>Từ ngày</label><input type="date" name="from" value="<?= $f_from ?>"></div>
            <div class="filter-item"><label>Đến ngày</label><input type="date" name="to" value="<?= $f_to ?>"></div>
            <div style="padding-top:20px;">
                <button type="submit" class="btn btn-primary btn-sm">Xem Báo Cáo</button>
            </div>
        </form>

        <?php if ($bao_cao): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Loại</th>
                    <th>Tồn đầu kỳ</th>
                    <th>Nhập trong kỳ</th>
                    <th>Xuất trong kỳ</th>
                    <th>Tồn cuối kỳ</th>
                    <th>Giá vốn BQ</th>
                    <th>Giá bán</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_nhap = $total_xuat = 0;
                while ($row = $bao_cao->fetch_assoc()):
                    $gia_ban  = ($row['gia_von']??0) * (1 + ($row['tl_loi_nhuan']??20)/100);
                    $total_nhap += $row['tong_nhap_kc'];
                    $total_xuat += $row['tong_xuat_kc'];
                    $mid      = $row['ma_sp'];
                    $ton_dau  = $row['ton_dau_ky'];
                    $ton_cuoi = $ton_dau + $row['tong_nhap_kc'] - $row['tong_xuat_kc'];

                    $detail_nhap = $conn->query("
                        SELECT pn.ma_phieu, pn.ngay_nhap, cn.so_luong, cn.gia_nhap
                        FROM chitiet_nhap cn
                        JOIN phieu_nhap pn ON cn.ma_phieu=pn.ma_phieu
                        WHERE cn.ma_sp=$mid AND pn.trang_thai=1
                        AND pn.ngay_nhap BETWEEN '$f_from' AND '$f_to'
                        ORDER BY pn.ngay_nhap
                    ");

                    $detail_xuat = $conn->query("
                        SELECT dh.ma_dh, dh.ngay_dat, cd.so_luong
                        FROM chitiet_dh cd
                        JOIN don_hang dh ON cd.ma_dh=dh.ma_dh
                        WHERE cd.ma_sp=$mid AND dh.trang_thai='da_giao'
                        AND DATE(dh.ngay_dat) BETWEEN '$f_from' AND '$f_to'
                        ORDER BY dh.ngay_dat
                    ");
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['ten_sp']) ?></strong></td>
                    <td class="text-muted"><?= htmlspecialchars($row['ten_loai']) ?></td>
                    <td><?= number_format($ton_dau) ?></td>
                    <td><span style="color:var(--green);font-weight:600;">+<?= $row['tong_nhap_kc'] ?></span></td>
                    <td><span style="color:var(--red);font-weight:600;">−<?= $row['tong_xuat_kc'] ?></span></td>
                    <td>
                        <?php if ($ton_cuoi<=0): ?><span class="out-stock">0</span>
                        <?php elseif($ton_cuoi<=$threshold): ?><span class="low-stock"><?= $ton_cuoi ?></span>
                        <?php else: ?><span class="in-stock"><?= $ton_cuoi ?></span><?php endif; ?>
                    </td>
                    <td><?= number_format($row['gia_von']??0,0,',','.') ?> ₫</td>
                    <td class="fw-600 text-walnut"><?= number_format($gia_ban,0,',','.') ?> ₫</td>
                    <td>
                        <button type="button" class="btn btn-xs btn-info" onclick="toggleDetail('d<?= $mid ?>')">▼ Chi tiết</button>
                    </td>
                </tr>
                <!-- Hàng chi tiết nhập/xuất -->
                <tr id="d<?= $mid ?>" style="display:none;background:#f9f9f9;">
                    <td colspan="9" style="padding:12px 24px;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <!-- Phiếu nhập -->
                            <div>
                                <strong style="color:var(--green);">📥 Phiếu Nhập</strong>
                                <table style="width:100%;margin-top:8px;font-size:0.85rem;border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#e8f5e9;">
                                            <th style="padding:6px 10px;text-align:left;">Phiếu #</th>
                                            <th style="padding:6px 10px;text-align:left;">Ngày nhập</th>
                                            <th style="padding:6px 10px;text-align:left;">SL</th>
                                            <th style="padding:6px 10px;text-align:left;">Giá nhập</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!$detail_nhap->num_rows): ?>
                                        <tr><td colspan="4" style="padding:8px 10px;color:#999;">Không có phiếu nhập</td></tr>
                                    <?php else: while ($dn = $detail_nhap->fetch_assoc()): ?>
                                        <tr style="border-bottom:1px solid #eee;">
                                            <td style="padding:6px 10px;">#<?= $dn['ma_phieu'] ?></td>
                                            <td style="padding:6px 10px;"><?= date('d/m/Y', strtotime($dn['ngay_nhap'])) ?></td>
                                            <td style="padding:6px 10px;color:var(--green);font-weight:600;">+<?= $dn['so_luong'] ?></td>
                                            <td style="padding:6px 10px;"><?= number_format($dn['gia_nhap'],0,',','.') ?> ₫</td>
                                        </tr>
                                    <?php endwhile; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Đơn hàng xuất -->
                            <div>
                                <strong style="color:var(--red);">📤 Đơn Hàng Xuất</strong>
                                <table style="width:100%;margin-top:8px;font-size:0.85rem;border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#fdecea;">
                                            <th style="padding:6px 10px;text-align:left;">Đơn #</th>
                                            <th style="padding:6px 10px;text-align:left;">Ngày đặt</th>
                                            <th style="padding:6px 10px;text-align:left;">SL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!$detail_xuat->num_rows): ?>
                                        <tr><td colspan="3" style="padding:8px 10px;color:#999;">Không có đơn hàng</td></tr>
                                    <?php else: while ($dx = $detail_xuat->fetch_assoc()): ?>
                                        <tr style="border-bottom:1px solid #eee;">
                                            <td style="padding:6px 10px;">#<?= $dx['ma_dh'] ?></td>
                                            <td style="padding:6px 10px;"><?= date('d/m/Y', strtotime($dx['ngay_dat'])) ?></td>
                                            <td style="padding:6px 10px;color:var(--red);font-weight:600;">−<?= $dx['so_luong'] ?></td>
                                        </tr>
                                    <?php endwhile; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                <tr style="background:var(--gray-light);">
                    <td colspan="3" class="fw-600">TỔNG CỘNG TRONG KỲ</td>
                    <td class="fw-600" style="color:var(--green);">+<?= number_format($total_nhap) ?></td>
                    <td class="fw-600" style="color:var(--red);">−<?= number_format($total_xuat) ?></td>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted" style="text-align:center;padding:32px;">Chọn khoảng thời gian để xem báo cáo nhập–xuất.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleDetail(id) {
    const row = document.getElementById(id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>