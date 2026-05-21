-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2026 at 06:00 PM
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
-- Database: `soundmarket`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `target_table` varchar(60) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `admin_id`, `action`, `target_table`, `target_id`, `ip_address`, `created_at`) VALUES
(1, 1, 'login', NULL, NULL, '::1', '2026-04-16 17:23:55'),
(2, 1, 'Статус поръчка #13 → paid', 'orders', 13, '::1', '2026-04-16 17:24:18'),
(3, 1, 'Статус поръчка #13 → created', 'orders', 13, '::1', '2026-04-16 17:24:19'),
(4, 1, 'Статус поръчка #13 → delivered', 'orders', 13, '::1', '2026-04-16 17:24:23');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('superadmin','editor') NOT NULL DEFAULT 'editor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$te3gG7QzMdXLjqddyheW5e/4Cm3bQt694T1Wes/7ps/wfzza1u9RO', 'superadmin', '2026-04-15 19:11:29');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `total_eur` int(11) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('created','paid','delivered','cancelled','pending') NOT NULL DEFAULT 'created',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stripe_session_id` varchar(255) DEFAULT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'EUR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `total_eur`, `customer_name`, `phone`, `city`, `address`, `payment_method`, `status`, `created_at`, `stripe_session_id`, `currency`) VALUES
(1, 1, 0, 85, NULL, NULL, NULL, NULL, NULL, 'created', '2026-03-10 09:35:40', NULL, 'EUR'),
(2, 2, 0, 15339, NULL, NULL, NULL, NULL, NULL, 'created', '2026-03-28 13:51:46', NULL, 'EUR'),
(3, 2, 0, 15424, NULL, NULL, NULL, NULL, NULL, 'created', '2026-03-28 13:56:55', NULL, 'EUR'),
(5, 2, 0, 85, NULL, NULL, NULL, NULL, NULL, 'created', '2026-03-28 14:06:23', NULL, 'EUR'),
(6, 2, 0, 15339, NULL, NULL, NULL, NULL, NULL, 'created', '2026-03-28 14:13:50', NULL, 'EUR'),
(7, 2, 1, 0, NULL, NULL, NULL, NULL, NULL, 'paid', '2026-04-07 15:33:25', NULL, 'EUR'),
(8, 2, 4, 0, NULL, NULL, NULL, NULL, NULL, 'paid', '2026-04-07 15:37:42', NULL, 'EUR'),
(9, 2, 4, 0, NULL, NULL, NULL, NULL, NULL, 'paid', '2026-04-09 12:32:11', NULL, 'EUR'),
(10, 2, 5, 0, NULL, NULL, NULL, NULL, NULL, 'paid', '2026-04-09 12:32:11', NULL, 'EUR'),
(11, 1, 0, 20452, NULL, NULL, NULL, NULL, NULL, 'paid', '2026-04-14 18:42:39', NULL, 'EUR'),
(12, 1, 0, 72757, NULL, NULL, NULL, NULL, NULL, 'paid', '2026-04-15 17:49:28', NULL, 'EUR'),
(13, 1, 0, 72759, 'Иван Петров', '0886803702', 'Варна', 'Чайка', 'cod', 'delivered', '2026-04-15 19:35:06', NULL, 'EUR'),
(14, 1, 0, 88, 'дгг сгхгхн', '23452566', 'Варна', 'фегххасдфхнхнсг', 'card', 'pending', '2026-04-16 17:30:37', NULL, 'EUR'),
(15, 1, 0, 72759, 'Николай Костадинов', '23452566', 'Варна', 'вефяржбяефбябд', 'card', 'paid', '2026-04-16 17:59:14', NULL, 'EUR'),
(16, 1, 0, 2556, 'Николай Костадинов', '023452566', 'Варна', 'вефяржбяефбябд', 'card', 'paid', '2026-04-16 20:00:31', 'cs_test_a1dBisWjVNojrHDHGJvoNMVJ1kPWbnjzRkkZJ98j2Pd1kCgr4ZtCjxuXeO', 'EUR'),
(17, 1, 0, 72842, 'Николай Костадинов', '023452566', 'Варна', 'вефяржбяефбябд', 'cod', 'created', '2026-04-16 21:24:47', NULL, 'EUR'),
(18, 1, 0, 72757, 'Николай Костадинов', '023452566', 'Варна', 'вефяржбяефбябд', 'cod', 'created', '2026-04-16 21:25:03', NULL, 'EUR'),
(19, 1, 0, 10226, 'Николай Костадинов', '023452566', 'Варна', 'вефяржбяефбябд', 'cod', 'created', '2026-04-16 21:25:42', NULL, 'EUR'),
(20, 1, 0, 10226, 'Николай Костадинов', '023452566', 'Варна', 'вефяржбяефбябд', 'stripe', 'created', '2026-04-16 21:27:42', 'cs_test_a1PyEmGfsRidyFWROBLobgmVq7p76AhvFHgCofsf1LcC8TCB4rOFXTi3t0', 'EUR'),
(21, 1, 0, 15339, 'Николай Костадинов', '023452566', 'Варна', 'вефяржбяефбябд', 'stripe', 'created', '2026-04-16 21:45:14', 'cs_test_a1Hw2BeDN6kM4NdAsMy55FibBQKYo92ZC2NjFtUISYsOfyL7X1XZAOpKFU', 'EUR');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_eur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_eur`) VALUES
(1, 1, 3, 1, 85),
(2, 2, 4, 1, 15339),
(3, 3, 3, 1, 85),
(4, 3, 4, 1, 15339),
(6, 5, 3, 1, 85),
(7, 6, 4, 1, 15339),
(8, 11, 5, 2, 10226),
(9, 12, 7, 1, 72757),
(10, 13, 7, 1, 72757),
(11, 14, 3, 1, 85),
(12, 15, 7, 1, 72757),
(14, 17, 3, 1, 85),
(15, 17, 7, 1, 72757),
(16, 18, 7, 1, 72757),
(17, 19, 5, 1, 10226),
(18, 20, 5, 1, 10226),
(19, 21, 4, 1, 15339);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `type` enum('beat','music','service','digital') NOT NULL,
  `title` varchar(140) NOT NULL,
  `description` text DEFAULT NULL,
  `price_eur` int(11) NOT NULL,
  `genre` varchar(60) DEFAULT NULL,
  `bpm` int(11) DEFAULT NULL,
  `delivery_days` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `cover_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avg_rating` decimal(3,2) DEFAULT NULL,
  `review_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `owner_id`, `type`, `title`, `description`, `price_eur`, `genre`, `bpm`, `delivery_days`, `file_path`, `cover_path`, `created_at`, `avg_rating`, `review_count`) VALUES
(3, 1, 'beat', 'demo2', 'lorem ipsum', 85, 'hiphop', NULL, NULL, 'uploads/1772566801-9810c893ad02.mp3', 'uploads/covers/1772566801-c8eda5c4334d.png', '2026-03-03 19:40:01', NULL, 0),
(4, 2, 'beat', 'abc', '', 15339, NULL, NULL, NULL, 'uploads/1774705872-f025eded52c1.mp3', 'uploads/covers/1774705872-139754f2e9fb.png', '2026-03-28 13:51:13', NULL, 0),
(5, 2, 'music', 'абц', '', 10226, NULL, NULL, NULL, 'uploads/1774711557-6cc094d31fe1.mp3', NULL, '2026-03-28 15:25:57', NULL, 0),
(6, 2, 'beat', 'test1', 'test', 21525, NULL, NULL, NULL, 'uploads/1774712516-2df03817af11.mp3', 'uploads/covers/1774712516-88da04e5a8fb.jpg', '2026-03-28 15:41:56', NULL, 0),
(7, 1, 'service', 'демо', 'Опиши услугата в полето „Какво включва\" по-горе. Можеш да изброиш всичко — брой ревизии, формати на доставка, бонуси и т.н.', 72757, NULL, NULL, 2, NULL, NULL, '2026-04-15 17:48:55', NULL, 0),
(8, 3, 'music', 'BAD IDEA', 'Aditya Sharma — NCS Release', 999, 'Hip-Hop', NULL, NULL, 'uploads/Aditya Sharma - BAD IDEA [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(9, 3, 'music', 'VANDALI', 'Aditya Sharma — NCS Release', 850, 'Hip-Hop', NULL, NULL, 'uploads/Aditya Sharma - VANDALI [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(10, 4, 'music', 'All Night Long', 'Alex Moretto — NCS Release', 1200, 'Dance', NULL, NULL, 'uploads/Alex Moretto - All Night Long [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(11, 4, 'music', 'Hundred Proof', 'ARIA, HXPETRAIN — NCS Release', 1100, 'Electronic', NULL, NULL, 'uploads/ARIA, HXPETRAIN - Hundred Proof [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(12, 5, 'music', 'Too Late', 'BENJAMINRICH, Daniel Javan — NCS Release', 950, 'R&B', NULL, NULL, 'uploads/BENJAMINRICH, Daniel Javan - Too Late [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(15, 6, 'music', 'Savannah 2026', 'Diviners, Ren — Japanese Version NCS Release', 1500, 'Pop', NULL, NULL, 'uploads/Diviners, Ren - Savannah 2026 (Japanese version) [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(16, 7, 'music', 'The Sky High', 'Elektronomia — NCS Release', 1100, 'EDM', NULL, NULL, 'uploads/Elektronomia - The Sky High [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(17, 7, 'music', 'whatdoyousee', 'GlitchCat, prodBigMike — NCS Release', 900, 'Hip-Hop', NULL, NULL, 'uploads/GlitchCat, prodBigMike - whatdoyousee [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(18, 8, 'music', 'Fly High', 'KDH, Tatsunoshin — NCS Release', 1200, 'Electronic', NULL, NULL, 'uploads/KDH, Tatsunoshin - Fly High [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(19, 8, 'music', 'Damaged', 'Kronus — NCS Release', 1000, 'Dubstep', NULL, NULL, 'uploads/Kronus - Damaged [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(20, 9, 'music', 'Make Me Feel', 'nuphory, Chikaya — NCS Release', 1100, 'Pop', NULL, NULL, 'uploads/nuphory, Chikaya - Make Me Feel [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(21, 9, 'music', 'Archangel', 'Rameses B — NCS Release', 1400, 'Drum & Bass', NULL, NULL, 'uploads/Rameses B - Archangel [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(22, 10, 'music', 'Gamble', 'Rameses B, eerie — NCS Release', 1300, 'Drum & Bass', NULL, NULL, 'uploads/Rameses B, eerie - Gamble [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(23, 10, 'music', 'SCREAM OUT LOUD', 'REKZ! — NCS Release', 950, 'Rock', NULL, NULL, 'uploads/REKZ! - SCREAM OUT LOUD [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(24, 3, 'music', 'Don\'t Wake Me Up', 'RetroVision — NCS Release', 1200, 'EDM', NULL, NULL, 'uploads/RetroVision - Don\'t Wake Me Up [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(25, 4, 'music', 'PRETEND', 'RezaDead, prodcrucial — NCS Release', 900, 'Hip-Hop', NULL, NULL, 'uploads/RezaDead, prodcrucial - PRETEND [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(26, 5, 'music', 'Cash Out', 'Schrandy — NCS Release', 850, 'Trap', NULL, NULL, 'uploads/Schrandy - Cash Out [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(27, 6, 'music', 'Nervous', 'Sean Pitaro — NCS Release', 1000, 'Pop', NULL, NULL, 'uploads/Sean Pitaro - Nervous [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(28, 7, 'music', 'Passport', 'Sean Pitaro — NCS Release', 1000, 'Pop', NULL, NULL, 'uploads/Sean Pitaro - Passport [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(29, 8, 'music', 'Coming Back', 'The Uncommon, Kaphy — NCS Release', 1100, 'Electronic', NULL, NULL, 'uploads/The Uncommon, Kaphy - Coming Back [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(30, 9, 'music', 'devaste', 'TWISTED, kellapsage — NCS Release', 950, 'Dark Trap', NULL, NULL, 'uploads/TWISTED, kellapsage - devaste [NCS Release].mp3', NULL, '2026-04-18 14:44:49', NULL, 0),
(51, 11, 'beat', 'Made For The Game', 'AC13, KAZHI, Jessee, Cartoon — NCS Release', 1300, 'Electronic', NULL, NULL, 'uploads/AC13, KAZHI, Jessee, Cartoon - Made For The Game (ft. Kazhi) [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(52, 11, 'beat', 'akina', 'Ailow — NCS Release', 900, 'Lo-Fi', NULL, NULL, 'uploads/Ailow - akina [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(53, 11, 'beat', 'Like Fire', 'Apollo On The Run, Immy Odon, Biometrix — NCS Release', 1100, 'Electronic', NULL, NULL, 'uploads/Apollo On The Run, Immy Odon, Biometrix - Like Fire [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(54, 11, 'beat', 'Dependant', 'Cartoon — NCS Release', 1000, 'Drum & Bass', NULL, NULL, 'uploads/Cartoon - Dependant [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(55, 11, 'beat', 'Do It All', 'Cartoon — NCS Release', 1000, 'Drum & Bass', NULL, NULL, 'uploads/Cartoon - Do It All [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(56, 11, 'beat', 'Overheat', 'Cartoon — NCS Release', 950, 'Electronic', NULL, NULL, 'uploads/Cartoon - Overheat [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(57, 11, 'beat', 'Spicy', 'Cartoon — NCS Release', 850, 'Electronic', NULL, NULL, 'uploads/Cartoon - Spicy [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(58, 11, 'beat', 'Whiplash', 'Cartoon — NCS Release', 1100, 'Drum & Bass', NULL, NULL, 'uploads/Cartoon - Whiplash [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(59, 11, 'beat', 'All We\'ve Ever Known', 'Cartoon, Fred V, Immy Odon — NCS Release', 1400, 'Drum & Bass', NULL, NULL, 'uploads/Cartoon, Fred V, Immy Odon - All We\'ve Ever Known (ft. Fred V & Immy Odon) [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(60, 11, 'beat', 'Euphoria', 'Cartoon, VALLO, KAZHI, Blooom — NCS Release', 1200, 'Electronic', NULL, NULL, 'uploads/Cartoon, VALLO, KAZHI, Blooom - Euphoria [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(61, 12, 'beat', 'Fading Light', 'Fytch — NCS Release', 1000, 'Electronic', NULL, NULL, 'uploads/Fytch - Fading Light [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(62, 12, 'beat', 'Shadow Glow', 'Janji — NCS Release', 1300, 'EDM', NULL, NULL, 'uploads/Janji - Shadow Glow [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(63, 12, 'beat', 'Wait A While', 'm els, Pasha, Leowi, Cartoon — NCS Release', 1100, 'Electronic', NULL, NULL, 'uploads/m els, Pasha, Leowi, Cartoon - Wait A While (ft. Pasha & m els) [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(64, 13, 'beat', 'So Good', 'More Plastic — NCS Release', 950, 'Pop', NULL, NULL, 'uploads/More Plastic - So Good [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(65, 13, 'beat', 'Around Us', 'Pasha, Carlos Ukareda, VALLO, NCT, Cartoon — NCS', 1200, 'Electronic', NULL, NULL, 'uploads/Pasha, Carlos Ukareda, VALLO, NCT, Cartoon - Around Us (ft. Pasha & Carlos Ukareda) [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(66, 14, 'beat', 'On My Mind', 'Slenderino, Tatsunoshin — NCS Release', 1000, 'Electronic', NULL, NULL, 'uploads/Slenderino, Tatsunoshin - On My Mind [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(67, 14, 'beat', 'Alive', 'Tamlin — NCS Release', 900, 'Indie', NULL, NULL, 'uploads/Tamlin - Alive [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(68, 14, 'beat', 'Sunrise', 'Tatsunoshin, fawlin — NCS Release', 1100, 'Electronic', NULL, NULL, 'uploads/Tatsunoshin, fawlin - Sunrise (feat. fawlin) [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(69, 15, 'beat', 'Pull Me Down', 'CERES, TAME — NCS Release', 1300, 'Electronic', NULL, NULL, 'uploads/CERES, TAME - Pull Me Down [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(70, 15, 'beat', 'Just The Way It Goes', 'Crumb Pit — NCS Release', 800, 'Lo-Fi', NULL, NULL, 'uploads/Crumb Pit - Just The Way It Goes [NCS Release].mp3', NULL, '2026-04-18 15:03:54', NULL, 0),
(71, 16, 'service', 'Професионален Микс', 'Пълен миксинг на песен — до 2 ревизии, доставка в WAV и MP3.', 8000, NULL, NULL, 5, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(72, 16, 'service', 'Мастъринг за стрийминг', 'Мастъринг оптимизиран за Spotify, Apple Music и YouTube. Доставка в 24 часа.', 5000, NULL, NULL, 2, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(73, 17, 'service', 'Микс + Мастъринг пакет', 'Комбиниран пакет — миксинг и мастъринг на 1 песен. До 3 ревизии.', 12000, NULL, NULL, 7, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(74, 17, 'service', 'Запис на вокали', 'Студийни сесии за запис на вокали в Sofia. 2 часа студио + основна обработка.', 15000, NULL, NULL, 3, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(75, 18, 'service', 'Аранжимент и продукция', 'Пълна продукция на песен от нулата — beat, аранжимент, микс.', 25000, NULL, NULL, 14, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(76, 18, 'service', 'Корекция на тон (Pitch)', 'Професионална корекция на вокали с Auto-Tune и Melodyne.', 4000, NULL, NULL, 3, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(77, 19, 'service', 'Drum Programming', 'Програмиране на барабани за Trap, Hip-Hop, R&B и Electronic музика.', 6000, NULL, NULL, 4, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(78, 19, 'service', 'Stem Mixing', 'Миксинг на stems — идеален за продуценти които искат финален polish.', 7000, NULL, NULL, 5, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(79, 20, 'service', 'Вокален коучинг (1 час)', 'Онлайн вокален урок — техника, дишане, интерпретация. Via Zoom.', 3500, NULL, NULL, 1, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(80, 20, 'service', 'Запис и продукция за начинаещи', 'Пакет за начинаещи артисти — консултация, запис и базов микс.', 10000, NULL, NULL, 7, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(81, 21, 'digital', 'Trap Sample Pack Vol.1', 'Колекция от 150 drum samples, 50 melody loops и 20 one-shots. Формат: WAV 24bit.', 2500, NULL, NULL, NULL, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(82, 21, 'digital', 'Lo-Fi Essentials Kit', '80 chord loops, 40 vinyl crackle samples и 30 drum patterns за Lo-Fi продукция.', 2000, NULL, NULL, NULL, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(83, 16, 'digital', 'Hip-Hop Drums Bundle', '200 drum hits — kicks, snares, hi-hats и перкусии. Royalty-free, WAV формат.', 1800, NULL, NULL, NULL, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(84, 17, 'digital', 'Mixing Preset Pack', '25 Equalizer и Compressor presets за Logic Pro и Ableton. Готови шаблони за вокали и инструменти.', 1500, NULL, NULL, NULL, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(85, 18, 'digital', 'Ableton Live Template', 'Пълен Ableton проект за Trap/Hip-Hop продукция. Включва всички routing и effects chains.', 3000, NULL, NULL, NULL, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(86, 19, 'digital', 'FL Studio Mixer Template', 'Професионален FL Studio mixer template с 20 channels, сидчейн и reverb send.', 2200, NULL, NULL, NULL, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(87, 20, 'digital', 'Vocal Processing Chain', 'Готова верига за обработка на вокали — Auto-Tune, EQ, Compression, Reverb. За Logic и Ableton.', 1700, NULL, NULL, NULL, NULL, NULL, '2026-04-18 15:06:38', NULL, 0),
(88, 21, 'digital', 'Electronic Music Starter Pack', '100 synth loops, 60 FX sounds и 40 bass samples. Идеален за EDM и Electronic продукция. WAV 44.1kHz.', 2800, NULL, NULL, NULL, NULL, NULL, '2026-04-18 15:06:38', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `display_name` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `soundcloud` varchar(100) DEFAULT NULL,
  `youtube` varchar(100) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `banner_path` varchar(255) DEFAULT NULL,
  `genre_tags` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `display_name`, `bio`, `location`, `website`, `instagram`, `soundcloud`, `youtube`, `avatar_path`, `banner_path`, `genre_tags`) VALUES
(1, 'niki', 'novacaunt13@gmail.com', '$2y$10$w9qv84dLL0Spwn9Oer.yD.XIHvcA3KseFvMebSMp.d9Teyq/mvAze', '2026-02-28 12:51:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'yao', 'bexita5775@exahut.com', '$2y$10$5d/AZr1xa8udxbdlsJglden7fOhu/sdxJceq/GbcaDSIdk9mxdd1O', '2026-03-28 13:49:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'beatmaker_bg', 'beatmaker@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-01-15 08:23:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'prodby_martin', 'martin.prod@abv.bg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-01-28 12:55:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'trapqueen_sof', 'trapqueen@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-02-03 07:11:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'dj_varna', 'djvarna@outlook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-02-10 16:44:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'lofi_stefan', 'stefan.lofi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-02-19 09:22:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'audiocraft_bg', 'audiocraft@abv.bg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-02-25 06:33:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'hiphop_alex', 'alex.hiphop@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-04 14:08:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'rnb_diana', 'diana.rnb@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-11 11:27:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'cartoon_beats', 'cartoon@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-15 08:11:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'janji_official', 'janji@outlook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-18 12:33:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'fytch_music', 'fytch@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-22 07:44:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'tatsunoshin_dj', 'tatsunoshin@abv.bg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-25 14:55:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'tamlin_sounds', 'tamlin.music@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-04-01 05:22:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'mixmaster_pro', 'mixmaster@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-12 07:15:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'studio_varna', 'studiovarna@abv.bg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-17 12:28:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'soundeng_bg', 'soundeng@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-20 09:44:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'prodhouse_sofia', 'prodhouse@outlook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-28 14:02:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'vocal_coach_bg', 'vocalcoach@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-04-02 05:37:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'mastering_bg', 'masteringbg@abv.bg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-04-05 10:19:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `one_review_per_user` (`product_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_wish` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
