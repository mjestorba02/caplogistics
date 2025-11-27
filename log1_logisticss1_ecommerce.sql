-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2025 at 06:42 AM
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
-- Database: `log1_logisticss1_ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_id` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `status` enum('Available','In Use','Maintenance','Retired','Archived') NOT NULL DEFAULT 'Available',
  `assigned_to` varchar(100) DEFAULT NULL,
  `purchased_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_id`, `name`, `category`, `status`, `assigned_to`, `purchased_date`, `notes`, `archived_at`, `created_at`, `updated_at`) VALUES
(1, 'dawda', 'dd', 'dadaw', 'In Use', 'dawd', '2025-09-17', 'dwadwad', NULL, '2025-09-18 17:26:30', '2025-09-18 17:26:30');

-- --------------------------------------------------------

--
-- Table structure for table `asset_maintenance`
--

CREATE TABLE `asset_maintenance` (
  `id` int(11) NOT NULL,
  `asset_id` varchar(50) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `maintenance_date` date NOT NULL,
  `type` enum('Preventive','Corrective','Predictive') NOT NULL DEFAULT 'Preventive',
  `status` enum('Scheduled','Completed','Cancelled','Archived') NOT NULL DEFAULT 'Scheduled',
  `notes` text DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_maintenance`
--

INSERT INTO `asset_maintenance` (`id`, `asset_id`, `asset_name`, `maintenance_date`, `type`, `status`, `notes`, `archived_at`, `created_at`, `updated_at`) VALUES
(1, 'd11', 'Dell laptop', '2025-09-18', 'Corrective', 'Scheduled', 'did not turn on', NULL, '2025-09-18 17:27:02', '2025-09-18 17:27:02');

-- --------------------------------------------------------

--
-- Table structure for table `asset_replacements`
--

CREATE TABLE `asset_replacements` (
  `id` int(11) NOT NULL,
  `asset_id` varchar(50) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `current_age_years` int(11) NOT NULL DEFAULT 0,
  `replacement_date` date NOT NULL,
  `status` enum('Planned','Approved','Completed','Cancelled','Archived') NOT NULL DEFAULT 'Planned',
  `notes` text DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_replacements`
--

INSERT INTO `asset_replacements` (`id`, `asset_id`, `asset_name`, `current_age_years`, `replacement_date`, `status`, `notes`, `archived_at`, `created_at`, `updated_at`) VALUES
(1, 'd11', 'Dell laptop', 5, '2025-09-18', 'Approved', 'dawdwadwadwa', NULL, '2025-09-18 17:27:23', '2025-09-18 17:27:23');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(6, 2, 1, 2, '2025-09-25 03:58:03', '2025-09-25 03:58:03'),
(7, 2, 2, 1, '2025-09-25 03:58:03', '2025-09-25 03:58:03'),
(8, 3, 3, 1, '2025-09-25 03:58:03', '2025-09-25 03:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `vendor_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(11) NOT NULL,
  `delivery_number` varchar(100) NOT NULL,
  `shipment_id` int(11) DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `items_quantity` int(11) DEFAULT 0,
  `delivery_date` datetime DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cod'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `delivery_number`, `shipment_id`, `origin`, `destination`, `items_quantity`, `delivery_date`, `status`, `notes`, `created_at`, `user_id`, `payment_method`) VALUES
(17, 'DLV-1758463308688', 23, 'Warehouse A', 'john Doe', 1, '2025-09-21 14:01:48', 'Pending', 'Auto-created delivery for shipment 23 (payment: cod)', '2025-09-21 14:01:48', 3, 'cod'),
(18, 'DLV-1758465811627', 24, 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-21 14:43:31', 'Complete', 'Auto-created delivery for shipment 24 (payment: cod)', '2025-09-21 14:43:31', 2, 'cod'),
(19, '', 25, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 02:57:59', 2, 'cod'),
(20, '', 26, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 03:09:18', 2, 'cod'),
(21, 'DLV-1758771343571', 27, 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25 03:35:43', 'Complete', 'Auto-created delivery for shipment 27 (payment: cod)', '2025-09-25 03:35:43', 2, 'cod'),
(22, '', 28, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 03:42:25', 2, 'cod'),
(23, '', 29, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 03:48:48', 2, 'cod'),
(24, '', 30, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 03:56:50', 2, 'cod'),
(25, 'DLV-1758773136428', 31, 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 9, '2025-09-25 04:05:36', 'Complete', 'Auto-created delivery for shipment 31 (payment: cod)', '2025-09-25 04:05:36', 2, 'cod'),
(26, '', 32, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:06:48', 2, 'cod'),
(27, '', 33, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:09:38', 2, 'cod'),
(28, '', 34, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:10:26', 2, 'cod'),
(29, '', 35, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:12:29', 2, 'cod'),
(30, '', 36, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:14:26', 2, 'cod'),
(31, 'DLV-1758773704363', 37, 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25 04:15:04', 'Complete', 'Auto-created delivery for shipment 37 (payment: cod)', '2025-09-25 04:15:04', 2, 'cod'),
(32, '', 38, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:16:57', 2, 'cod'),
(33, '', 39, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:17:25', 2, 'cod'),
(34, '', 40, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:18:28', 2, 'cod'),
(35, '', 41, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:22:10', 2, 'cod'),
(36, '', 42, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:22:32', 2, 'cod'),
(37, '', 43, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:26:56', 2, 'cod'),
(38, '', 44, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:30:19', 2, 'cod'),
(39, '', 45, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:33:15', 2, 'cod'),
(40, '', 46, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:36:19', 2, 'cod'),
(41, '', 47, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:37:16', 2, 'cod'),
(42, '', 48, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:40:29', 2, 'cod'),
(43, '', 49, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:40:56', 2, 'cod'),
(44, '', 50, NULL, NULL, 0, NULL, 'Complete', NULL, '2025-09-25 04:41:11', 2, 'cod');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` varchar(100) NOT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  `archived_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `name`, `type`, `file_path`, `uploaded_by`, `uploaded_at`, `archived_at`, `created_at`, `updated_at`) VALUES
(1, 'dd', 'Contract', '/uploads/milestones__1__1758187659.csv', 'Julian Castañares', '2025-09-18 17:27:39', '2025-09-18 17:32:33', '2025-09-18 17:27:39', '2025-09-18 17:32:33'),
(2, 'contract Laptops', 'Contract', '/uploads/attendance_report_1758187990.pdf', 'Julian Castañares', '2025-09-18 17:33:10', '2025-09-18 17:41:27', '2025-09-18 17:33:10', '2025-09-18 17:41:27'),
(3, 'contract Laptops', 'Contract', '/logistics1_ecommerce/uploads/attendance_report__1__1758188806.pdf', 'Julian Castañares', '2025-09-18 17:46:46', NULL, '2025-09-18 17:46:46', '2025-09-18 17:46:46'),
(4, 'Laptops', 'Audit', '/logistics1_ecommerce/uploads/123_1758189410.jpeg', 'Julian Castañares', '2025-09-18 17:56:50', NULL, '2025-09-18 17:56:50', '2025-09-18 17:56:50'),
(5, 'supplier Audit', 'Audit', '/logistics1_ecommerce/uploads/82ead1bd-def4-4cd3-9678-1079dd72e67d__1__1758190750.jfif', 'Julian Castañares', '2025-09-18 18:19:10', NULL, '2025-09-18 18:19:10', '2025-09-18 18:19:10');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `stock_level` int(11) NOT NULL,
  `reorder_level` int(11) NOT NULL,
  `supplier` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `last_restocked` date NOT NULL,
  `notes` text DEFAULT NULL,
  `product_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `item_name`, `sku`, `category`, `stock_level`, `reorder_level`, `supplier`, `price`, `last_restocked`, `notes`, `product_photo`, `created_at`) VALUES
(10, 'Gaming Headset', '00001', 'Computers Accessory ', 100, 5, 'Inplay gears', 15000.00, '2025-09-01', 'Gaming Headset Stereo Surround Sound Gaming Headphones with Breathing RGB Light & Adjustable Mic for PS4 PS5 PC Xbox One Laptop Mac', '68cf5f5dc45c6_gaming_headset.jpg', '2025-09-20 12:30:02'),
(12, 'Gaming Keyboard with mouse', '00002', 'Computers Accessory ', 20, 4, 'Inplay gears', 800.00, '2025-09-05', 'Wireless Gaming Keyboard and Mouse,RGB Backlit Rechargeable Keyboard Mouse with 5000mAh Battery Metal Panel,Removable Hand Rest Mechanical Feel Keyboard and 7 Color Gaming Mute Mouse for PC Gamer', '68cec7bd32b5d_key_mouse.jpg', '2025-09-20 15:26:53'),
(13, 'Jungle Leopard KF420', '00004', 'Computers Accessory ', 20, 5, 'kldz168168', 1627.00, '2025-09-16', 'Jungle Leopard KF420 Digital ARGB CPU Cooler Black / White 4 Heat Pipes Temperature Digital Display Infinity Mirror Top Cover 5V 3PIN ARGB Mobo Sync Intel LGA1155 LGA2011 AMD AM4', '68cf665e85eca_cooler.jpg', '2025-09-21 01:43:30'),
(14, 'G34WQC Gaming Monitor', '00003', 'Computers Accessory ', 1, 2, 'Gigibye', 80000.00, '2025-09-21', 'G34WQC Gaming Monitor Key Features | Monitor - GIGABYTE Philippines', '68cf5f2d2ca13_mon.png', '2025-09-21 02:13:01');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(64) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `shipment_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `delivery_from` varchar(255) DEFAULT NULL,
  `delivery_to` varchar(255) DEFAULT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`items`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `date`, `due_date`, `subtotal`, `notes`, `created_at`, `updated_at`, `shipment_id`, `user_id`, `user_name`, `delivery_from`, `delivery_to`, `items`) VALUES
(8, 'INV-20250925-000031', '2025-09-25', NULL, 30800.00, 'Auto-generated from shipment', '2025-09-25 04:05:36', '2025-09-25 04:05:36', 31, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"Gaming Headset\",\"quantity\":2,\"unit_price\":15000,\"total\":30000},{\"name\":\"Gaming Keyboard with mouse\",\"quantity\":1,\"unit_price\":800,\"total\":800}]'),
(9, 'INV-20250925-000032', '2025-09-25', NULL, 30800.00, 'Auto-generated from shipment', '2025-09-25 04:06:48', '2025-09-25 04:06:48', 32, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"Gaming Headset\",\"quantity\":2,\"unit_price\":15000,\"total\":30000},{\"name\":\"Gaming Keyboard with mouse\",\"quantity\":1,\"unit_price\":800,\"total\":800}]'),
(10, 'INV-20250925-000033', '2025-09-25', NULL, 30800.00, 'Auto-generated from shipment', '2025-09-25 04:09:38', '2025-09-25 04:09:38', 33, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"Gaming Headset\",\"quantity\":2,\"unit_price\":15000,\"total\":30000},{\"name\":\"Gaming Keyboard with mouse\",\"quantity\":1,\"unit_price\":800,\"total\":800}]'),
(11, 'INV-20250925-000034', '2025-09-25', NULL, 30800.00, 'Auto-generated from shipment', '2025-09-25 04:10:26', '2025-09-25 04:10:26', 34, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"Gaming Headset\",\"quantity\":2,\"unit_price\":15000,\"total\":30000},{\"name\":\"Gaming Keyboard with mouse\",\"quantity\":1,\"unit_price\":800,\"total\":800}]'),
(12, 'INV-20250925-000035', '2025-09-25', NULL, 30800.00, 'Auto-generated from shipment', '2025-09-25 04:12:29', '2025-09-25 04:12:29', 35, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"Gaming Headset\",\"quantity\":2,\"unit_price\":15000,\"total\":30000},{\"name\":\"Gaming Keyboard with mouse\",\"quantity\":1,\"unit_price\":800,\"total\":800}]'),
(13, 'INV-20250925-000036', '2025-09-25', NULL, 30800.00, 'Auto-generated from shipment', '2025-09-25 04:14:26', '2025-09-25 04:14:26', 36, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"Gaming Headset\",\"quantity\":2,\"unit_price\":15000,\"total\":30000},{\"name\":\"Gaming Keyboard with mouse\",\"quantity\":1,\"unit_price\":800,\"total\":800}]'),
(14, 'INV-20250925-000037', '2025-09-25', NULL, 30800.00, 'Auto-generated from shipment', '2025-09-25 04:15:04', '2025-09-25 04:15:04', 37, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"Gaming Headset\",\"quantity\":2,\"unit_price\":15000,\"total\":30000},{\"name\":\"Gaming Keyboard with mouse\",\"quantity\":1,\"unit_price\":800,\"total\":800}]'),
(15, 'INV-20250925-000038', '2025-09-25', NULL, 0.00, 'Auto-generated from shipment', '2025-09-25 04:16:57', '2025-09-25 04:16:57', 38, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[]'),
(16, 'INV-20250925-000039', '2025-09-25', NULL, 0.00, 'Auto-generated from shipment', '2025-09-25 04:17:25', '2025-09-25 04:17:25', 39, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[]'),
(17, 'INV-20250925-000040', '2025-09-25', NULL, 30800.00, 'Auto-generated from shipment', '2025-09-25 04:18:28', '2025-09-25 04:18:28', 40, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"Gaming Headset\",\"quantity\":2,\"unit_price\":15000,\"total\":30000},{\"name\":\"Gaming Keyboard with mouse\",\"quantity\":1,\"unit_price\":800,\"total\":800}]'),
(18, 'INV-20250925-000041', '2025-09-25', NULL, 0.00, 'Auto-generated from shipment', '2025-09-25 04:22:10', '2025-09-25 04:22:10', 41, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[]'),
(19, 'INV-20250925-000042', '2025-09-25', NULL, 0.00, 'Auto-generated from shipment', '2025-09-25 04:22:32', '2025-09-25 04:22:32', 42, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[]'),
(20, 'INV-20250925-000043', '2025-09-25', NULL, 0.00, 'Auto-generated from shipment', '2025-09-25 04:26:56', '2025-09-25 04:26:56', 43, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[]'),
(21, 'INV-20250925-000044', '2025-09-25', NULL, 30800.00, 'Auto-generated from shipment', '2025-09-25 04:30:19', '2025-09-25 04:30:19', 44, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"Gaming Headset\",\"quantity\":2,\"unit_price\":15000,\"total\":30000},{\"name\":\"Gaming Keyboard with mouse\",\"quantity\":1,\"unit_price\":800,\"total\":800}]'),
(22, 'INV-20250925-000045', '2025-09-25', NULL, 0.00, 'Auto-generated from shipment', '2025-09-25 04:33:15', '2025-09-25 04:33:15', 45, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[]'),
(23, 'INV-20250925-000046', '2025-09-25', NULL, 0.00, 'Auto-generated from shipment', '2025-09-25 04:36:19', '2025-09-25 04:36:19', 46, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[]'),
(24, 'INV-20250925-000047', '2025-09-25', NULL, 30800.00, 'Auto-generated from shipment', '2025-09-25 04:37:16', '2025-09-25 04:37:16', 47, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"Gaming Headset\",\"quantity\":2,\"unit_price\":15000,\"total\":30000},{\"name\":\"Gaming Keyboard with mouse\",\"quantity\":1,\"unit_price\":800,\"total\":800}]'),
(25, 'INV-20250925-000048', '2025-09-25', NULL, 1627.00, 'Auto-generated from shipment', '2025-09-25 04:40:29', '2025-09-25 04:40:29', 48, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"Jungle Leopard KF420\",\"quantity\":1,\"unit_price\":1627,\"total\":1627}]'),
(26, 'INV-20250925-000049', '2025-09-25', NULL, 80000.00, 'Auto-generated from shipment', '2025-09-25 04:40:56', '2025-09-25 04:40:56', 49, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"G34WQC Gaming Monitor\",\"quantity\":1,\"unit_price\":80000,\"total\":80000}]'),
(27, 'INV-20250925-000050', '2025-09-25', NULL, 81627.00, 'Auto-generated from shipment', '2025-09-25 04:41:11', '2025-09-25 04:41:11', 50, 2, 'Julian Castañares', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', '[{\"name\":\"G34WQC Gaming Monitor\",\"quantity\":1,\"unit_price\":80000,\"total\":80000},{\"name\":\"Jungle Leopard KF420\",\"quantity\":1,\"unit_price\":1627,\"total\":1627}]');

-- --------------------------------------------------------

--
-- Table structure for table `procurement_contracts`
--

CREATE TABLE `procurement_contracts` (
  `id` int(11) NOT NULL,
  `reference` varchar(100) NOT NULL,
  `supplier` varchar(255) NOT NULL,
  `status` enum('Active','Pending','Terminated','Expired','Cancelled','Completed','Archived') NOT NULL DEFAULT 'Pending',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `archived_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procurement_contracts`
--

INSERT INTO `procurement_contracts` (`id`, `reference`, `supplier`, `status`, `start_date`, `end_date`, `total_value`, `currency`, `archived_at`, `created_at`, `updated_at`) VALUES
(1, 'wdwadawdwad', 'jane doe', 'Archived', '2025-09-18', '2025-09-19', 50000.00, 'php', '2025-09-18 16:11:32', '2025-09-18 16:11:15', '2025-09-18 16:11:32'),
(3, 'ddaw', 'jane doe', 'Expired', '2025-09-18', '2025-09-30', 50000.00, 'USD', NULL, '2025-09-18 16:17:00', '2025-09-19 09:55:15');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `image`, `created_at`) VALUES
(1, 'Gaming Headset', 15000.00, 'Gaming Headset Stereo Surround Sound Gaming Headphones with Breathing RGB Light & Adjustable Mic for PS4 PS5 PC Xbox One Laptop Mac', '68cf5f5dc45c6_gaming_headset.jpg', '2025-09-25 03:53:04'),
(2, 'Gaming Keyboard with mouse', 800.00, 'Wireless Gaming Keyboard and Mouse,RGB Backlit Rechargeable Keyboard Mouse with 5000mAh Battery Metal Panel,Removable Hand Rest Mechanical Feel Keyboard and 7 Color Gaming Mute Mouse for PC Gamer', '68cec7bd32b5d_key_mouse.jpg', '2025-09-25 03:53:04'),
(3, 'Jungle Leopard KF420', 1627.00, 'Jungle Leopard KF420 Digital ARGB CPU Cooler Black / White 4 Heat Pipes Temperature Digital Display Infinity Mirror Top Cover 5V 3PIN ARGB Mobo Sync Intel LGA1155 LGA2011 AMD AM4', '68cf665e85eca_cooler.jpg', '2025-09-25 03:53:04'),
(4, 'G34WQC Gaming Monitor', 80000.00, 'G34WQC Gaming Monitor Key Features | Monitor - GIGABYTE Philippines', '68cf5f2d2ca13_mon.png', '2025-09-25 03:53:04');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` int(11) NOT NULL,
  `request_number` varchar(50) NOT NULL,
  `requested_by` varchar(100) NOT NULL,
  `items_quantity` int(11) NOT NULL DEFAULT 0,
  `request_date` date NOT NULL,
  `status` enum('Pending','Approved','Rejected','Archived') NOT NULL DEFAULT 'Pending',
  `archived_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`id`, `request_number`, `requested_by`, `items_quantity`, `request_date`, `status`, `archived_at`, `created_at`, `updated_at`) VALUES
(1, '#P 01', 'jame smith123', 5, '2025-09-18', 'Archived', '2025-09-18 17:26:00', '2025-09-17 22:46:02', '2025-09-18 17:26:00'),
(2, '#P 02', 'John Doe', 5, '2025-09-18', 'Pending', NULL, '2025-09-17 23:14:03', '2025-09-17 23:14:03');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `shipment_number` varchar(50) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `items_quantity` int(11) NOT NULL,
  `dispatch_date` date NOT NULL,
  `status` enum('Pending','In Transit','Delivered','Cancelled','Archived') DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cod'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `shipment_number`, `origin`, `destination`, `items_quantity`, `dispatch_date`, `status`, `notes`, `created_at`, `user_id`, `payment_method`) VALUES
(2, 'SHP-003', 'Warehouse C', 'Store D', 200, '2025-09-05', 'Pending', 'Urgent deliveryssss', '2025-09-01 03:48:00', NULL, 'cod'),
(4, 'SHP00001', 'warehouse ', 'bahay', 1000, '2025-09-01', 'In Transit', '', '2025-09-01 05:34:08', NULL, 'cod'),
(5, 'B111331', 'warehouse', 'barangay tibay', 10, '2025-09-02', 'Delivered', '', '2025-09-02 12:27:35', NULL, 'cod'),
(6, 'dawd123', 'warehouse A', 'Warehouse B', 50, '2025-09-18', 'Cancelled', 'dadaodhawdw', '2025-09-18 08:28:13', NULL, 'cod'),
(17, 'SHP-1758457538570', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-21', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-21 12:25:38', 2, 'cod'),
(18, 'SHP-1758457683360', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-18', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-21 12:28:03', 2, 'cod'),
(19, 'SHP-1758458840688', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-21', 'Cancelled', 'Created from cart checkout (payment: cod)', '2025-09-21 12:47:20', 2, 'cod'),
(20, 'SHP-1758462347422', 'Warehouse A', 'john Doe', 1, '2025-09-21', 'Cancelled', 'Created from cart checkout (payment: cod)', '2025-09-21 13:45:47', 3, 'cod'),
(21, 'SHP-1758463027939', 'Warehouse A', 'john Doe', 1, '2025-09-21', 'Pending', 'Created from cart checkout (payment: cod)', '2025-09-21 13:57:07', 3, 'cod'),
(22, 'SHP-1758463171769', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 2, '2025-09-21', 'Cancelled', 'Created from cart checkout (payment: cod)', '2025-09-21 13:59:31', 2, 'cod'),
(23, 'SHP-1758463308684', 'Warehouse A', 'john Doe', 1, '2025-09-21', 'Pending', 'Created from cart checkout (payment: cod)', '2025-09-21 14:01:48', 3, 'cod'),
(24, 'SHP-1758465811623', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-21', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-21 14:43:31', 2, 'cod'),
(25, 'SHP-1758769079958', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 02:57:59', 2, 'cod'),
(26, 'SHP-1758769758453', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 2, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 03:09:18', 2, 'cod'),
(27, 'SHP-1758771343566', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 03:35:43', 2, 'cod'),
(28, 'SHP-1758771745885', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 03:42:25', 2, 'cod'),
(29, 'SHP-1758772128457', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 03:48:48', 2, 'cod'),
(30, 'SHP-1758772610353', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 03:56:50', 2, 'cod'),
(31, 'SHP-1758773136402', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 9, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:05:36', 2, 'cod'),
(32, 'SHP-1758773208921', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:06:48', 2, 'cod'),
(33, 'SHP-1758773378324', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:09:38', 2, 'cod'),
(34, 'SHP-1758773426521', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:10:26', 2, 'cod'),
(35, 'SHP-1758773549893', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:12:29', 2, 'cod'),
(36, 'SHP-1758773666178', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:14:26', 2, 'cod'),
(37, 'SHP-1758773704357', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:15:04', 2, 'cod'),
(38, 'SHP-1758773817826', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 2, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:16:57', 2, 'cod'),
(39, 'SHP-1758773845319', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:17:25', 2, 'cod'),
(40, 'SHP-1758773908386', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:18:28', 2, 'cod'),
(41, 'SHP-1758774130532', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:22:10', 2, 'cod'),
(42, 'SHP-1758774152066', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:22:32', 2, 'cod'),
(43, 'SHP-1758774416157', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:26:56', 2, 'cod'),
(44, 'SHP-1758774619477', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:30:19', 2, 'cod'),
(45, 'SHP-1758774795386', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:33:15', 2, 'cod'),
(46, 'SHP-1758774979757', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:36:19', 2, 'cod'),
(47, 'SHP-1758775035984', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:37:16', 2, 'cod'),
(48, 'SHP-1758775229569', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:40:29', 2, 'cod'),
(49, 'SHP-1758775256155', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 1, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:40:56', 2, 'cod'),
(50, 'SHP-1758775271116', 'Warehouse A', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 2, '2025-09-25', 'Delivered', 'Created from cart checkout (payment: cod)', '2025-09-25 04:41:11', 2, 'cod');

-- --------------------------------------------------------

--
-- Table structure for table `shipment_milestones`
--

CREATE TABLE `shipment_milestones` (
  `id` int(11) NOT NULL,
  `shipment_number` varchar(50) NOT NULL,
  `milestone` varchar(255) NOT NULL,
  `status` enum('Pending','In Progress','Completed','Archived') NOT NULL DEFAULT 'Pending',
  `milestone_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipment_milestones`
--

INSERT INTO `shipment_milestones` (`id`, `shipment_number`, `milestone`, `status`, `milestone_date`, `notes`, `archived_at`, `created_at`, `updated_at`) VALUES
(1, 'dawd123', '123', 'Archived', '2025-09-18', 'dawdawdaw', NULL, '2025-09-18 16:34:40', '2025-09-19 09:29:41'),
(2, 'S01', 'dawdawd', 'In Progress', '2025-09-19', 'dawdawdawdawdasdwdwadsadw', NULL, '2025-09-19 09:30:48', '2025-09-19 09:30:48');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `rfqs_sent` int(11) NOT NULL DEFAULT 0,
  `bids_submitted` int(11) NOT NULL DEFAULT 0,
  `status` enum('Active','Inactive','Archived') NOT NULL DEFAULT 'Active',
  `archived_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `email`, `rfqs_sent`, `bids_submitted`, `status`, `archived_at`, `created_at`, `updated_at`) VALUES
(1, 'Julian Castañares123', 'supplierA@example.com', 5, 1, 'Active', NULL, '2025-09-17 23:10:50', '2025-09-18 15:41:30'),
(2, 'James Smith', 'jamesSmith@gmail.com', 5, 1, 'Archived', '2025-09-18 18:52:19', '2025-09-18 18:52:07', '2025-09-18 18:52:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(3, 'John', 'john11@example.com', '$2y$10$d2jiE.5JwoPa0/DxezkhZ.QLKVFuaKc69ELQmwYUPh3sgXEKfiyau', '2025-09-01 02:15:47'),
(8, 'john', 'sanchezland222@gmail.com', '$2y$10$kyE5IfD180rMLTp4UXoa6.PQ7vIO7KobGW7Lr9C5Drb53R1ZCN94C', '2025-09-01 02:36:44'),
(11, 'lance', 'sanchezlando3333@gmail.com', '$2y$10$7VL2IQUSzV0Puac6XY8qFuzR78RdNJXvng9tGJVN2MzaQW0.FNMvG', '2025-09-01 05:09:44'),
(12, 'manang', 'sanchezlando3334@gmail.com', '$2y$10$Hy4EhwHmh4RIFamsYvClwOaK44n29cO5p0CsffiPDQSIePTIPxNI2', '2025-09-01 05:11:43'),
(13, 'Ariel mendoza', 'mendoza.ariel.dalubatan@gmail.com', '$2y$10$v8FEz7mhsNiEoeUQhdVGde8qk7iVsXuMcLSLLT3Xarg7AhwPuT.E6', '2025-09-01 05:23:39'),
(14, 'andrei', 'andrei@email.com', '$2y$10$ler6QX1RkAh7oT/jsK9f8e50vMaWVmFnW6K621Hc4ClcsCJwn3sKa', '2025-09-01 05:29:14'),
(15, 'Daniel Zabat', 'danielzabat01@gmail.com', '$2y$10$E/9QEXMNiY0yXzLTqIojQuIm48PzOjCj03PA6Gsf5L7QyZVk5tW9a', '2025-09-01 05:33:09'),
(16, 'randy alvarez', 'randyalvarez102716@gmail.com', '$2y$10$NaVr3VlwDpK1ADVw9qFf1e16YEhMZUTE8PJ85v0g//MG0XvbOxR2a', '2025-09-01 06:00:50'),
(17, 'Harley', 'romharleyn@gmail.com', '$2y$10$SyDtY9T0ejHdOZ.h4nMzHewrC5MZxQSudbQU/oO8E9wBGVA4uxFji', '2025-09-02 12:16:30'),
(18, 'Julian Castañares', 'juliancastanares1@gmail.com', '$2y$10$nft883U3mCBmCKzrKy4Evuw8okSe5TP8jPqEWLW.K8AQvpTgGhryq', '2025-09-17 12:05:13');

-- --------------------------------------------------------

--
-- Table structure for table `user_vendor`
--

CREATE TABLE `user_vendor` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_vendor`
--

INSERT INTO `user_vendor` (`id`, `first_name`, `last_name`, `email`, `password`, `company_name`, `phone`, `address`, `bio`, `profile_image`, `is_active`, `is_email_verified`, `verification_token`, `reset_token`, `reset_token_expiry`, `created_at`, `updated_at`) VALUES
(1, 'Jane', 'Smith', 'jane@example.com', '$2y$10$Kb.uT/FtdNns9qE.z6D.2.qCObhYTQVBcSkwqf2IZQ8yYNgWSL7Zy', 'Jane Tech Store', '', '', NULL, NULL, 1, 0, '966afe6dd3ae2c8697a5cbbfe6d074aa95f8f2af39b774f07642dc8aafccdc1c', NULL, NULL, '2025-09-21 03:12:12', '2025-09-21 03:12:12'),
(2, 'Julian', 'Castañares', 'juliancastanares1@gmail.com', '$2y$10$nX6uKhABkdSLUlDgp09OWuI..OiJpUbQCaxQQrc0N0WKzbQaMH3/C', '', '09077386196', 'prk.Tubig, Cagpangi,Cabangahan, Tago, Surigao del Sur', 'dawddawd', 'profile_2_1758463396.jfif', 1, 0, NULL, NULL, NULL, '2025-09-21 03:53:10', '2025-09-21 14:03:16'),
(3, 'john', 'Doe', 'john@gmail.com', '$2y$10$DLN47Z5IvR7kUm73DM.0zeKFo7aGXlA.cg95QNXw/JgFwwJR2NtQ2', 'Tech Corp', NULL, NULL, NULL, 'profile_3_1758462324.jfif', 1, 0, NULL, NULL, NULL, '2025-09-21 13:44:56', '2025-09-21 13:45:24');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `vendor_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist_items`
--

INSERT INTO `wishlist_items` (`id`, `vendor_id`, `product_id`, `created_at`) VALUES
(6, 3, 13, '2025-09-21 13:45:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asset_id` (`asset_id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `asset_maintenance`
--
ALTER TABLE `asset_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asset_id` (`asset_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date` (`maintenance_date`);

--
-- Indexes for table `asset_replacements`
--
ALTER TABLE `asset_replacements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asset_id` (`asset_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_replacement_date` (`replacement_date`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_vendor_product` (`vendor_id`,`product_id`),
  ADD KEY `idx_vendor` (`vendor_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`);

--
-- Indexes for table `procurement_contracts`
--
ALTER TABLE `procurement_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_number` (`request_number`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipment_milestones`
--
ALTER TABLE `shipment_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shipment_number` (`shipment_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_milestone_date` (`milestone_date`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_vendor`
--
ALTER TABLE `user_vendor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_verification_token` (`verification_token`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_vendor_product` (`vendor_id`,`product_id`),
  ADD KEY `idx_vendor` (`vendor_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `asset_maintenance`
--
ALTER TABLE `asset_maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `asset_replacements`
--
ALTER TABLE `asset_replacements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `procurement_contracts`
--
ALTER TABLE `procurement_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `shipment_milestones`
--
ALTER TABLE `shipment_milestones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `user_vendor`
--
ALTER TABLE `user_vendor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
