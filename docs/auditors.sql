-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2026 at 03:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `auditors`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_registration`
--

CREATE TABLE `attendance_registration` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_code` varchar(250) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_registration_logs`
--

CREATE TABLE `attendance_registration_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_code` varchar(255) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attire_colors`
--

CREATE TABLE `attire_colors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attire_sizes`
--

CREATE TABLE `attire_sizes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attire_sizes`
--

INSERT INTO `attire_sizes` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Small', NULL, NULL),
(2, 'Medium', NULL, NULL),
(3, 'Large', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attire_types`
--

CREATE TABLE `attire_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `authorization_logs`
--

CREATE TABLE `authorization_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_code` varchar(250) DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `action` varchar(250) DEFAULT NULL,
  `performed_by` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `authorize_event_participants`
--

CREATE TABLE `authorize_event_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_code` varchar(250) DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `status` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `authorize_meal_coupon`
--

CREATE TABLE `authorize_meal_coupon` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_code` varchar(250) DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `status` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookers`
--

CREATE TABLE `bookers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bookingID` varchar(255) NOT NULL,
  `event_id` varchar(250) NOT NULL,
  `event_selection` enum('governance','main','both') DEFAULT 'both',
  `accommodation` tinyint(1) NOT NULL DEFAULT 0,
  `hotel_choice` varchar(255) DEFAULT NULL,
  `spouse_included` tinyint(1) NOT NULL DEFAULT 0,
  `extras` int(11) NOT NULL DEFAULT 0,
  `room_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `room_allocated` varchar(100) DEFAULT NULL,
  `reference_code` varchar(250) DEFAULT NULL,
  `attire_size_id` int(11) NOT NULL,
  `memberID` varchar(250) DEFAULT NULL,
  `name` varchar(250) DEFAULT NULL,
  `status` varchar(250) DEFAULT NULL,
  `datejoined` varchar(250) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `phone_number` varchar(250) DEFAULT NULL,
  `company` varchar(250) DEFAULT NULL,
  `position` varchar(250) DEFAULT NULL,
  `gender` varchar(250) DEFAULT NULL,
  `usd_fee` decimal(15,2) DEFAULT NULL,
  `date_paid` varchar(250) DEFAULT NULL,
  `check_in` varchar(250) DEFAULT NULL,
  `check_out` varchar(250) DEFAULT NULL,
  `total_cost` decimal(15,2) DEFAULT NULL,
  `booking_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `receipt_number` varchar(250) DEFAULT NULL,
  `date_verified` varchar(250) DEFAULT NULL,
  `amount_paid` decimal(15,2) DEFAULT NULL,
  `mode_of_attendance` varchar(250) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT NULL,
  `proof_of_payment` varchar(250) DEFAULT NULL,
  `invoice_status` enum('pending','sent','paid') NOT NULL DEFAULT 'pending',
  `invoice_sent_at` timestamp NULL DEFAULT NULL,
  `member_type` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookers`
--

INSERT INTO `bookers` (`id`, `bookingID`, `event_id`, `event_selection`, `accommodation`, `hotel_choice`, `spouse_included`, `extras`, `room_type_id`, `room_allocated`, `reference_code`, `attire_size_id`, `memberID`, `name`, `status`, `datejoined`, `email`, `phone_number`, `company`, `position`, `gender`, `usd_fee`, `date_paid`, `check_in`, `check_out`, `total_cost`, `booking_status`, `receipt_number`, `date_verified`, `amount_paid`, `mode_of_attendance`, `balance`, `proof_of_payment`, `invoice_status`, `invoice_sent_at`, `member_type`, `created_at`, `updated_at`) VALUES
(23, 'IIA-BK-6A13CDBF57717', 'IIA-GF-2026', NULL, 1, 'sunbird_nkopola', 1, 0, NULL, NULL, NULL, 1, NULL, 'Wongani Msumba', NULL, NULL, 'wonganimsumba0@gmail.com', '0991234567', 'Celeris', NULL, NULL, NULL, '2026-05-25 11:53:21', NULL, NULL, 2700000.00, 'Confirmed', NULL, NULL, NULL, NULL, NULL, 'proof_of_payments/Vz9257VFN1hWDaFsNkN095hvHXOgzQyvECIELlKa.png', 'paid', NULL, 'Member', '2026-05-25 04:19:11', '2026-05-25 09:53:21'),
(24, 'IIA-BK-6A1419128AEE3', 'IIA-AC-2026', NULL, 1, 'sun_n_sand_holiday_resort', 0, 0, NULL, NULL, NULL, 1, NULL, 'Wongani Msumba', NULL, NULL, 'wonganimsumba0@gmail.com', '0991234567', 'Celeris', NULL, NULL, NULL, '2026-05-25 12:04:28', NULL, NULL, 1755000.00, 'Confirmed', NULL, NULL, NULL, NULL, NULL, NULL, 'paid', NULL, 'Member', '2026-05-25 09:40:34', '2026-05-25 10:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `booking_forms`
--

CREATE TABLE `booking_forms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `questions` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_invoices`
--

CREATE TABLE `booking_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','sent','paid','overdue') NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(250) NOT NULL,
  `event_type` enum('governance','main') DEFAULT NULL,
  `event_name` varchar(250) NOT NULL,
  `theme` varchar(250) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `event_venue` varchar(250) NOT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `event_status` varchar(250) NOT NULL,
  `event_gps_coordinates` varchar(250) DEFAULT NULL,
  `booking_start_time` timestamp NULL DEFAULT NULL,
  `booking_end_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_id`, `event_type`, `event_name`, `theme`, `start_date`, `end_date`, `event_venue`, `venue`, `event_status`, `event_gps_coordinates`, `booking_start_time`, `booking_end_time`, `created_at`, `updated_at`) VALUES
