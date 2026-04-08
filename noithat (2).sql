-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2026 at 07:43 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `noithat`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `ma_admin` int(11) NOT NULL,
  `ten_admin` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`ma_admin`, `ten_admin`, `email`, `mat_khau`, `created_at`) VALUES
(1, 'Quản Trị Viên', 'admin@noithat.vn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-04-01 12:51:06');

-- --------------------------------------------------------

--
-- Table structure for table `chitiet_dh`
--

CREATE TABLE `chitiet_dh` (
  `id` int(11) NOT NULL,
  `ma_dh` int(11) NOT NULL,
  `ma_sp` int(11) NOT NULL,
  `ten_sp` varchar(200) NOT NULL,
  `so_luong` int(11) NOT NULL DEFAULT 1,
  `gia_ban` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chitiet_dh`
--

INSERT INTO `chitiet_dh` (`id`, `ma_dh`, `ma_sp`, `ten_sp`, `so_luong`, `gia_ban`) VALUES
(1, 1, 9, 'Ghế Ăn Rattan Tự Nhiên', 6, 700000.00),
(2, 1, 8, 'Bàn Ăn Gỗ Teak 6 Người', 1, 17280000.00);

-- --------------------------------------------------------

--
-- Table structure for table `chitiet_nhap`
--

CREATE TABLE `chitiet_nhap` (
  `id` int(11) NOT NULL,
  `ma_phieu` int(11) NOT NULL,
  `ma_sp` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL DEFAULT 0,
  `gia_nhap` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chitiet_nhap`
--

INSERT INTO `chitiet_nhap` (`id`, `ma_phieu`, `ma_sp`, `so_luong`, `gia_nhap`) VALUES
(1, 1, 8, 5, 12800000.00),
(2, 1, 9, 30, 500000.00),
(3, 1, 14, 10, 1200000.00),
(4, 1, 13, 5, 16000000.00),
(5, 3, 2, 5, 1800000.00),
(6, 3, 4, 10, 1100000.00),
(7, 3, 3, 5, 2000000.00),
(8, 3, 1, 10, 3400000.00),
(9, 4, 10, 15, 1950000.00),
(10, 4, 11, 15, 1450000.00),
(11, 4, 12, 10, 1200000.00),
(12, 5, 7, 5, 12000000.00),
(13, 5, 5, 5, 8000000.00),
(14, 5, 6, 10, 3200000.00);

-- --------------------------------------------------------

--
-- Table structure for table `don_hang`
--

CREATE TABLE `don_hang` (
  `ma_dh` int(11) NOT NULL,
  `ma_kh` int(11) NOT NULL,
  `ngay_dat` datetime DEFAULT current_timestamp(),
  `ho_ten_giao` varchar(100) NOT NULL,
  `dien_thoai_giao` varchar(20) DEFAULT NULL,
  `dia_chi_giao` varchar(255) NOT NULL,
  `phuong_xa` varchar(100) DEFAULT NULL,
  `quan_huyen` varchar(100) DEFAULT NULL,
  `tinh_tp` varchar(100) DEFAULT NULL,
  `hinh_thuc_tt` enum('tien_mat','chuyen_khoan','truc_tuyen') DEFAULT 'tien_mat',
  `tong_tien` decimal(15,2) DEFAULT 0.00,
  `trang_thai` enum('moi_dat','da_xac_nhan','da_giao','da_huy') DEFAULT 'moi_dat',
  `ghi_chu` text DEFAULT NULL,
  `da_tru_kho` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `don_hang`
--

INSERT INTO `don_hang` (`ma_dh`, `ma_kh`, `ngay_dat`, `ho_ten_giao`, `dien_thoai_giao`, `dia_chi_giao`, `phuong_xa`, `quan_huyen`, `tinh_tp`, `hinh_thuc_tt`, `tong_tien`, `trang_thai`, `ghi_chu`, `da_tru_kho`) VALUES
(1, 4, '2026-04-08 12:40:11', 'Nguyễn Thanh Tuấn', '0328206371', '123 LTL', 'Tân Hiệp', 'Hóc Môn', 'TP.HCM', 'tien_mat', 21480000.00, 'da_giao', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `gia_ban`
--

CREATE TABLE `gia_ban` (
  `ma_sp` int(11) NOT NULL,
  `tl_loi_nhuan` decimal(5,2) DEFAULT 20.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gia_ban`
--

INSERT INTO `gia_ban` (`ma_sp`, `tl_loi_nhuan`) VALUES
(1, 25.00),
(2, 30.00),
(3, 20.00),
(4, 35.00),
(5, 22.00),
(6, 28.00),
(7, 32.00),
(8, 35.00),
(9, 40.00),
(10, 30.00),
(11, 35.00),
(12, 25.00),
(13, 20.00),
(14, 38.00);

-- --------------------------------------------------------

--
-- Table structure for table `gio_hang`
--

CREATE TABLE `gio_hang` (
  `id` int(11) NOT NULL,
  `ma_kh` int(11) NOT NULL,
  `ma_sp` int(11) NOT NULL,
  `so_luong` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `khach_hang`
--

CREATE TABLE `khach_hang` (
  `ma_kh` int(11) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `dien_thoai` varchar(20) DEFAULT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `phuong_xa` varchar(100) DEFAULT NULL,
  `quan_huyen` varchar(100) DEFAULT NULL,
  `tinh_tp` varchar(100) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1 COMMENT '1=active, 0=khóa',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `khach_hang`
--

INSERT INTO `khach_hang` (`ma_kh`, `ho_ten`, `email`, `mat_khau`, `dien_thoai`, `dia_chi`, `phuong_xa`, `quan_huyen`, `tinh_tp`, `trang_thai`, `created_at`) VALUES
(1, 'Nguyễn Văn An', 'an@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234567', '123 Nguyễn Huệ', 'Bến Nghé', 'Quận 1', 'TP. Hồ Chí Minh', 1, '2026-04-01 12:51:06'),
(2, 'Trần Thị Bình', 'binh@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', '456 Lê Lợi', 'Phường 3', 'Quận 3', 'TP. Hồ Chí Minh', 1, '2026-04-01 12:51:06'),
(3, 'Lê Minh Châu', 'chau@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0923456789', '789 Hai Bà Trưng', 'Phường 6', 'Quận 3', 'TP. Hồ Chí Minh', 1, '2026-04-01 12:51:06'),
(4, 'Nguyễn Thanh Tuấn', 'binjkl555@gmail.com', '$2y$10$BmWEjPcRviwNRl6A5vCJ1eD4rm7zBv9wGSnO9oSO2Rwl3m4ez6rbm', '0328206371', '123 LTL', 'Tân Hiệp', 'Hóc Môn', 'TP.HCM', 1, '2026-04-01 13:09:45');

-- --------------------------------------------------------

--
-- Table structure for table `loai_sp`
--

CREATE TABLE `loai_sp` (
  `ma_loai` int(11) NOT NULL,
  `ten_loai` varchar(100) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1 COMMENT '1=hiện, 0=ẩn',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loai_sp`
--

INSERT INTO `loai_sp` (`ma_loai`, `ten_loai`, `mo_ta`, `trang_thai`, `created_at`) VALUES
(1, 'Phòng Khách', 'Sofa, bàn trà, kệ TV, tủ trang trí', 1, '2026-04-01 12:51:06'),
(2, 'Phòng Ngủ', 'Giường, tủ quần áo, đầu giường, bàn trang điểm', 1, '2026-04-01 12:51:06'),
(3, 'Phòng Ăn', 'Bàn ăn, ghế ăn, tủ buffet, xe đẩy', 1, '2026-04-01 12:51:06'),
(4, 'Phòng Làm Việc', 'Bàn làm việc, ghế văn phòng, kệ sách, tủ hồ sơ', 1, '2026-04-01 12:51:06'),
(5, 'Phòng Bếp', 'Tủ bếp, kệ bếp, ghế bar, bàn đảo bếp', 1, '2026-04-01 12:51:06');

-- --------------------------------------------------------

--
-- Table structure for table `phieu_nhap`
--

CREATE TABLE `phieu_nhap` (
  `ma_phieu` int(11) NOT NULL,
  `ngay_nhap` date NOT NULL,
  `trang_thai` tinyint(1) DEFAULT 0 COMMENT '0=chưa hoàn thành, 1=hoàn thành',
  `ghi_chu` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phieu_nhap`
--

INSERT INTO `phieu_nhap` (`ma_phieu`, `ngay_nhap`, `trang_thai`, `ghi_chu`, `created_at`) VALUES
(1, '2026-04-01', 1, '', '2026-04-08 12:28:34'),
(3, '2026-04-02', 1, '', '2026-04-08 12:31:29'),
(4, '2026-04-03', 1, '', '2026-04-08 12:35:16'),
(5, '2026-04-04', 1, '', '2026-04-08 12:36:17');

-- --------------------------------------------------------

--
-- Table structure for table `san_pham`
--

CREATE TABLE `san_pham` (
  `ma_sp` int(11) NOT NULL,
  `ma_loai` int(11) NOT NULL,
  `ten_sp` varchar(200) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `don_vi_tinh` varchar(30) DEFAULT 'Cái',
  `hinh` varchar(255) DEFAULT NULL,
  `tl_loi_nhuan` decimal(5,2) DEFAULT 20.00 COMMENT '% lợi nhuận mặc định',
  `trang_thai` tinyint(1) DEFAULT 1 COMMENT '1=đang bán, 0=ẩn',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `san_pham`
--

INSERT INTO `san_pham` (`ma_sp`, `ma_loai`, `ten_sp`, `mo_ta`, `don_vi_tinh`, `hinh`, `tl_loi_nhuan`, `trang_thai`, `created_at`) VALUES
(1, 1, 'Sofa Góc Vải Linen Bắc Âu', 'Sofa góc chữ L thiết kế tối giản phong cách Scandinavian, chân gỗ sồi tự nhiên, vải linen cao cấp chống bụi bẩn.', 'Bộ', 'sp_69d5e5ce65966.jpg', 25.00, 1, '2026-04-08 12:20:47'),
(2, 1, 'Bàn Trà Kính Cường Lực', 'Bàn trà mặt kính cường lực 10mm, khung thép sơn tĩnh điện màu đen matte, thiết kế hiện đại thanh lịch.', 'Cái', 'sp_69d5e5fad9a1e.jpg', 30.00, 1, '2026-04-08 12:22:02'),
(3, 1, 'Kệ TV Gỗ Walnut', 'TV gỗ walnut thật ghép tấm, có ngăn kéo lưu trữ, chiều dài 180cm phù hợp TV 65-75 inch.', 'Cái', 'sp_69d5e6159a696.webp', 20.00, 1, '2026-04-08 12:22:29'),
(4, 1, 'Ghế Armchair Da Thật', 'Ghế đơn bọc da thật nhập khẩu, khung gỗ sồi, chân gỗ cao, kiểu dáng sang trọng cổ điển.', 'Cái', 'sp_69d5e633f403c.webp', 35.00, 1, '2026-04-08 12:23:00'),
(5, 2, 'Giường Ngủ Gỗ Sồi 1m8', 'Giường đôi gỗ sồi tự nhiên, đầu giường bọc da PU, thiết kế tối giản hiện đại, bền đẹp theo thời gian.', 'Cái', 'sp_69d5e65dd622a.jpg', 22.00, 1, '2026-04-08 12:23:41'),
(6, 2, 'Tủ Quần Áo 4 Cánh', 'Tủ quần áo 4 cánh gỗ công nghiệp MDF phủ melamine, có gương toàn thân, nhiều ngăn phân chia khoa học.', 'Cái', 'sp_69d5e67fc97ce.jpg', 28.00, 1, '2026-04-08 12:24:15'),
(7, 2, 'Đầu Giường Bọc Da', 'Đầu giường bọc da PU cao cấp, độ dày đệm 8cm, khung gỗ thông chắc chắn, phù hợp giường 1m6-2m.', 'Cái', 'sp_69d5e6a15cb8f.jpg', 32.00, 1, '2026-04-08 12:24:49'),
(8, 3, 'Bàn Ăn Gỗ Teak 6 Người', 'Bàn ăn gỗ teak nguyên khối, mặt bàn dày 4cm, chân chữ T, sang trọng và bền vĩnh cửu với thời gian.', 'Bộ', 'sp_69d5e6bb7c52a.webp', 35.00, 1, '2026-04-08 12:25:15'),
(9, 3, 'Ghế Ăn Rattan Tự Nhiên', 'Ghế ăn khung thép sơn tĩnh điện, mặt ngồi và lưng đan mây rattan tự nhiên, nhẹ và bền.', 'Cái', 'sp_69d5e6d93a0df.webp', 40.00, 1, '2026-04-08 12:25:45'),
(10, 4, 'Bàn Làm Việc Thông Minh', 'Bàn làm việc gỗ MDF cao cấp, có ngăn kéo bí mật, hộc để máy tính, cổng USB tích hợp sẵn.', 'Cái', 'sp_69d5e6f02ff95.jpg', 30.00, 1, '2026-04-08 12:26:08'),
(11, 4, 'Ghế Văn Phòng Ergonomic', 'Ghế văn phòng thiết kế ergonomic hỗ trợ cột sống, tựa đầu, tựa tay 3D, lưng lưới thoáng khí.', 'Cái', 'sp_69d5e70729a34.jpg', 35.00, 1, '2026-04-08 12:26:31'),
(12, 4, 'Kệ Sách Gỗ Thông 5 Tầng', 'Kệ sách 5 tầng gỗ thông tự nhiên, có thể điều chỉnh độ cao ngăn, màu tự nhiên ấm áp.', 'Cái', 'sp_69d5e72165d79.jpg', 25.00, 1, '2026-04-08 12:26:57'),
(13, 5, 'Tủ Bếp Acrylic Bóng', 'Tủ bếp cánh Acrylic bóng gương, tủ dưới mở ra dễ dàng, chống ẩm, dễ vệ sinh, nhiều màu tùy chọn.', 'Bộ', 'sp_69d5e7411b8b1.webp', 20.00, 1, '2026-04-08 12:27:29'),
(14, 5, 'Ghế Bar Chân Cao', 'Ghế bar gỗ ash, chân kim loại, nệm ngồi bọc vải chống thấm, có thể điều chỉnh độ cao.', 'Cái', 'sp_69d5e75f6cf3a.webp', 38.00, 1, '2026-04-08 12:27:59');

-- --------------------------------------------------------

--
-- Table structure for table `ton_kho`
--

CREATE TABLE `ton_kho` (
  `ma_sp` int(11) NOT NULL,
  `so_luong` int(11) DEFAULT 0,
  `gia_von` decimal(15,2) DEFAULT 0.00 COMMENT 'Giá nhập bình quân',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ton_kho`
--

INSERT INTO `ton_kho` (`ma_sp`, `so_luong`, `gia_von`, `updated_at`) VALUES
(1, 10, 3400000.00, '2026-04-08 12:34:53'),
(2, 5, 1800000.00, '2026-04-08 12:34:53'),
(3, 5, 2000000.00, '2026-04-08 12:34:53'),
(4, 10, 1100000.00, '2026-04-08 12:34:53'),
(5, 5, 8000000.00, '2026-04-08 12:36:57'),
(6, 10, 3200000.00, '2026-04-08 12:36:57'),
(7, 5, 12000000.00, '2026-04-08 12:36:57'),
(8, 4, 12800000.00, '2026-04-08 12:42:42'),
(9, 24, 500000.00, '2026-04-08 12:42:42'),
(10, 15, 1950000.00, '2026-04-08 12:36:10'),
(11, 15, 1450000.00, '2026-04-08 12:36:10'),
(12, 10, 1200000.00, '2026-04-08 12:36:10'),
(13, 5, 16000000.00, '2026-04-08 12:30:30'),
(14, 10, 1200000.00, '2026-04-08 12:30:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ma_admin`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `chitiet_dh`
--
ALTER TABLE `chitiet_dh`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ma_dh` (`ma_dh`),
  ADD KEY `ma_sp` (`ma_sp`);

--
-- Indexes for table `chitiet_nhap`
--
ALTER TABLE `chitiet_nhap`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ma_phieu` (`ma_phieu`),
  ADD KEY `ma_sp` (`ma_sp`);

--
-- Indexes for table `don_hang`
--
ALTER TABLE `don_hang`
  ADD PRIMARY KEY (`ma_dh`),
  ADD KEY `ma_kh` (`ma_kh`);

--
-- Indexes for table `gia_ban`
--
ALTER TABLE `gia_ban`
  ADD PRIMARY KEY (`ma_sp`);

--
-- Indexes for table `gio_hang`
--
ALTER TABLE `gio_hang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart` (`ma_kh`,`ma_sp`),
  ADD KEY `ma_sp` (`ma_sp`);

--
-- Indexes for table `khach_hang`
--
ALTER TABLE `khach_hang`
  ADD PRIMARY KEY (`ma_kh`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `loai_sp`
--
ALTER TABLE `loai_sp`
  ADD PRIMARY KEY (`ma_loai`);

--
-- Indexes for table `phieu_nhap`
--
ALTER TABLE `phieu_nhap`
  ADD PRIMARY KEY (`ma_phieu`);

--
-- Indexes for table `san_pham`
--
ALTER TABLE `san_pham`
  ADD PRIMARY KEY (`ma_sp`),
  ADD KEY `ma_loai` (`ma_loai`);

--
-- Indexes for table `ton_kho`
--
ALTER TABLE `ton_kho`
  ADD PRIMARY KEY (`ma_sp`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `ma_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chitiet_dh`
--
ALTER TABLE `chitiet_dh`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chitiet_nhap`
--
ALTER TABLE `chitiet_nhap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `don_hang`
--
ALTER TABLE `don_hang`
  MODIFY `ma_dh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gio_hang`
--
ALTER TABLE `gio_hang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `khach_hang`
--
ALTER TABLE `khach_hang`
  MODIFY `ma_kh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `loai_sp`
--
ALTER TABLE `loai_sp`
  MODIFY `ma_loai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `phieu_nhap`
--
ALTER TABLE `phieu_nhap`
  MODIFY `ma_phieu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `san_pham`
--
ALTER TABLE `san_pham`
  MODIFY `ma_sp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chitiet_dh`
--
ALTER TABLE `chitiet_dh`
  ADD CONSTRAINT `chitiet_dh_ibfk_1` FOREIGN KEY (`ma_dh`) REFERENCES `don_hang` (`ma_dh`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitiet_dh_ibfk_2` FOREIGN KEY (`ma_sp`) REFERENCES `san_pham` (`ma_sp`);

--
-- Constraints for table `chitiet_nhap`
--
ALTER TABLE `chitiet_nhap`
  ADD CONSTRAINT `chitiet_nhap_ibfk_1` FOREIGN KEY (`ma_phieu`) REFERENCES `phieu_nhap` (`ma_phieu`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitiet_nhap_ibfk_2` FOREIGN KEY (`ma_sp`) REFERENCES `san_pham` (`ma_sp`);

--
-- Constraints for table `don_hang`
--
ALTER TABLE `don_hang`
  ADD CONSTRAINT `don_hang_ibfk_1` FOREIGN KEY (`ma_kh`) REFERENCES `khach_hang` (`ma_kh`);

--
-- Constraints for table `gia_ban`
--
ALTER TABLE `gia_ban`
  ADD CONSTRAINT `gia_ban_ibfk_1` FOREIGN KEY (`ma_sp`) REFERENCES `san_pham` (`ma_sp`);

--
-- Constraints for table `gio_hang`
--
ALTER TABLE `gio_hang`
  ADD CONSTRAINT `gio_hang_ibfk_1` FOREIGN KEY (`ma_kh`) REFERENCES `khach_hang` (`ma_kh`) ON DELETE CASCADE,
  ADD CONSTRAINT `gio_hang_ibfk_2` FOREIGN KEY (`ma_sp`) REFERENCES `san_pham` (`ma_sp`);

--
-- Constraints for table `san_pham`
--
ALTER TABLE `san_pham`
  ADD CONSTRAINT `san_pham_ibfk_1` FOREIGN KEY (`ma_loai`) REFERENCES `loai_sp` (`ma_loai`) ON UPDATE CASCADE;

--
-- Constraints for table `ton_kho`
--
ALTER TABLE `ton_kho`
  ADD CONSTRAINT `ton_kho_ibfk_1` FOREIGN KEY (`ma_sp`) REFERENCES `san_pham` (`ma_sp`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
