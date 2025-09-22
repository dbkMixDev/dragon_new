-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.36-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             12.6.0.6765
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table dragon_playx.booking
CREATE TABLE IF NOT EXISTS `booking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` varchar(50) NOT NULL DEFAULT '0',
  `time_start` varchar(50) NOT NULL DEFAULT '0',
  `time_end` varchar(50) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `tlp` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  `acc_admin` varchar(50) DEFAULT NULL,
  `no_ps` varchar(50) DEFAULT NULL,
  `userx` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.booking: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.login_attempts
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(100) DEFAULT NULL,
  `attempts` int(11) DEFAULT '0',
  `last_attempt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hostname` (`hostname`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.login_attempts: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.memberships
CREATE TABLE IF NOT EXISTS `memberships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `membership_type` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `features` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `membership_type` (`membership_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- Dumping data for table dragon_playx.memberships: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userx` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text,
  `icon` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT 'assets/images/logos/default-logo.png',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userx` (`userx`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.notifications: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `membership_type` varchar(20) NOT NULL,
  `gross_amount` int(11) NOT NULL,
  `payment_status` varchar(20) DEFAULT 'pending',
  `snap_token` varchar(255) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `transaction_status` varchar(50) DEFAULT NULL,
  `fraud_status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  KEY `idx_order_id` (`order_id`) USING BTREE,
  KEY `idx_payment_status` (`payment_status`) USING BTREE,
  KEY `idx_customer_email` (`customer_email`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- Dumping data for table dragon_playx.orders: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.password_change_logs
CREATE TABLE IF NOT EXISTS `password_change_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `change_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_agent` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.password_change_logs: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.playstations
CREATE TABLE IF NOT EXISTS `playstations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userx` varchar(100) DEFAULT NULL,
  `no_ps` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'available',
  `type_ps` varchar(50) NOT NULL,
  `type_rental` varchar(50) NOT NULL,
  `start_time` varchar(50) DEFAULT NULL,
  `end_time` varchar(50) DEFAULT NULL,
  `duration` varchar(50) NOT NULL DEFAULT '0',
  `id_usb` varchar(50) DEFAULT NULL,
  `status_device` varchar(50) DEFAULT NULL,
  `total_pause` int(11) DEFAULT NULL,
  `pause_time` varchar(50) DEFAULT NULL,
  `type_modul` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userx` (`userx`),
  KEY `no_ps` (`no_ps`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table dragon_playx.playstations: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.recoverpw
CREATE TABLE IF NOT EXISTS `recoverpw` (
  `email` varchar(255) NOT NULL,
  `token` varchar(10) DEFAULT NULL,
  `update` datetime DEFAULT NULL,
  `reset` int(11) NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.recoverpw: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.tb_category
CREATE TABLE IF NOT EXISTS `tb_category` (
  `id_category` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userx` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_category`),
  KEY `userx` (`userx`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.tb_category: ~14 rows (approximately)
INSERT INTO `tb_category` (`id_category`, `name`, `created_at`, `userx`, `status`, `type`) VALUES
	(1, 'Playstation 3', '2025-06-03 18:00:28', 'ALL', NULL, 'Playstation'),
	(2, 'Playstation 4', '2025-06-03 18:00:28', 'ALL', NULL, 'Playstation'),
	(3, 'Playstation 3 VIP', '2025-06-03 18:00:28', 'ALL', NULL, 'Playstation'),
	(4, 'Playstation 5 VIP', '2025-06-03 23:40:39', 'ALL', NULL, 'Playstation'),
	(5, 'Playstation 4 VIP ', '2025-06-03 23:41:01', 'ALL', NULL, 'Playstation'),
	(6, 'Playstation 5', '2025-06-03 23:48:13', 'ALL', NULL, 'Playstation'),
	(8, 'Billiard Reguler', '2025-06-04 01:57:38', 'ALL', NULL, 'Billiard'),
	(9, 'Billiard VIP', '2025-06-04 08:15:04', 'ALL', NULL, 'Billiard'),
	(12, 'Playbox PS 3', '2025-06-04 17:35:01', 'ALL', NULL, 'Playbox'),
	(13, 'Playbox PS 4', '2025-06-04 17:35:12', 'ALL', NULL, 'Playbox'),
	(33, 'Playbox PS 5', '2025-06-04 17:35:12', 'ALL', NULL, 'Playbox'),
	(34, 'Playbox PS 3 SET TV', '2025-06-04 17:35:01', 'ALL', NULL, 'Playbox'),
	(35, 'Playbox PS 4 SET TV', '2025-06-04 17:35:12', 'ALL', NULL, 'Playbox'),
	(36, 'Playbox PS 5 SET TV', '2025-06-04 17:35:12', 'ALL', NULL, 'Playbox');

-- Dumping structure for table dragon_playx.tb_feature
CREATE TABLE IF NOT EXISTS `tb_feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feature` varchar(50) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `value` varchar(50) DEFAULT NULL,
  `userx` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.tb_feature: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.tb_fnb
CREATE TABLE IF NOT EXISTS `tb_fnb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `userx` varchar(100) DEFAULT NULL,
  `type_fnb` varchar(50) DEFAULT NULL,
  `update_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userx` (`userx`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.tb_fnb: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.tb_package
CREATE TABLE IF NOT EXISTS `tb_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_package` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `unit` int(11) DEFAULT '0',
  `portal` int(11) DEFAULT '0',
  `multi_cabang` int(11) DEFAULT '0',
  `qr` int(11) DEFAULT '0',
  `update_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_package` (`id_package`),
  KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.tb_package: ~1 rows (approximately)
INSERT INTO `tb_package` (`id`, `id_package`, `username`, `unit`, `portal`, `multi_cabang`, `qr`, `update_at`) VALUES
	(1, 'starter', 'dwibudikur@gmail.com', 5, 0, 0, 0, '2025-06-20 21:47:48');

-- Dumping structure for table dragon_playx.tb_pricelist
CREATE TABLE IF NOT EXISTS `tb_pricelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_ps` varchar(25) NOT NULL,
  `duration` varchar(50) NOT NULL DEFAULT '',
  `price` int(11) NOT NULL,
  `userx` varchar(100) DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL COMMENT 'UserX yang update',
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userx` (`userx`),
  KEY `type_ps` (`type_ps`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table dragon_playx.tb_pricelist: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.tb_pricelist_log
CREATE TABLE IF NOT EXISTS `tb_pricelist_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pricelist_id` int(11) NOT NULL,
  `old_price` int(11) NOT NULL,
  `new_price` int(11) NOT NULL,
  `updated_by` varchar(100) NOT NULL COMMENT 'Session username',
  `userx` varchar(100) NOT NULL COMMENT 'Parameter userx (prioritas tertinggi)',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pricelist_id` (`pricelist_id`),
  KEY `idx_updated_by` (`updated_by`),
  KEY `idx_userx` (`userx`),
  KEY `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.tb_pricelist_log: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.tb_promo
CREATE TABLE IF NOT EXISTS `tb_promo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_promo` varchar(50) DEFAULT NULL,
  `type_rental` varchar(50) DEFAULT NULL,
  `qty_potongan` varchar(50) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `userx` varchar(100) DEFAULT NULL,
  `disc_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userx` (`userx`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.tb_promo: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.tb_trans
CREATE TABLE IF NOT EXISTS `tb_trans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trans` varchar(50) DEFAULT NULL,
  `id_ps` varchar(50) NOT NULL,
  `start` varchar(50) NOT NULL,
  `end` varchar(50) DEFAULT NULL,
  `durasi` varchar(50) DEFAULT NULL,
  `mode_stop` varchar(50) DEFAULT NULL,
  `manual_stop` varchar(50) DEFAULT NULL,
  `harga` varchar(50) DEFAULT NULL,
  `date_trans` varchar(50) DEFAULT NULL,
  `extra` varchar(50) DEFAULT NULL,
  `userx` varchar(100) DEFAULT NULL,
  `inv` varchar(100) DEFAULT NULL,
  `is_deleted` int(11) DEFAULT NULL,
  `usercreate` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_trans` (`id_trans`),
  KEY `userx` (`userx`),
  KEY `id_ps` (`id_ps`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table dragon_playx.tb_trans: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.tb_trans_final
CREATE TABLE IF NOT EXISTS `tb_trans_final` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metode_pembayaran` varchar(20) DEFAULT NULL,
  `bayar` int(11) DEFAULT NULL,
  `kembali` int(11) DEFAULT NULL,
  `promo` int(11) DEFAULT NULL,
  `invoice` varchar(50) DEFAULT NULL,
  `created_at` varchar(50) DEFAULT NULL,
  `id_trans` varchar(50) DEFAULT NULL,
  `userx` varchar(50) DEFAULT NULL,
  `grand_total` int(11) NOT NULL DEFAULT '0',
  `is_deleted` int(11) DEFAULT NULL,
  `usercreate` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice` (`invoice`),
  KEY `userx` (`userx`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.tb_trans_final: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.tb_trans_fnb
CREATE TABLE IF NOT EXISTS `tb_trans_fnb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trans` varchar(32) NOT NULL,
  `id_fnb` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT '1',
  `created_at` varchar(50) DEFAULT NULL,
  `diskon` varchar(50) DEFAULT NULL,
  `inv` varchar(50) DEFAULT NULL,
  `userx` varchar(50) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `id_ps` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_deleted` int(11) DEFAULT NULL,
  `usercreate` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_fnb` (`id_fnb`),
  KEY `userx` (`userx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table dragon_playx.tb_trans_fnb: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.tb_trans_out
CREATE TABLE IF NOT EXISTS `tb_trans_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metode_pembayaran` varchar(20) DEFAULT NULL,
  `created_at` varchar(50) DEFAULT NULL,
  `id_trans` varchar(50) DEFAULT NULL,
  `userx` varchar(50) DEFAULT NULL,
  `grand_total` int(11) NOT NULL DEFAULT '0',
  `category` varchar(50) DEFAULT NULL,
  `note` text,
  `is_deleted` int(11) DEFAULT NULL,
  `invoice` varchar(50) DEFAULT NULL,
  `usercreate` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `userx` (`userx`),
  KEY `id_trans` (`id_trans`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- Dumping data for table dragon_playx.tb_trans_out: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','success','failed','challenge') DEFAULT 'pending',
  `payment_type` varchar(50) DEFAULT NULL,
  `transaction_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `package` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.transactions: ~1 rows (approximately)
INSERT INTO `transactions` (`id`, `order_id`, `email`, `phone`, `full_name`, `amount`, `status`, `payment_type`, `transaction_time`, `created_at`, `updated_at`, `package`) VALUES
	(2, 'DP-20250620234201-7028', 'dwibudikur@gmail.com', '08984390348', 'Dwi Budi Kurniawan', 350000.00, 'success', NULL, NULL, '2025-06-20 21:42:01', '2025-06-20 21:47:48', 'starter');

-- Dumping structure for table dragon_playx.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `membership_status` enum('active','inactive','expired') DEFAULT 'inactive',
  `membership_expired` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_membership` (`membership_status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.users: ~0 rows (approximately)

-- Dumping structure for table dragon_playx.userx
CREATE TABLE IF NOT EXISTS `userx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `merchand` varchar(100) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `cabang` varchar(100) DEFAULT NULL,
  `last_log` datetime DEFAULT NULL,
  `host` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `license` varchar(255) DEFAULT NULL,
  `license_exp` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email` varchar(100) DEFAULT NULL,
  `address` text,
  `login_token` varchar(50) DEFAULT NULL,
  `logox` text,
  `timezone` varchar(50) DEFAULT 'Asia/Jakarta',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table dragon_playx.userx: ~1 rows (approximately)
INSERT INTO `userx` (`id`, `username`, `pass`, `merchand`, `level`, `cabang`, `last_log`, `host`, `status`, `license`, `license_exp`, `created_at`, `updated_at`, `email`, `address`, `login_token`, `logox`, `timezone`) VALUES
	(1, 'dwibudikur@gmail.com', '$2y$10$YuQvR/JCWBXG6quG/s7c1.23LIvHo666fcBpFEkMfCXXQXfO.FysW', 'Dragon Play', 'admin', NULL, '2025-06-21 10:54:09', 'DBK', 1, 'LIC-A0D62F', '2026-06-20', '2025-06-20 21:47:50', '2025-06-21 03:54:09', 'dwibudikur@gmail.com', 'Muarapiluk, Bakauheni Lampung Selatan', 'ab1bb8df398efa3c8d7d6294d303e694', 'assets/images/logos/logo_6855da70f240b.jpg', 'Asia/Jakarta');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