(1, 'IIA-GF-2026', 'governance', '2026 Governance Forum', 'The Currency of Trust: Governance as Strategy, Assurance as Proof', '2026-09-07', '2026-09-10', 'Sunbird Nkopola', 'Mangochi', 'active', '-14.0500,35.1500', '2026-05-24 13:45:18', '2026-05-28 13:45:26', NULL, NULL),
(2, 'IIA-AC-2026', 'main', '2026 Annual Conference', 'The Currency of Trust: Governance as Strategy, Assurance as Proof', '2026-09-10', '2026-09-13', 'Sun N Sand Holiday Resort', 'Mangochi', 'active', '-14.0600,35.1600', '2026-05-24 13:45:33', '2026-05-27 13:45:38', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(250) NOT NULL,
  `reference_code` varchar(255) DEFAULT NULL,
  `registered` varchar(250) DEFAULT NULL,
  `phone_number` varchar(250) DEFAULT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  `participant` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `hotel_id` varchar(255) DEFAULT NULL,
  `room_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `room_allocated` varchar(100) DEFAULT NULL,
  `accommodation` tinyint(1) NOT NULL DEFAULT 0,
  `event_selection` enum('governance','main','both') DEFAULT 'both',
  `meals` int(11) NOT NULL DEFAULT 0,
  `extra_meals` varchar(255) DEFAULT NULL,
  `date_paid` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `event_name` varchar(250) DEFAULT NULL,
  `qr_code` text DEFAULT NULL,
  `qrcode_path` varchar(2048) DEFAULT NULL,
  `balance` varchar(250) DEFAULT NULL,
  `invoice_number` varchar(250) DEFAULT NULL,
  `pending_status` varchar(255) DEFAULT NULL,
  `status` varchar(250) DEFAULT NULL,
  `type` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_participants`
--

INSERT INTO `event_participants` (`id`, `event_id`, `reference_code`, `registered`, `phone_number`, `email_address`, `participant`, `company_name`, `hotel_id`, `room_type_id`, `room_allocated`, `accommodation`, `event_selection`, `meals`, `extra_meals`, `date_paid`, `name`, `file_path`, `event_name`, `qr_code`, `qrcode_path`, `balance`, `invoice_number`, `pending_status`, `status`, `type`, `created_at`, `updated_at`) VALUES
(8, 'IIA-GF-2026', NULL, NULL, '0991234567', 'wonganimsumba0@gmail.com', 'Wongani Msumba', 'Celeris', 'sunbird_nkopola', NULL, NULL, 1, 'both', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'IIA-BK-6A13CDBF57717', 'approved', 'Member', 'main', '2026-05-25 09:53:21', '2026-05-25 09:53:21'),
(9, 'IIA-AC-2026', NULL, NULL, '0991234567', 'wonganimsumba0@gmail.com', 'Wongani Msumba', 'Celeris', 'sun_n_sand_holiday_resort', NULL, NULL, 1, 'both', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'IIA-BK-6A1419128AEE3', 'approved', 'Member', 'main', '2026-05-25 10:04:27', '2026-05-25 10:04:27');

-- --------------------------------------------------------

--
-- Table structure for table `event_prices`
--

CREATE TABLE `event_prices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(250) NOT NULL,
  `member_type` varchar(50) DEFAULT NULL,
  `accommodation` tinyint(1) NOT NULL DEFAULT 0,
  `hotel` enum('sunbird_nkopola','sun_n_sand_holiday_resort') DEFAULT NULL,
  `spouse_included` tinyint(1) NOT NULL DEFAULT 0,
  `event_type` enum('governance','main') DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `extra_person_price` decimal(15,2) NOT NULL DEFAULT 600000.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_prices`
--

INSERT INTO `event_prices` (`id`, `event_id`, `member_type`, `accommodation`, `hotel`, `spouse_included`, `event_type`, `status`, `price`, `extra_person_price`, `created_at`, `updated_at`) VALUES
(1, 'IIA-GF-2026', 'Member', 0, NULL, 0, 'governance', 'Conference Only Members (no accommodation)', 1200000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(2, 'IIA-GF-2026', 'Member', 1, 'sunbird_nkopola', 0, 'governance', 'Nkopola Members', 2250000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(3, 'IIA-GF-2026', 'Member', 1, 'sunbird_nkopola', 1, 'governance', 'Nkopola Members with Spouse', 2700000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(4, 'IIA-GF-2026', 'Member', 1, 'sun_n_sand_holiday_resort', 0, 'governance', 'Sun N Sand Members', 2200000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(5, 'IIA-GF-2026', 'Member', 1, 'sun_n_sand_holiday_resort', 1, 'governance', 'Sun N Sand Members with Spouse', 2800000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(6, 'IIA-GF-2026', 'Non-Member', 0, NULL, 0, 'governance', 'Conference Only Non-Members (no accommodation)', 1350000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(7, 'IIA-GF-2026', 'Non-Member', 1, 'sunbird_nkopola', 0, 'governance', 'Nkopola Non-Members', 2500000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(8, 'IIA-GF-2026', 'Non-Member', 1, 'sunbird_nkopola', 1, 'governance', 'Nkopola Non-Members with Spouse', 3150000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(9, 'IIA-GF-2026', 'Non-Member', 1, 'sun_n_sand_holiday_resort', 0, 'governance', 'Sun N Sand Non-Members', 2350000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(10, 'IIA-GF-2026', 'Non-Member', 1, 'sun_n_sand_holiday_resort', 1, 'governance', 'Sun N Sand Non-Members with Spouse', 2950000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(11, 'IIA-AC-2026', 'Member', 0, NULL, 0, 'main', 'Conference Only Members (no accommodation)', 980000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(12, 'IIA-AC-2026', 'Member', 1, 'sunbird_nkopola', 0, 'main', 'Nkopola Members', 2200000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(13, 'IIA-AC-2026', 'Member', 1, 'sunbird_nkopola', 1, 'main', 'Nkopola Members with Spouse', 2800000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(14, 'IIA-AC-2026', 'Member', 1, 'sun_n_sand_holiday_resort', 0, 'main', 'Sun N Sand Members', 1755000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(15, 'IIA-AC-2026', 'Member', 1, 'sun_n_sand_holiday_resort', 1, 'main', 'Sun N Sand Members with Spouse', 2350000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(16, 'IIA-AC-2026', 'Non-Member', 0, NULL, 0, 'main', 'Conference Only Non-Members (no accommodation)', 1150000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(17, 'IIA-AC-2026', 'Non-Member', 1, 'sunbird_nkopola', 0, 'main', 'Nkopola Non-Members', 2500000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(18, 'IIA-AC-2026', 'Non-Member', 1, 'sunbird_nkopola', 1, 'main', 'Nkopola Non-Members with Spouse', 3100000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(19, 'IIA-AC-2026', 'Non-Member', 1, 'sun_n_sand_holiday_resort', 0, 'main', 'Sun N Sand Non-Members', 2150000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(20, 'IIA-AC-2026', 'Non-Member', 1, 'sun_n_sand_holiday_resort', 1, 'main', 'Sun N Sand Non-Members with Spouse', 2750000.00, 600000.00, '2026-05-24 09:21:42', '2026-05-24 09:21:42');

-- --------------------------------------------------------

--
-- Table structure for table `event_programme`
--

CREATE TABLE `event_programme` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(250) NOT NULL,
  `session_name` varchar(250) DEFAULT NULL,
  `session_description` text DEFAULT NULL,
  `session_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `presenter` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_sessions`
--

CREATE TABLE `event_sessions` (
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(250) NOT NULL,
  `session_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hotel`
--

CREATE TABLE `hotel` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(250) NOT NULL,
  `venue_type` enum('governance','main','both') DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `available_count` int(11) NOT NULL,
  `booked_count` int(11) NOT NULL,
  `gps_coordinates` varchar(255) DEFAULT NULL,
  `latitudes` varchar(250) DEFAULT NULL,
  `longitudes` varchar(250) DEFAULT NULL,
  `extra_price` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotel`
--

INSERT INTO `hotel` (`id`, `event_id`, `venue_type`, `name`, `quantity`, `available_count`, `booked_count`, `gps_coordinates`, `latitudes`, `longitudes`, `extra_price`, `created_at`, `updated_at`) VALUES
(1, 'IIA-GF-2026', 'governance', 'Sunbird Nkopola', 50, 0, 5, '-14.0500,35.1500', NULL, NULL, 0.00, '2026-05-24 09:21:42', '2026-05-25 04:19:11'),
(2, 'IIA-AC-2026', 'both', 'Sun N Sand Holiday Resort', 50, 49, 1, '-14.0600,35.1600', NULL, NULL, 0.00, '2026-05-24 09:21:42', '2026-05-25 09:40:34');

-- --------------------------------------------------------

--
-- Table structure for table `i_meal_coupons_print_queue`
--

CREATE TABLE `i_meal_coupons_print_queue` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_code` varchar(250) DEFAULT NULL,
  `unique_code` varchar(250) DEFAULT NULL,
  `status` varchar(250) DEFAULT NULL,
  `total_meals` int(11) DEFAULT NULL,
  `meals_redeemed` varchar(250) DEFAULT NULL,
  `qrcode_path` varchar(250) DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `day` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `i_participant_event_registrations`
--

CREATE TABLE `i_participant_event_registrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_code` varchar(250) DEFAULT NULL,
  `participant_id` varchar(250) DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `registration_date_time` timestamp NULL DEFAULT NULL,
  `conference_pack_redeemed` tinyint(1) NOT NULL DEFAULT 0,
  `conference_pack_redeem_date_time` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `i_participant_event_registrations_logs`
--

CREATE TABLE `i_participant_event_registrations_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_code` varchar(250) DEFAULT NULL,
  `participant_name` varchar(250) DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `registration_date_time` timestamp NULL DEFAULT NULL,
  `conference_pack_redeemed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `i_user_event`
--

CREATE TABLE `i_user_event` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `user_id` varchar(250) DEFAULT NULL,
  `status` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `i_user_otp`
--

CREATE TABLE `i_user_otp` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(250) DEFAULT NULL,
  `otp` varchar(250) DEFAULT NULL,
  `reference_code` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal_coupon`
--

CREATE TABLE `meal_coupon` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `participant_reference_code` varchar(255) DEFAULT NULL,
  `unique_code` varchar(255) DEFAULT NULL,
  `total_meals` int(11) NOT NULL DEFAULT 0,
  `qrcode_path` varchar(2048) DEFAULT NULL,
  `redeemed` varchar(250) DEFAULT NULL,
  `day` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meal_coupon`
--

INSERT INTO `meal_coupon` (`id`, `event_id`, `participant_reference_code`, `unique_code`, `total_meals`, `qrcode_path`, `redeemed`, `day`, `date`, `time`, `status`, `created_at`, `updated_at`) VALUES
(7, 'IIA-GF-2026', NULL, '-IIA-GF-2026', 5, 'qrcodes/-IIA-GF-2026_6a141c117a860.svg', NULL, NULL, NULL, NULL, 'main', '2026-05-25 09:53:21', '2026-05-25 09:53:21'),
(8, 'IIA-GF-2026', NULL, '-spouse', 5, 'qrcodes/-spouse_6a141c117fef5.svg', NULL, NULL, NULL, NULL, 'spouse', '2026-05-25 09:53:21', '2026-05-25 09:53:21'),
(9, 'IIA-AC-2026', NULL, '-IIA-AC-2026', 2, 'qrcodes/-IIA-AC-2026_6a141eabeff3d.svg', NULL, NULL, NULL, NULL, 'main', '2026-05-25 10:04:28', '2026-05-25 10:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `meal_scans_per_day`
--

CREATE TABLE `meal_scans_per_day` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `participant_reference_code` varchar(255) NOT NULL,
  `unique_code` varchar(255) NOT NULL,
  `day` varchar(255) NOT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `redeemed` tinyint(1) NOT NULL DEFAULT 0,
  `hotel_name` varchar(250) DEFAULT NULL,
  `created_by` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal_scans_per_day_logs`
--

CREATE TABLE `meal_scans_per_day_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `participant_reference_code` varchar(255) DEFAULT NULL,
  `unique_code` varchar(255) DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `redeemed` int(11) DEFAULT NULL,
  `hotel_name` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `member_id` varchar(50) DEFAULT NULL,
  `participant` varchar(255) DEFAULT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `password_set` tinyint(1) NOT NULL DEFAULT 0,
  `otp` varchar(255) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `datejoined` varchar(255) DEFAULT NULL,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `device_type` varchar(255) DEFAULT NULL,
  `reference_code` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `member_id`, `participant`, `email_address`, `phone_number`, `company_name`, `status`, `address`, `password`, `password_set`, `otp`, `otp_expires_at`, `datejoined`, `last_active_at`, `device_type`, `reference_code`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, '2VTCFVHUIO', 'Wongani Msumba', 'wongani087@gmail.com', '0882466662', 'iMoSyS', 'Non-Member', 'Co Willice Msumba, OPC, Private Bag 301', '$2y$10$RKhSUuTtbTNs1kTczhakoePQIBFn1xzThk2xWaX4Wh2N98GvkLqgy', 1, NULL, NULL, NULL, '2026-05-24 22:34:36', 'Other', '2VTCFVHUIO', NULL, NULL, NULL),
(2, 'IIA-001', 'Wongani Msumba', 'wonganimsumba0@gmail.com', '0991234567', 'Celeris', 'Member', NULL, '$2y$10$b6sOCQc36aVjaxBbSVHExu8P9W3dxkWmEFgGieNUBgRKOw9rPlmYm', 1, NULL, NULL, NULL, '2026-05-25 03:21:23', 'Other', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(2, '2025_03_25_160142_create_sessions_table', 1),
(3, '2026_05_24_105800_drop_unused_tables', 1),
(4, '2026_05_24_105700_create_base_tables', 2),
(5, '2026_05_24_105801_update_members_table', 3),
(6, '2026_05_24_105802_update_bookers_table', 3),
(7, '2026_05_24_105803_update_event_participants_table', 3),
(8, '2026_05_24_105804_update_events_table', 3),
(9, '2026_05_24_105805_update_event_prices_table', 3),
(10, '2026_05_24_105806_update_hotel_table', 3),
(11, '2026_05_24_105807_create_room_types_table', 3),
(12, '2026_05_24_105808_create_booking_invoices_table', 3),
(13, '2026_05_24_105809_add_foreign_keys', 3),
(14, '2026_05_24_105600_create_permission_tables', 4);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1);

-- --------------------------------------------------------

--
-- Table structure for table `participant_evaluation`
--

CREATE TABLE `participant_evaluation` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `questions` text DEFAULT NULL,
  `type` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant`
--

CREATE TABLE `restaurant` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_code` varchar(250) NOT NULL,
  `status` varchar(250) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_module`
--

CREATE TABLE `restaurant_module` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_code` varchar(250) NOT NULL,
  `total_meals` int(11) NOT NULL,
  `meal_coupons` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'web', '2026-05-24 09:21:41', '2026-05-24 09:21:41'),
(2, 'Secretariat', 'web', '2026-05-24 09:21:41', '2026-05-24 09:21:41'),
(3, 'Finance', 'web', '2026-05-24 09:21:41', '2026-05-24 09:21:41'),
(4, 'Event Manager', 'web', '2026-05-24 09:21:41', '2026-05-24 09:21:41');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_types`
--

CREATE TABLE `room_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `hotel_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `available_count` int(11) NOT NULL DEFAULT 0,
  `booked_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_types`
--

INSERT INTO `room_types` (`id`, `hotel_id`, `name`, `quantity`, `available_count`, `booked_count`, `created_at`, `updated_at`) VALUES
(1, 1, 'Standard Double', 50, 50, 0, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(2, 1, 'Deluxe Suite', 20, 20, 0, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(3, 2, 'Standard Double', 80, 80, 0, '2026-05-24 09:21:42', '2026-05-24 09:21:42'),
(4, 2, 'Deluxe Suite', 30, 30, 0, '2026-05-24 09:21:42', '2026-05-24 09:21:42');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('brygWeItVqAKMd68ejkNlX0Bi9CneWjmYeHN5uEb', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRUFicnRnUzhneFJ6eUhTU3lJMDdRZ2l2cUhhSk9DVEFBdlh0M3V4ViI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozODoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL21lbWJlci1kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozOToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3BhcnRpY2lwYW50LWxvZ2luIjt9fQ==', 1779655607),
('BuR06WNiBE8yK1xY1AvrPudozoCMwAt3w1EiYxd0', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiODhKaHprcWNSdW1DNXJaeWUzQW5wb2VhUWt0SFZqUlh0T0xDR0xjeiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9teS1ib29raW5nL0lJQS1HRi0yMDI2Ijt9czo1MzoibG9naW5fbWVtYmVyXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1779652386),
('jnWVW6k9FwgutMA8qnaTXkHX0LjVDZassUKOUuCu', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiclVQNVJEeWxwbkxiVXRSN0ZWUTdTNmpDSWdURHB4WTZBMDhoZzFaciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1779653048),
('oJw2clntvJUl4HlLyjhEOorODaQTXNC6gos7bOll', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiR2FPVG5yWUFjOVY2RTQydG5odEtxaEk0cEdPTW9XbmpNcUZ4TG5xZiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL215LWJvb2tpbmcvSUlBLUdGLTIwMjYiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozODoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL21lbWJlci1kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUzOiJsb2dpbl9tZW1iZXJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1779662079),
('SkNdQmpjqPQjCn8vxqI3bnYxmbyKcxE2V7ZcAumE', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoic01qZmpPWDlXU2JVYW9HY0l2YjV6bzV0TFBlajNJYTg5cWtFZXlCQyI7czozOiJ1cmwiO2E6MDp7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjU2OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkLXR3bz9ldmVudF9pZD1JSUEtQUMtMjAyNiI7fXM6NTM6ImxvZ2luX21lbWJlcl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjEyOiJ1c2VyX3Nlc3Npb24iO2E6Mjp7czo3OiJkZXRhaWxzIjthOjY6e3M6MjoiaWQiO2k6MTtzOjQ6Im5hbWUiO3M6MTM6IldvbmdhbmkgQWRtaW4iO3M6NToiZW1haWwiO3M6MjA6IndvbmdhbmkwODdAZ21haWwuY29tIjtzOjU6InBob25lIjtOO3M6NToicm9sZXMiO086Mjk6IklsbHVtaW5hdGVcU3VwcG9ydFxDb2xsZWN0aW9uIjoyOntzOjg6IgAqAGl0ZW1zIjthOjE6e2k6MDtzOjExOiJTdXBlciBBZG1pbiI7fXM6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDt9czoxMjoicHJpbWFyeV9yb2xlIjtzOjExOiJTdXBlciBBZG1pbiI7fXM6NzoiZmlsdGVycyI7YTowOnt9fXM6MjE6InBhc3N3b3JkX2hhc2hfc2FuY3R1bSI7czo2MDoiJDJ5JDEwJG92WWtvc3Vhai9uLnpWQkpoZEdvak92cDBIQ09YdjNsb1RYOFZiZy5RS05XLlV1NDFRTGpTIjt9', 1779703498),
('Tx9vcfvMGAyzGBC4KUynMuzAgc8OwNHmsmY0fvoX', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYnFhNzg0V1dLeTYzbDdlRGdycUFZWVZJb0ozTm5LRWQ5MU9USnFRSCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1779700184);

-- --------------------------------------------------------

--
-- Table structure for table `sponsor_ads`
--

CREATE TABLE `sponsor_ads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(250) DEFAULT NULL,
  `file_path` varchar(250) DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t&cs`
--

CREATE TABLE `t&cs` (
  `id` int(11) NOT NULL,
  `terms` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE `terms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `terms` text DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `terms`
--

INSERT INTO `terms` (`id`, `terms`, `event_id`, `created_at`, `updated_at`) VALUES
(1, 'Terms and Conditions', NULL, '2026-05-25 06:56:27', '2026-05-25 06:56:27'),
(2, 'Terms', NULL, '2026-05-25 06:56:37', '2026-05-25 06:56:37'),
(3, 'terms', NULL, '2026-05-25 06:59:04', '2026-05-25 06:59:04');

-- --------------------------------------------------------

--
-- Table structure for table `to_form`
--

CREATE TABLE `to_form` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_code` varchar(250) DEFAULT NULL,
  `event_id` varchar(250) DEFAULT NULL,
  `answers` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `gender` enum('female','male') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `current_team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `profile_photo_path` varchar(2048) DEFAULT NULL,
  `firebase_token` varchar(255) DEFAULT NULL,
  `unique_id` varchar(32) DEFAULT NULL,
  `total_web_logins` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `total_mobile_app_logins` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `gender`, `dob`, `phone`, `email`, `email_verified_at`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `current_team_id`, `profile_photo_path`, `firebase_token`, `unique_id`, `total_web_logins`, `total_mobile_app_logins`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Wongani Admin', NULL, NULL, NULL, 'wongani087@gmail.com', '2026-05-24 09:58:38', '$2y$10$ovYkosuaj/n.zVBJhdGojOvp0HCOXv3loTX8Vbg.QKNW.Uu41QLjS', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, 0, 'active', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_registration`
--
ALTER TABLE `attendance_registration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance_registration_logs`
--
ALTER TABLE `attendance_registration_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attire_colors`
--
ALTER TABLE `attire_colors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attire_sizes`
--
ALTER TABLE `attire_sizes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attire_types`
--
ALTER TABLE `attire_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `authorization_logs`
--
ALTER TABLE `authorization_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `authorize_event_participants`
--
ALTER TABLE `authorize_event_participants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `authorize_meal_coupon`
--
ALTER TABLE `authorize_meal_coupon`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookers`
--
ALTER TABLE `bookers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookers_room_type_id_foreign` (`room_type_id`);

--
-- Indexes for table `booking_forms`
--
ALTER TABLE `booking_forms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_invoices`
--
ALTER TABLE `booking_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `booking_invoices_booking_id_foreign` (`booking_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_participants_room_type_id_foreign` (`room_type_id`);

--
-- Indexes for table `event_prices`
--
ALTER TABLE `event_prices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_programme`
--
ALTER TABLE `event_programme`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_sessions`
--
ALTER TABLE `event_sessions`
  ADD PRIMARY KEY (`session_id`);

--
-- Indexes for table `hotel`
--
ALTER TABLE `hotel`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `i_meal_coupons_print_queue`
--
ALTER TABLE `i_meal_coupons_print_queue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `i_participant_event_registrations`
--
ALTER TABLE `i_participant_event_registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `i_participant_event_registrations_logs`
--
ALTER TABLE `i_participant_event_registrations_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `i_user_event`
--
ALTER TABLE `i_user_event`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `i_user_otp`
--
ALTER TABLE `i_user_otp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meal_coupon`
--
ALTER TABLE `meal_coupon`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meal_scans_per_day`
--
ALTER TABLE `meal_scans_per_day`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meal_scans_per_day_logs`
--
ALTER TABLE `meal_scans_per_day_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `members_member_id_unique` (`member_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `participant_evaluation`
--
ALTER TABLE `participant_evaluation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `restaurant`
--
ALTER TABLE `restaurant`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `restaurant_module`
--
ALTER TABLE `restaurant_module`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_types_hotel_id_foreign` (`hotel_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `sponsor_ads`
--
ALTER TABLE `sponsor_ads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t&cs`
--
ALTER TABLE `t&cs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `to_form`
--
ALTER TABLE `to_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_registration`
--
ALTER TABLE `attendance_registration`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_registration_logs`
--
ALTER TABLE `attendance_registration_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attire_colors`
--
ALTER TABLE `attire_colors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attire_sizes`
--
ALTER TABLE `attire_sizes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attire_types`
--
ALTER TABLE `attire_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `authorization_logs`
--
ALTER TABLE `authorization_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `authorize_event_participants`
--
ALTER TABLE `authorize_event_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `authorize_meal_coupon`
--
ALTER TABLE `authorize_meal_coupon`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookers`
--
ALTER TABLE `bookers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `booking_forms`
--
ALTER TABLE `booking_forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_invoices`
--
ALTER TABLE `booking_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `event_participants`
--
ALTER TABLE `event_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `event_prices`
--
ALTER TABLE `event_prices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `event_programme`
--
ALTER TABLE `event_programme`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_sessions`
--
ALTER TABLE `event_sessions`
  MODIFY `session_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hotel`
--
ALTER TABLE `hotel`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `i_meal_coupons_print_queue`
--
ALTER TABLE `i_meal_coupons_print_queue`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `i_participant_event_registrations`
--
ALTER TABLE `i_participant_event_registrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `i_participant_event_registrations_logs`
--
ALTER TABLE `i_participant_event_registrations_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `i_user_event`
--
ALTER TABLE `i_user_event`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `i_user_otp`
--
ALTER TABLE `i_user_otp`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meal_coupon`
--
ALTER TABLE `meal_coupon`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `meal_scans_per_day`
--
ALTER TABLE `meal_scans_per_day`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meal_scans_per_day_logs`
--
ALTER TABLE `meal_scans_per_day_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `participant_evaluation`
--
ALTER TABLE `participant_evaluation`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurant`
--
ALTER TABLE `restaurant`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurant_module`
--
ALTER TABLE `restaurant_module`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `room_types`
--
ALTER TABLE `room_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sponsor_ads`
--
ALTER TABLE `sponsor_ads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t&cs`
--
ALTER TABLE `t&cs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `to_form`
--
ALTER TABLE `to_form`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookers`
--
ALTER TABLE `bookers`
  ADD CONSTRAINT `bookers_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `booking_invoices`
--
ALTER TABLE `booking_invoices`
  ADD CONSTRAINT `booking_invoices_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_types`
--
ALTER TABLE `room_types`
  ADD CONSTRAINT `room_types_hotel_id_foreign` FOREIGN KEY (`hotel_id`) REFERENCES `hotel` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
