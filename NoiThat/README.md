# 🛋️ Nội Thất SGN — Website Bán Đồ Nội Thất

**Đồ Án Web 1 & Web 2 — Trường Đại học Sài Gòn**  
Học phần: Lập trình Web và Ứng dụng / Nâng cao

---

## 📁 Cấu Trúc Thư Mục

```
NoiThat/
├── config/
│   └── db.php              ← Cấu hình CSDL
├── includes/
│   ├── functions.php       ← Hàm tiện ích chung
│   ├── header.php          ← Header khách hàng
│   └── footer.php          ← Footer khách hàng
├── auth/
│   ├── login.php           ← Đăng nhập khách hàng
│   ├── register.php        ← Đăng ký
│   ├── profile.php         ← Thông tin cá nhân
│   └── logout.php
├── admin/
│   ├── includes/
│   │   ├── header.php      ← Layout admin
│   │   └── footer.php
│   ├── login.php           ← Đăng nhập ADMIN (URL riêng)
│   ├── logout.php
│   ├── index.php           ← Dashboard
│   ├── categories.php      ← Loại sản phẩm
│   ├── products.php        ← Sản phẩm
│   ├── imports.php         ← Nhập hàng (bình quân)
│   ├── pricing.php         ← Giá bán
│   ├── orders.php          ← Đơn hàng
│   ├── inventory.php       ← Tồn kho & Báo cáo
│   └── users.php           ← Khách hàng
├── assets/
│   ├── css/
│   │   ├── style.css       ← CSS khách hàng
│   │   └── admin.css       ← CSS quản trị
│   ├── js/
│   │   └── main.js
│   └── images/
├── uploads/
│   └── products/           ← Ảnh sản phẩm upload
├── index.php               ← Trang chủ
├── products.php            ← Danh sách sản phẩm (phân trang)
├── product-detail.php      ← Chi tiết sản phẩm
├── search.php              ← Tìm kiếm (cơ bản + nâng cao)
├── cart.php                ← Giỏ hàng
├── checkout.php            ← Đặt hàng
├── order-success.php       ← Xác nhận đặt hàng
├── orders.php              ← Lịch sử đơn hàng
└── database.sql            ← Script CSDL + dữ liệu mẫu
```

---

## ⚙️ Hướng Dẫn Cài Đặt

### 1. Yêu cầu
- PHP ≥ 7.4
- MySQL ≥ 5.7 / MariaDB ≥ 10.3
- Web server: Apache (XAMPP/WAMP) hoặc Nginx
- Trình duyệt: Chrome / Firefox (phiên bản mới nhất)

### 2. Thiết lập CSDL
```sql
-- Chạy file database.sql trong phpMyAdmin hoặc MySQL CLI:
mysql -u root -p < database.sql
```

### 3. Cấu hình
Mở `config/db.php` và điều chỉnh:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // Mật khẩu MySQL của bạn
define('DB_NAME', 'noithat');
define('SITE_URL', 'http://localhost/NoiThat');  // URL project
```

### 4. Thư mục upload
Đảm bảo thư mục có quyền ghi:
```bash
chmod 777 uploads/products/
```

### 5. Chạy dự án
- Đặt thư mục `NoiThat/` vào `htdocs/` (XAMPP) hoặc `www/` (WAMP)
- Truy cập: `http://localhost/NoiThat`

---

## 🔐 Tài Khoản Demo

### Admin
- URL: `http://localhost/NoiThat/admin/login.php`
- Email: `admin@noithat.vn`
- Mật khẩu: `password`

> **Lưu ý:** Mật khẩu trong DB là hash của `password`. Nếu login lỗi, chạy SQL sau:
> ```sql
> UPDATE admin SET mat_khau = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email='admin@noithat.vn';
> ```

### Khách hàng
- Email: `an@gmail.com` | Mật khẩu: `password`
- Email: `binh@gmail.com` | Mật khẩu: `password`

---

## ✅ Danh Sách Chức Năng

### 🔐 Admin (Đồ Án Web 1 — Giao Diện + Web 2 — Backend)
| Chức năng | Trạng thái |
|-----------|------------|
| Đăng nhập admin URL riêng | ✅ |
| Dashboard thống kê | ✅ |
| Quản lý loại sản phẩm (thêm/sửa/xóa/ẩn) | ✅ |
| Quản lý sản phẩm (CRUD + upload ảnh + ẩn/xóa thông minh) | ✅ |
| Quản lý nhập hàng (phiếu nhiều SP, hoàn thành, giá bình quân) | ✅ |
| Quản lý giá bán (% lợi nhuận theo SP + hàng loạt theo loại) | ✅ |
| Quản lý đơn hàng (lọc ngày/tình trạng/phường, cập nhật) | ✅ |
| Tồn kho: tra cứu tại thời điểm + cảnh báo ngưỡng tự định | ✅ |
| Báo cáo nhập–xuất theo khoảng thời gian | ✅ |
| Quản lý khách hàng (thêm, khóa/mở, reset mật khẩu) | ✅ |

### 👤 Khách Hàng
| Chức năng | Trạng thái |
|-----------|------------|
| Đăng ký (đủ thông tin giao hàng) | ✅ |
| Đăng nhập / Đăng xuất | ✅ |
| Xem / Sửa thông tin cá nhân + đổi mật khẩu | ✅ |
| Hiển thị SP theo phân loại + phân trang | ✅ |
| Chi tiết sản phẩm | ✅ |
| Tìm kiếm cơ bản (tên) + nâng cao (tên+loại+khoảng giá) | ✅ |
| Giỏ hàng (thêm/bớt/xóa) | ✅ |
| Chọn địa chỉ giao (từ tài khoản hoặc nhập mới) | ✅ |
| Thanh toán (tiền mặt/CK/trực tuyến) | ✅ |
| Xem tóm tắt đơn sau khi đặt | ✅ |
| Lịch sử đơn hàng (gần nhất lên đầu) | ✅ |

### 📐 Kỹ Thuật
| Yêu cầu | Trạng thái |
|---------|------------|
| PHP + MySQL (không CMS) | ✅ |
| Đường dẫn tương đối | ✅ |
| Giá bình quân khi nhập hàng | ✅ |
| Validate form phía client | ✅ |
| Giao diện không quá xấu 😄 | ✅ |
| Thiết kế DB quan hệ 1–nhiều | ✅ |

---

## 💡 Ghi Chú Kỹ Thuật

**Giá bán = Giá nhập bình quân × (100% + % Lợi nhuận)**

**Giá nhập bình quân** được cập nhật mỗi khi hoàn thành phiếu nhập:
```
Giá BQ mới = (SL tồn × Giá BQ cũ + SL nhập × Giá nhập) / (SL tồn + SL nhập)
```

---

*© 2025 — Trường Đại học Sài Gòn — Khoa Công Nghệ Thông Tin*
