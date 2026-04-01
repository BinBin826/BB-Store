<?php $base = defined('SITE_URL') ? SITE_URL : ''; ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo">Nội<span>Thất</span> SGN</div>
                <p>Không gian sống của bạn xứng đáng được tốt hơn. Chúng tôi mang đến những sản phẩm nội thất chất lượng cao với thiết kế tinh tế, bền đẹp theo thời gian.</p>
            </div>
            <div class="footer-col">
                <h4>Sản Phẩm</h4>
                <a href="<?= $base ?>/products.php?loai=1">Phòng Khách</a>
                <a href="<?= $base ?>/products.php?loai=2">Phòng Ngủ</a>
                <a href="<?= $base ?>/products.php?loai=3">Phòng Ăn</a>
                <a href="<?= $base ?>/products.php?loai=4">Phòng Làm Việc</a>
                <a href="<?= $base ?>/products.php?loai=5">Phòng Bếp</a>
            </div>
            <div class="footer-col">
                <h4>Tài Khoản</h4>
                <a href="<?= $base ?>/auth/login.php">Đăng nhập</a>
                <a href="<?= $base ?>/auth/register.php">Đăng ký</a>
                <a href="<?= $base ?>/auth/profile.php">Thông tin cá nhân</a>
                <a href="<?= $base ?>/orders.php">Đơn hàng của tôi</a>
            </div>
            <div class="footer-col">
                <h4>Liên Hệ</h4>
                <a href="#">📍 273 An Dương Vương, Q.5</a>
                <a href="#">📞 (028) 3838 5555</a>
                <a href="#">✉️ info@noithatsgn.vn</a>
                <a href="#">🕐 T2–T7: 8:00 – 20:00</a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2025 Nội Thất Sài Gòn. Đồ Án Web – Trường ĐH Sài Gòn.</span>
            <span>Designed with ♥ for SGU</span>
        </div>
    </div>
</footer>
<script src="<?= $base ?>/assets/js/main.js"></script>
</body>
</html>
