<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$per_page = 12;
$page     = max(1, (int)($_GET['page'] ?? 1));
$keyword  = trim($_GET['q'] ?? '');
$loai     = (int)($_GET['loai'] ?? 0);
$gia_min  = (float)($_GET['gia_min'] ?? 0);
$gia_max  = (float)($_GET['gia_max'] ?? 0);
$advanced = isset($_GET['advanced']);
$offset   = ($page - 1) * $per_page;

// 1. Định nghĩa biểu thức tính giá bán (Dùng chung cho WHERE và SELECT)
// Mặc định lợi nhuận là 20% nếu không có thiết lập nào khác
$price_expr = "(COALESCE(tk.gia_von, 0) * (1 + COALESCE(gb.tl_loi_nhuan, sp.tl_loi_nhuan, 20)/100))";

// 2. Xây dựng điều kiện WHERE
$where = "sp.trang_thai=1 AND ls.trang_thai=1";

if ($keyword !== '') {
    $kw = $conn->real_escape_string($keyword);
    $where .= " AND sp.ten_sp LIKE '%$kw%'";
}

if ($loai > 0) {
    $where .= " AND sp.ma_loai=$loai";
}

if ($gia_min > 0) {
    $where .= " AND $price_expr >= $gia_min";
}

if ($gia_max > 0) {
    $where .= " AND $price_expr <= $gia_max";
}

// 3. Xây dựng SQL chính
$base_sql = "FROM san_pham sp
    JOIN loai_sp ls ON sp.ma_loai=ls.ma_loai
    LEFT JOIN ton_kho tk ON sp.ma_sp=tk.ma_sp
    LEFT JOIN gia_ban gb ON sp.ma_sp=gb.ma_sp
    WHERE $where";

$select_cols = "sp.*, ls.ten_loai, tk.so_luong as ton_kho, tk.gia_von,
    COALESCE(gb.tl_loi_nhuan, sp.tl_loi_nhuan, 20) as tl_loi_nhuan,
    $price_expr as gia_ban_calculated";

$has_search = $keyword !== '' || $loai > 0 || $gia_min > 0 || $gia_max > 0;

$total = 0; 
$san_phams = null;

if ($has_search) {
    // Đếm tổng số dòng
    $count_sql = "SELECT COUNT(*) $base_sql";
    $cr = $conn->query($count_sql);
    $total = (int)$cr->fetch_row()[0];

    // Lấy dữ liệu phân trang
    $data_sql = "SELECT $select_cols $base_sql ORDER BY sp.ten_sp LIMIT $per_page OFFSET $offset";
    $san_phams = $conn->query($data_sql);
}

// Lấy danh sách danh mục cho filter
$loai_list_all = $conn->query("SELECT * FROM loai_sp WHERE trang_thai=1 ORDER BY ten_loai");

// Tạo link phân trang
$url_params = http_build_query(array_filter([
    'q' => $keyword, 
    'loai' => $loai, 
    'gia_min' => $gia_min, 
    'gia_max' => $gia_max, 
    'advanced' => $advanced ? 1 : 0
]));
$url_pattern = SITE_URL . '/search.php?' . $url_params . '&page=%d';

$page_title = 'Tìm Kiếm Sản Phẩm | Nội Thất SGN';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Tìm Kiếm Sản Phẩm</h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>">Trang chủ</a><span>/</span><span>Tìm kiếm</span>
        </div>
    </div>
</div>

