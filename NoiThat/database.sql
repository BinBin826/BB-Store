-- ============================================================
-- DATABASE: NoiThat - Website Bán Đồ Nội Thất
-- Trường Đại học Sài Gòn - Đồ Án Web1 & Web2
-- ============================================================

CREATE DATABASE IF NOT EXISTS noithat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE noithat;

-- ============================================================
-- BẢNG: loai_sp (Loại sản phẩm)
-- ============================================================
CREATE TABLE loai_sp (
    ma_loai     INT AUTO_INCREMENT PRIMARY KEY,
    ten_loai    VARCHAR(100) NOT NULL,
    mo_ta       TEXT,
    trang_thai  TINYINT(1) DEFAULT 1 COMMENT '1=hiện, 0=ẩn',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- BẢNG: san_pham (Sản phẩm)
-- ============================================================
CREATE TABLE san_pham (
    ma_sp       INT AUTO_INCREMENT PRIMARY KEY,
    ma_loai     INT NOT NULL,
    ten_sp      VARCHAR(200) NOT NULL,
    mo_ta       TEXT,
    don_vi_tinh VARCHAR(30) DEFAULT 'Cái',
    hinh        VARCHAR(255),
    tl_loi_nhuan DECIMAL(5,2) DEFAULT 20.00 COMMENT '% lợi nhuận mặc định',
    trang_thai  TINYINT(1) DEFAULT 1 COMMENT '1=đang bán, 0=ẩn',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_loai) REFERENCES loai_sp(ma_loai) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- BẢNG: phieu_nhap (Phiếu nhập hàng)
-- ============================================================
CREATE TABLE phieu_nhap (
    ma_phieu    INT AUTO_INCREMENT PRIMARY KEY,
    ngay_nhap   DATE NOT NULL,
    trang_thai  TINYINT(1) DEFAULT 0 COMMENT '0=chưa hoàn thành, 1=hoàn thành',
    ghi_chu     TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- BẢNG: chitiet_nhap (Chi tiết phiếu nhập)
-- ============================================================
CREATE TABLE chitiet_nhap (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ma_phieu    INT NOT NULL,
    ma_sp       INT NOT NULL,
    so_luong    INT NOT NULL DEFAULT 0,
    gia_nhap    DECIMAL(15,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (ma_phieu) REFERENCES phieu_nhap(ma_phieu) ON DELETE CASCADE,
    FOREIGN KEY (ma_sp) REFERENCES san_pham(ma_sp)
) ENGINE=InnoDB;

-- ============================================================
-- BẢNG: ton_kho (Tồn kho - cập nhật theo bình quân)
-- ============================================================
CREATE TABLE ton_kho (
    ma_sp       INT PRIMARY KEY,
    so_luong    INT DEFAULT 0,
    gia_von     DECIMAL(15,2) DEFAULT 0 COMMENT 'Giá nhập bình quân',
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ma_sp) REFERENCES san_pham(ma_sp)
) ENGINE=InnoDB;

-- ============================================================
-- BẢNG: gia_ban (Tỷ lệ lợi nhuận & giá bán)
-- ============================================================
CREATE TABLE gia_ban (
    ma_sp       INT PRIMARY KEY,
    tl_loi_nhuan DECIMAL(5,2) DEFAULT 20.00,
    FOREIGN KEY (ma_sp) REFERENCES san_pham(ma_sp)
) ENGINE=InnoDB;

-- ============================================================
-- BẢNG: khach_hang (Khách hàng / Người dùng)
-- ============================================================
CREATE TABLE khach_hang (
    ma_kh       INT AUTO_INCREMENT PRIMARY KEY,
    ho_ten      VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    mat_khau    VARCHAR(255) NOT NULL,
    dien_thoai  VARCHAR(20),
    dia_chi     VARCHAR(255),
    phuong_xa   VARCHAR(100),
    quan_huyen  VARCHAR(100),
    tinh_tp     VARCHAR(100),
    trang_thai  TINYINT(1) DEFAULT 1 COMMENT '1=active, 0=khóa',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- BẢNG: admin (Quản trị viên)
-- ============================================================
CREATE TABLE admin (
    ma_admin    INT AUTO_INCREMENT PRIMARY KEY,
    ten_admin   VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    mat_khau    VARCHAR(255) NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- BẢNG: don_hang (Đơn hàng)
-- ============================================================
CREATE TABLE don_hang (
    ma_dh       INT AUTO_INCREMENT PRIMARY KEY,
    ma_kh       INT NOT NULL,
    ngay_dat    DATETIME DEFAULT CURRENT_TIMESTAMP,
    ho_ten_giao VARCHAR(100) NOT NULL,
    dien_thoai_giao VARCHAR(20),
    dia_chi_giao VARCHAR(255) NOT NULL,
    phuong_xa   VARCHAR(100),
    quan_huyen  VARCHAR(100),
    tinh_tp     VARCHAR(100),
    hinh_thuc_tt ENUM('tien_mat','chuyen_khoan','truc_tuyen') DEFAULT 'tien_mat',
    tong_tien   DECIMAL(15,2) DEFAULT 0,
    trang_thai  ENUM('moi_dat','da_xac_nhan','da_giao','da_huy') DEFAULT 'moi_dat',
    ghi_chu     TEXT,
    FOREIGN KEY (ma_kh) REFERENCES khach_hang(ma_kh)
) ENGINE=InnoDB;

-- ============================================================
-- BẢNG: chitiet_dh (Chi tiết đơn hàng)
-- ============================================================
CREATE TABLE chitiet_dh (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ma_dh       INT NOT NULL,
    ma_sp       INT NOT NULL,
    ten_sp      VARCHAR(200) NOT NULL,
    so_luong    INT NOT NULL DEFAULT 1,
    gia_ban     DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (ma_dh) REFERENCES don_hang(ma_dh) ON DELETE CASCADE,
    FOREIGN KEY (ma_sp) REFERENCES san_pham(ma_sp)
) ENGINE=InnoDB;

-- ============================================================
-- BẢNG: gio_hang (Giỏ hàng - lưu session)
-- ============================================================
CREATE TABLE gio_hang (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ma_kh       INT NOT NULL,
    ma_sp       INT NOT NULL,
    so_luong    INT DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart (ma_kh, ma_sp),
    FOREIGN KEY (ma_kh) REFERENCES khach_hang(ma_kh) ON DELETE CASCADE,
    FOREIGN KEY (ma_sp) REFERENCES san_pham(ma_sp)
) ENGINE=InnoDB;

-- ============================================================
-- DỮ LIỆU MẪU
-- ============================================================

-- Admin mặc định (password: admin123)
INSERT INTO admin (ten_admin, email, mat_khau) VALUES
('Quản Trị Viên', 'admin@noithat.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Loại sản phẩm
INSERT INTO loai_sp (ten_loai, mo_ta) VALUES
('Phòng Khách', 'Sofa, bàn trà, kệ TV, tủ trang trí'),
('Phòng Ngủ', 'Giường, tủ quần áo, đầu giường, bàn trang điểm'),
('Phòng Ăn', 'Bàn ăn, ghế ăn, tủ buffet, xe đẩy'),
('Phòng Làm Việc', 'Bàn làm việc, ghế văn phòng, kệ sách, tủ hồ sơ'),
('Phòng Bếp', 'Tủ bếp, kệ bếp, ghế bar, bàn đảo bếp');

-- Sản phẩm mẫu
INSERT INTO san_pham (ma_loai, ten_sp, mo_ta, don_vi_tinh, hinh, tl_loi_nhuan) VALUES
(1,'Sofa Góc Vải Linen Bắc Âu','Sofa góc chữ L thiết kế tối giản phong cách Scandinavian, chân gỗ sồi tự nhiên, vải linen cao cấp chống bụi bẩn.','Bộ','sofa-goc-linen.jpg',25),
(1,'Bàn Trà Kính Cường Lực','Bàn trà mặt kính cường lực 10mm, khung thép sơn tĩnh điện màu đen matte, thiết kế hiện đại thanh lịch.','Cái','ban-tra-kinh.jpg',30),
(1,'Kệ TV Gỗ Walnut','Kệ TV gỗ walnut thật ghép tấm, có ngăn kéo lưu trữ, chiều dài 180cm phù hợp TV 65-75 inch.','Cái','ke-tv-walnut.jpg',20),
(1,'Ghế Armchair Da Thật','Ghế đơn bọc da thật nhập khẩu, khung gỗ sồi, chân gỗ cao, kiểu dáng sang trọng cổ điển.','Cái','ghe-armchair-da.jpg',35),
(2,'Giường Ngủ Gỗ Sồi 1m8','Giường đôi gỗ sồi tự nhiên, đầu giường bọc da PU, thiết kế tối giản hiện đại, bền đẹp theo thời gian.','Cái','giuong-soi-1m8.jpg',22),
(2,'Tủ Quần Áo 4 Cánh','Tủ quần áo 4 cánh gỗ công nghiệp MDF phủ melamine, có gương toàn thân, nhiều ngăn phân chia khoa học.','Cái','tu-quan-ao-4-canh.jpg',28),
(2,'Đầu Giường Bọc Da','Đầu giường bọc da PU cao cấp, độ dày đệm 8cm, khung gỗ thông chắc chắn, phù hợp giường 1m6-2m.','Cái','dau-giuong-da.jpg',32),
(3,'Bàn Ăn Gỗ Teak 6 Người','Bàn ăn gỗ teak nguyên khối, mặt bàn dày 4cm, chân chữ T, sang trọng và bền vĩnh cửu với thời gian.','Bộ','ban-an-teak.jpg',25),
(3,'Ghế Ăn Rattan Tự Nhiên','Ghế ăn khung thép sơn tĩnh điện, mặt ngồi và lưng đan mây rattan tự nhiên, nhẹ và bền.','Cái','ghe-an-rattan.jpg',40),
(4,'Bàn Làm Việc Thông Minh','Bàn làm việc gỗ MDF cao cấp, có ngăn kéo bí mật, hộc để máy tính, cổng USB tích hợp sẵn.','Cái','ban-lam-viec.jpg',30),
(4,'Ghế Văn Phòng Ergonomic','Ghế văn phòng thiết kế ergonomic hỗ trợ cột sống, tựa đầu, tựa tay 3D, lưng lưới thoáng khí.','Cái','ghe-van-phong.jpg',35),
(4,'Kệ Sách Gỗ Thông 5 Tầng','Kệ sách 5 tầng gỗ thông tự nhiên, có thể điều chỉnh độ cao ngăn, màu tự nhiên ấm áp.','Cái','ke-sach-thong.jpg',25),
(5,'Tủ Bếp Acrylic Bóng','Tủ bếp cánh Acrylic bóng gương, tủ dưới mở ra dễ dàng, chống ẩm, dễ vệ sinh, nhiều màu tùy chọn.','Bộ','tu-bep-acrylic.jpg',20),
(5,'Ghế Bar Chân Cao','Ghế bar gỗ ash, chân kim loại, nệm ngồi bọc vải chống thấm, có thể điều chỉnh độ cao.','Cái','ghe-bar.jpg',38);

-- Khách hàng mẫu (password: 123456)
INSERT INTO khach_hang (ho_ten, email, mat_khau, dien_thoai, dia_chi, phuong_xa, quan_huyen, tinh_tp) VALUES
('Nguyễn Văn An','an@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','0901234567','123 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh'),
('Trần Thị Bình','binh@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','0912345678','456 Lê Lợi','Phường 3','Quận 3','TP. Hồ Chí Minh'),
('Lê Minh Châu','chau@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','0923456789','789 Hai Bà Trưng','Phường 6','Quận 3','TP. Hồ Chí Minh');

-- Tồn kho ban đầu (sẽ được cập nhật qua phiếu nhập)
INSERT INTO ton_kho (ma_sp, so_luong, gia_von) VALUES
(1,5,8000000),(2,10,1500000),(3,8,3500000),(4,6,5000000),
(5,4,12000000),(6,7,6000000),(7,5,2000000),(8,3,15000000),
(9,20,800000),(10,6,4500000),(11,5,3500000),(12,10,1200000),
(13,2,25000000),(14,15,1800000);

-- Giá bán
INSERT INTO gia_ban (ma_sp, tl_loi_nhuan) VALUES
(1,25),(2,30),(3,20),(4,35),(5,22),(6,28),(7,32),
(8,25),(9,40),(10,30),(11,35),(12,25),(13,20),(14,38);

-- Phiếu nhập mẫu
INSERT INTO phieu_nhap (ngay_nhap, trang_thai, ghi_chu) VALUES
('2025-01-10',1,'Nhập hàng đầu năm 2025'),
('2025-03-15',1,'Nhập bổ sung phòng khách'),
('2025-06-01',0,'Phiếu nhập tháng 6 - chưa hoàn thành');

INSERT INTO chitiet_nhap (ma_phieu, ma_sp, so_luong, gia_nhap) VALUES
(1,1,5,8000000),(1,2,10,1500000),(1,3,8,3500000),
(2,4,6,5000000),(2,5,4,12000000),(2,6,7,6000000),
(3,7,10,2000000),(3,8,3,15000000);

-- Đơn hàng mẫu
INSERT INTO don_hang (ma_kh,ho_ten_giao,dien_thoai_giao,dia_chi_giao,phuong_xa,quan_huyen,tinh_tp,hinh_thuc_tt,tong_tien,trang_thai) VALUES
(1,'Nguyễn Văn An','0901234567','123 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','tien_mat',10000000,'da_giao'),
(1,'Nguyễn Văn An','0901234567','123 Nguyễn Huệ','Bến Nghé','Quận 1','TP. Hồ Chí Minh','chuyen_khoan',4375000,'da_xac_nhan'),
(2,'Trần Thị Bình','0912345678','456 Lê Lợi','Phường 3','Quận 3','TP. Hồ Chí Minh','tien_mat',15000000,'moi_dat');

INSERT INTO chitiet_dh (ma_dh,ma_sp,ten_sp,so_luong,gia_ban) VALUES
(1,1,'Sofa Góc Vải Linen Bắc Âu',1,10000000),
(2,2,'Bàn Trà Kính Cường Lực',1,1875000),(2,9,'Ghế Ăn Rattan Tự Nhiên',2,1250000),
(3,5,'Giường Ngủ Gỗ Sồi 1m8',1,14640000);