<section class="py-section" style="padding:48px 0 80px;">
    <div class="container">

        <form method="GET" action="" class="filter-bar" style="flex-direction:column;align-items:stretch;gap:16px;">
            <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <div class="filter-group flex-1">
                    <label>🔍 Tìm theo tên sản phẩm</label>
                    <input type="text" name="q" placeholder="Nhập tên sản phẩm..." value="<?= htmlspecialchars($keyword) ?>" class="filter-group input">
                </div>
                <button type="submit" class="btn-search">Tìm Kiếm</button>
                <a href="<?= SITE_URL ?>/search.php" class="btn-reset">Xóa</a>
                <button type="button" id="advancedToggle" class="btn-reset" style="color:var(--walnut);">
                    <?= ($loai > 0 || $gia_min > 0 || $gia_max > 0 || $advanced) ? '− Thu gọn' : '+ Tìm nâng cao' ?>
                </button>
            </div>

            <div id="advancedFields" style="display:<?= ($loai > 0 || $gia_min > 0 || $gia_max > 0 || $advanced) ? 'flex' : 'none' ?>;gap:12px;flex-wrap:wrap;align-items:flex-end;padding-top:16px;border-top:1px solid #eee;">
                <div class="filter-group">
                    <label>Danh Mục</label>
                    <select name="loai">
                        <option value="0">Tất cả danh mục</option>
                        <?php while ($l = $loai_list_all->fetch_assoc()): ?>
                        <option value="<?= $l['ma_loai'] ?>" <?= $loai == $l['ma_loai'] ? 'selected' : '' ?>><?= htmlspecialchars($l['ten_loai']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Giá Từ (₫)</label>
                    <input type="number" name="gia_min" placeholder="0" value="<?= $gia_min ?: '' ?>" min="0">
                </div>
                <div class="filter-group">
                    <label>Giá Đến (₫)</label>
                    <input type="number" name="gia_max" placeholder="Không giới hạn" value="<?= $gia_max ?: '' ?>" min="0">
                </div>
                <input type="hidden" name="advanced" value="1">
            </div>
        </form>

        <?php if (!$has_search): ?>
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h3>Nhập từ khóa để tìm kiếm</h3>
                <p>Bạn có thể tìm theo tên sản phẩm, danh mục hoặc khoảng giá.</p>
            </div>

        <?php elseif ($total === 0): ?>
            <div class="empty-state">
                <div class="empty-icon">😔</div>
                <h3>Không tìm thấy sản phẩm nào</h3>
                <p>Thử tìm kiếm với từ khóa khác hoặc điều chỉnh bộ lọc.</p>
                <a href="<?= SITE_URL ?>/products.php" class="btn-primary">Xem tất cả sản phẩm</a>
            </div>

        <?php else: ?>
            <p class="text-muted mb-24" style="font-size:0.88rem;">
                Tìm thấy <strong><?= $total ?></strong> sản phẩm
                <?= $keyword ? " cho \"<strong>" . htmlspecialchars($keyword) . "</strong>\"" : '' ?>
            </p>
            
            <div class="product-grid">
                <?php while ($sp = $san_phams->fetch_assoc()): 
                    $gia_hien_thi = $sp['gia_ban_calculated'];
                    $ton = (int)($sp['ton_kho'] ?? 0);
                ?>
                <div class="product-card">
                    <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $sp['ma_sp'] ?>" class="product-img">
                        <?php if ($sp['hinh'] && file_exists(UPLOAD_PATH . $sp['hinh'])): ?>
                            <img src="<?= UPLOAD_URL . $sp['hinh'] ?>" alt="<?= htmlspecialchars($sp['ten_sp']) ?>">
                        <?php else: ?>
                            <span class="no-img">🛋️</span>
                        <?php endif; ?>
                        
                        <?php if ($ton <= 0): ?>
                            <span class="out-of-stock-badge">Hết hàng</span>
                        <?php elseif ($ton <= 3): ?>
                            <span class="low-stock-badge">Sắp hết</span>
                        <?php endif; ?>
                    </a>
                    <div class="product-info">
                        <div class="product-category"><?= htmlspecialchars($sp['ten_loai']) ?></div>
                        <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $sp['ma_sp'] ?>" class="product-name">
                            <?= htmlspecialchars($sp['ten_sp']) ?>
                        </a>
                        <div class="product-price"><?= formatMoney($gia_hien_thi) ?></div>
                        <div class="product-actions">
                            <?php if ($ton > 0): ?>
                            <form method="POST" action="<?= SITE_URL ?>/cart.php" style="flex:1">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="ma_sp" value="<?= $sp['ma_sp'] ?>">
                                <button type="submit" class="btn-cart">+ Giỏ Hàng</button>
                            </form>
                            <?php else: ?>
                                <button class="btn-cart" disabled style="flex:1">Hết Hàng</button>
                            <?php endif; ?>
                            <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $sp['ma_sp'] ?>" class="btn-detail">🔍</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="pagination-wrapper" style="margin-top: 40px;">
                <?= paginate($total, $page, $per_page, $url_pattern) ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Script bổ sung để Toggle phần tìm kiếm nâng cao
document.getElementById('advancedToggle')?.addEventListener('click', function() {
    const fields = document.getElementById('advancedFields');
    const isHidden = fields.style.display === 'none';
    fields.style.display = isHidden ? 'flex' : 'none';
    this.innerText = isHidden ? '− Thu gọn' : '+ Tìm nâng cao';
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>