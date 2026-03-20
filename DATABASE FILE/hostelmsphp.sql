-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 19, 2026 at 03:00 PM
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
-- Database: `hostelmsphp`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(300) NOT NULL,
  `role` enum('super_admin','tenant_admin') DEFAULT 'tenant_admin',
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updation_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` int(11) NOT NULL,
  `amenity_name` varchar(100) NOT NULL,
  `icon_class` varchar(100) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `amenity_name`, `icon_class`, `display_order`) VALUES
(1, 'WiFi', 'fa-wifi', 1),
(2, 'Laundry', 'fa-tint', 2),
(3, 'Swimming Pool', 'fa-swimmer', 3),
(4, 'Parking', 'fa-parking', 4),
(5, '24/7 Security', 'fa-shield-alt', 5),
(6, 'CCTV Surveillance', 'fa-video', 6),
(7, 'Gym/Fitness Center', 'fa-dumbbell', 7),
(8, 'Kitchen Facilities', 'fa-utensils', 8),
(9, 'Study Room', 'fa-book', 9),
(10, 'Common Room/Lounge', 'fa-couch', 10),
(11, 'Air Conditioning', 'fa-snowflake', 11),
(12, 'Heating', 'fa-fire', 12),
(13, 'Hot Water', 'fa-hot-tub', 13),
(14, 'Furnished', 'fa-bed', 14),
(15, 'Cleaning Service', 'fa-broom', 15),
(16, 'Elevator', 'fa-elevator', 16),
(17, 'Backup Generator', 'fa-plug', 17),
(18, 'Internet Cafe', 'fa-desktop', 18),
(19, 'Games Room', 'fa-gamepad', 19),
(20, 'Garden/Outdoor Space', 'fa-tree', 20);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `hostel_id` int(11) DEFAULT NULL,
  `roomno` int(11) NOT NULL,
  `seater` int(11) NOT NULL,
  `feespm` int(11) NOT NULL,
  `stayfrom` date NOT NULL,
  `booking_status` varchar(50) DEFAULT 'pending',
  `duration` int(11) NOT NULL,
  `firstName` varchar(500) NOT NULL,
  `middleName` varchar(500) NOT NULL,
  `lastName` varchar(500) NOT NULL,
  `gender` varchar(250) NOT NULL,
  `contactno` varchar(50) NOT NULL,
  `emailid` varchar(500) NOT NULL,
  `egycontactno` varchar(50) NOT NULL,
  `guardianName` varchar(500) NOT NULL,
  `guardianRelation` varchar(500) NOT NULL,
  `guardianContactno` varchar(50) NOT NULL,
  `corresAddress` varchar(500) NOT NULL,
  `corresCIty` varchar(500) NOT NULL,
  `corresPincode` varchar(50) NOT NULL,
  `pmntAddress` varchar(500) NOT NULL,
  `pmntCity` varchar(500) NOT NULL,
  `pmntPincode` varchar(50) NOT NULL,
  `postingDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `updationDate` varchar(500) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `tenant_id`, `hostel_id`, `roomno`, `seater`, `feespm`, `stayfrom`, `booking_status`, `duration`, `firstName`, `middleName`, `lastName`, `gender`, `contactno`, `emailid`, `egycontactno`, `guardianName`, `guardianRelation`, `guardianContactno`, `corresAddress`, `corresCIty`, `corresPincode`, `pmntAddress`, `pmntCity`, `pmntPincode`, `postingDate`, `updationDate`) VALUES
(1, 2, 1, 10, 1, 2500, '2026-03-08', 'confirmed', 2, 'STARK', 'TONY', 'LOVER', 'Others', '0789654223', 'STARK@GMAIL.COM', '0789588498', 'gyr', 'fuu', 'rui', '100', 'kisii', 'p/c', '100', 'kisii', 'p/c', '2026-03-08 06:57:37', '');

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `medical_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_registration`
--

CREATE TABLE `client_registration` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `firstName` varchar(255) NOT NULL,
  `middleName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `contactNo` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `regDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `updationDate` varchar(45) NOT NULL,
  `passUdateDate` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daycare_bookings`
--

CREATE TABLE `daycare_bookings` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `child_id` int(11) DEFAULT NULL,
  `child_name` varchar(255) NOT NULL,
  `child_age` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `time_slot` varchar(50) DEFAULT NULL,
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `assigned_caretaker_id` int(11) DEFAULT NULL,
  `landlord_notes` text DEFAULT NULL,
  `status` enum('pending','approved','declined','checked_in','checked_out') DEFAULT 'pending',
  `payment_status` enum('pending','paid') DEFAULT 'pending',
  `amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostels`
--

CREATE TABLE `hostels` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(500) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  `featured_image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `hostels`
--

INSERT INTO `hostels` (`id`, `tenant_id`, `name`, `description`, `address`, `city`, `state`, `postal_code`, `phone`, `email`, `status`, `featured_image`, `created_at`, `updated_at`) VALUES
(1, 2, 'Holy scribes hostels', '', 'opposite rubis petrol station', 'lowlands', '', '', '', '', 'approved', NULL, '2026-01-30 09:45:32', '2026-01-30 09:49:40'),
(2, 8, 'grace apartments', '', 'opposite marine park', 'marine', '', '', '', '', 'approved', NULL, '2026-02-04 11:50:43', '2026-02-04 12:54:19'),
(3, 10, 'Sunrise Student Center', 'A modern facility located in the heart of Nirobi. Features high-speed WiFi, 24/7 security, and a rooftop lounge.', 'Near CBD', 'Nairobi', 'Nairobi', '00100', '0712345678', 'hostel@test.com', 'approved', NULL, '2026-02-17 11:47:58', '2026-02-17 11:47:58'),
(4, 10, 'Westlands Luxury Hostels', 'Premium accommodation for professionals and students. Walking distance to major malls and transport hubs.', 'Near CBD', 'Westlands', 'Nairobi', '00100', '0712345678', 'hostel@test.com', 'approved', NULL, '2026-02-17 11:49:30', '2026-02-17 11:49:30'),
(5, 10, 'Karen Peaceful Stay', 'Serene environment conducive for study and relaxation. Large gardens and spacious rooms.', 'Near CBD', 'Karen', 'Nairobi', '00100', '0712345678', 'hostel@test.com', 'approved', NULL, '2026-02-17 11:49:30', '2026-02-17 11:49:30');

-- --------------------------------------------------------

--
-- Table structure for table `hostel_amenities`
--

CREATE TABLE `hostel_amenities` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `hostel_amenities`
--

INSERT INTO `hostel_amenities` (`id`, `hostel_id`, `amenity_id`) VALUES
(12, 1, 1),
(13, 1, 2),
(14, 1, 4),
(15, 1, 8),
(16, 2, 1),
(17, 2, 4),
(18, 2, 5),
(19, 2, 6),
(20, 2, 13),
(21, 2, 14),
(22, 2, 20);

-- --------------------------------------------------------

--
-- Table structure for table `hostel_images`
--

CREATE TABLE `hostel_images` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `caption` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `hostel_images`
--

INSERT INTO `hostel_images` (`id`, `hostel_id`, `image_path`, `is_primary`, `caption`, `display_order`, `uploaded_at`) VALUES
(3, 1, 'uploads/hostels/1/hostel_img_69b7f403293b87.59405038.png', 0, NULL, 0, '2026-03-16 12:13:55'),
(4, 1, 'uploads/hostels/1/hostel_img_69b7f4032a66d0.21531939.png', 0, NULL, 1, '2026-03-16 12:13:55'),
(5, 1, 'uploads/hostels/1/hostel_img_69b7f4032acc33.89252306.png', 0, NULL, 2, '2026-03-16 12:13:55'),
(6, 1, 'uploads/hostels/1/hostel_img_69b7f4032b1de6.16645832.png', 0, NULL, 3, '2026-03-16 12:13:55'),
(7, 1, 'uploads/hostels/1/hostel_img_69b7f4032b6d57.12665108.png', 0, NULL, 4, '2026-03-16 12:13:55'),
(8, 2, 'uploads/hostels/2/hostel_img_69b85ca5318132.70834369.png', 0, NULL, 0, '2026-03-16 19:40:21'),
(9, 2, 'uploads/hostels/2/hostel_img_69b85ca5325377.34708145.png', 0, NULL, 1, '2026-03-16 19:40:21'),
(10, 2, 'uploads/hostels/2/hostel_img_69b85ca532ecf7.13052830.png', 0, NULL, 2, '2026-03-16 19:40:21');

-- --------------------------------------------------------

--
-- Table structure for table `hostel_services`
--

CREATE TABLE `hostel_services` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `service_key` varchar(50) NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 0,
  `max_capacity` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `price_per_day` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostel_services`
--

INSERT INTO `hostel_services` (`id`, `hostel_id`, `service_key`, `is_enabled`, `max_capacity`, `created_at`, `updated_at`, `price_per_day`) VALUES
(1, 2, 'daycare', 0, 10, '2026-03-17 12:09:31', '2026-03-18 16:22:49', 0.00),
(7, 1, 'daycare', 0, 10, '2026-03-18 16:23:20', '2026-03-19 13:15:45', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `hostel_types`
--

CREATE TABLE `hostel_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `hostel_types`
--

INSERT INTO `hostel_types` (`id`, `type_name`, `description`, `display_order`) VALUES
(1, 'Single Room', 'Private room for one person', 1),
(2, 'Bed-sitter', 'Studio apartment with bedroom and sitting area', 2),
(3, 'One Bedroom', 'Apartment with separate bedroom', 3),
(4, 'Two Bedroom', 'Apartment with two bedrooms', 4),
(5, 'Three Bedroom', 'Apartment with three bedrooms', 5),
(6, 'Studio', 'Open plan living space', 6),
(7, 'Shared Room', 'Room shared with other students', 7),
(8, 'Ensuite Room', 'Private room with attached bathroom', 8);

-- --------------------------------------------------------

--
-- Table structure for table `hostel_type_mapping`
--

CREATE TABLE `hostel_type_mapping` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `available_count` int(11) NOT NULL DEFAULT 0,
  `price_per_month` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `hostel_type_mapping`
--

INSERT INTO `hostel_type_mapping` (`id`, `hostel_id`, `type_id`, `available_count`, `price_per_month`) VALUES
(3, 1, 1, 12, 2500.00);

-- --------------------------------------------------------

--
-- Table structure for table `image_likes`
--

CREATE TABLE `image_likes` (
  `id` int(11) NOT NULL,
  `image_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `image_likes`
--

INSERT INTO `image_likes` (`id`, `image_id`, `session_id`, `ip_address`, `created_at`) VALUES
(16, 4, 'lo2ke23hhlr33ct0l9e44c7co6', '::1', '2026-03-16 12:15:21'),
(18, 3, '61hrkarnujg3k2oql730skb9h3', '::1', '2026-03-17 13:07:24');

-- --------------------------------------------------------

--
-- Table structure for table `landlord_payment_methods`
--

CREATE TABLE `landlord_payment_methods` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `method_type` varchar(50) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_number` varchar(100) NOT NULL,
  `additional_info` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `landlord_payment_methods`
--

INSERT INTO `landlord_payment_methods` (`id`, `tenant_id`, `method_type`, `account_name`, `account_number`, `additional_info`, `status`, `created_at`, `updated_at`) VALUES
(2, 2, 'MPESA_TILL', 'Mary hostels', '690933', '', 'active', '2026-02-03 09:42:01', '2026-02-03 09:42:01'),
(4, 8, 'MPESA_TILL', 'grace apartments', '234555', '', 'active', '2026-02-05 05:45:10', '2026-02-05 05:45:10');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_role` varchar(50) NOT NULL COMMENT 'student or admin',
  `receiver_id` int(11) DEFAULT NULL,
  `receiver_role` varchar(50) NOT NULL COMMENT 'student or admin',
  `tenant_id` int(11) DEFAULT NULL COMMENT 'NULL for super admin messages, tenant_id for landlord messages',
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `sender_role`, `receiver_id`, `receiver_role`, `tenant_id`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 20, 'client', NULL, 'admin', 2, 'payment', 'please confirm that you have recieved payment', 1, '2026-02-03 11:31:26'),
(2, 19, 'admin', 20, 'client', 2, 'Reply', 'yeah payment has been confirmed \r\nthank you\r\n', 1, '2026-02-03 11:32:18'),
(3, 20, 'client', 19, 'admin', 2, 'Conversation', 'hello sir?', 1, '2026-02-03 12:05:35'),
(4, 19, 'admin', 20, 'client', 2, 'Reply', 'yees\r\n', 1, '2026-02-03 12:06:50'),
(5, 19, 'admin', 20, 'client', 2, 'Reply', 'yes baby\r\n', 0, '2026-02-03 12:10:25'),
(6, 22, 'landlord', 23, '0', 8, 'bookings success', 'welcome to grace appartments', 0, '2026-02-17 10:53:31'),
(7, 22, 'landlord', 23, '0', 8, 'Reply', 'hi?\r\n', 0, '2026-02-17 11:13:31'),
(8, 23, 'client', 22, 'admin', 8, 'Conversation', 'ssup sir?', 1, '2026-02-17 11:16:16'),
(9, 22, 'landlord', 23, '0', 8, 'Reply', 'how are you doing?\r\n', 0, '2026-02-17 11:16:44'),
(10, 23, 'client', 22, 'admin', 8, 'Conversation', 'j', 1, '2026-02-17 11:26:48'),
(11, 23, 'client', NULL, 'admin', NULL, 'bookings success', 'nmn', 0, '2026-03-08 07:09:27'),
(12, 26, 'caretaker', 19, 'landlord', 2, 'dfghjk', 'rdtfgjhkl;', 0, '2026-03-16 17:50:49'),
(13, 26, 'caretaker', 23, 'client', 2, 'cockroach issue', 'the fumigation team will be at your place tommorrow please make yourself available when you will be contacted', 0, '2026-03-17 10:38:02');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `sender_id`, `receiver_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, NULL, 20, 'Booking Approved', 'Your booking for room 12 has been confirmed.', 1, '2026-02-03 10:35:59'),
(2, 20, 19, 'New Landlord Message', 'New message from client (harry@gmail.com)', 1, '2026-02-03 12:05:35'),
(3, 19, 20, 'New Message from Landlord', 'You have a new message from Nancy Vasquez', 1, '2026-02-03 12:06:50'),
(4, 19, 20, 'New Message from Landlord', 'You have a new message from Nancy Vasquez', 1, '2026-02-03 12:10:25'),
(5, 23, 22, 'New Booking Request', 'Client STARK LOVER has requested room 201.', 1, '2026-02-05 05:43:29'),
(6, 22, 23, 'New Message from Landlord', 'You have a new message from grace thomas', 1, '2026-02-17 10:53:31'),
(7, 22, 23, 'New Message from Landlord', 'You have a new message from grace thomas', 1, '2026-02-17 11:13:31'),
(8, 23, 22, 'New Landlord Message', 'New message from client (STARK@GMAIL.COM)', 1, '2026-02-17 11:16:16'),
(9, 22, 23, 'New Message from Landlord', 'You have a new message from grace thomas', 1, '2026-02-17 11:16:44'),
(10, 23, 22, 'New Landlord Message', 'New message from client (STARK@GMAIL.COM)', 1, '2026-02-17 11:26:48'),
(11, 21, 24, 'sdfghjkl;', 'dtfgyuhjkl', 0, '2026-02-20 09:23:34'),
(12, 21, 19, 'sdfghjkl;', 'dtfgyuhjkl', 1, '2026-02-20 09:23:34'),
(13, 21, 23, 'sdfghjkl;', 'dtfgyuhjkl', 1, '2026-02-20 09:23:34'),
(14, 21, 22, 'sdfghjkl;', 'dtfgyuhjkl', 1, '2026-02-20 09:23:34'),
(15, 23, 19, 'New Booking Request', 'Client STARK LOVER has requested room 1.', 1, '2026-02-20 10:36:28'),
(16, NULL, 23, 'Booking Approved', 'Your booking for room 1 has been confirmed.', 1, '2026-02-20 10:39:07'),
(17, 23, 19, 'New Booking Request', 'Client STARK LOVER has requested room 10.', 1, '2026-03-08 06:57:37'),
(18, 26, 19, 'New Message from Caretaker', 'You have a new message from red  mummy', 1, '2026-03-16 17:50:49'),
(19, 23, 22, 'New Maintenance Request', 'A new Emergency priority Pest Control request has been submitted for Room 10.', 1, '2026-03-17 10:31:15'),
(20, 26, 23, 'New Message from Caretaker', 'You have a new message from red  mummy', 1, '2026-03-17 10:38:02');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL COMMENT 'Reference to bookings table',
  `client_id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL COMMENT 'For tenant isolation',
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `payment_method` varchar(100) NOT NULL COMMENT 'M-Pesa, Bank Transfer, Cash',
  `proof_file` varchar(255) DEFAULT NULL COMMENT 'Path to uploaded proof file',
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `client_id`, `tenant_id`, `amount`, `transaction_id`, `payment_method`, `proof_file`, `status`, `created_at`, `updated_at`) VALUES
(4, 1, 23, 2, 5000.00, 'MK4HFKI45J', 'MPESA_TILL (690933)', 'pay_1772953221_23.png', 'verified', '2026-03-08 07:00:21', '2026-03-19 11:06:58');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `hostel_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, 1, 5, 'A bit expensive but worth it for the location.', '2026-02-17 11:49:30'),
(2, 3, 1, 5, 'A bit expensive but worth it for the location.', '2026-02-17 11:49:30'),
(3, 3, 1, 4, 'Security is top notch. I felt very safe.', '2026-02-17 11:49:30'),
(4, 3, 1, 5, 'Great place to stay! Very clean.', '2026-02-17 11:49:30'),
(5, 4, 1, 5, 'Security is top notch. I felt very safe.', '2026-02-17 11:49:30'),
(6, 4, 1, 5, 'Wifi is fast, but the food is average.', '2026-02-17 11:49:30'),
(7, 4, 1, 3, 'Security is top notch. I felt very safe.', '2026-02-17 11:49:30'),
(8, 5, 1, 5, 'Security is top notch. I felt very safe.', '2026-02-17 11:49:30'),
(9, 5, 1, 3, 'Great place to stay! Very clean.', '2026-02-17 11:49:30'),
(10, 5, 1, 4, 'Security is top notch. I felt very safe.', '2026-02-17 11:49:30'),
(11, 5, 1, 5, 'A bit expensive but worth it for the location.', '2026-02-17 11:49:30'),
(12, 2, 23, 5, 'great place', '2026-02-17 11:55:00'),
(13, 2, 23, 5, 'FINE ', '2026-02-21 09:22:41'),
(14, 1, 23, 5, 'NM', '2026-02-21 09:23:07'),
(15, 1, 23, 5, 'cvbnm,', '2026-03-17 10:53:50');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `hostel_id` int(11) DEFAULT NULL,
  `seater` int(11) NOT NULL,
  `room_type_id` int(11) DEFAULT NULL,
  `room_no` int(11) NOT NULL,
  `fees` int(11) NOT NULL,
  `status` enum('available','booked') NOT NULL DEFAULT 'available',
  `posting_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `tenant_id`, `hostel_id`, `seater`, `room_type_id`, `room_no`, `fees`, `status`, `posting_date`) VALUES
(1, 1, NULL, 5, NULL, 100, 1990, 'available', '2020-09-20 04:24:06'),
(2, 1, NULL, 4, NULL, 201, 1650, 'available', '2020-09-20 04:24:06'),
(3, 1, NULL, 2, NULL, 200, 910, 'available', '2020-09-20 04:33:06'),
(4, 1, NULL, 3, NULL, 112, 1300, 'available', '2020-09-20 04:33:30'),
(5, 1, NULL, 5, NULL, 132, 1990, 'available', '2020-09-20 04:28:52'),
(6, 1, NULL, 4, NULL, 11, 1650, 'available', '2021-03-07 05:01:02'),
(7, 1, NULL, 2, NULL, 269, 910, 'available', '2022-04-03 14:39:22'),
(8, 1, NULL, 1, NULL, 310, 750, 'available', '2022-04-03 14:41:36'),
(9, 1, NULL, 1, NULL, 330, 750, 'available', '2022-04-03 14:41:53'),
(22, 2, 1, 1, 1, 1, 2500, 'booked', '2026-01-31 16:35:02'),
(23, 2, 1, 1, 1, 2, 2500, 'booked', '2026-01-31 16:35:02'),
(24, 2, 1, 1, 1, 3, 2500, 'booked', '2026-01-31 16:35:02'),
(25, 2, 1, 1, 1, 4, 2500, 'available', '2026-01-31 16:35:02'),
(26, 2, 1, 1, 1, 5, 2500, 'available', '2026-01-31 16:35:02'),
(27, 2, 1, 1, 1, 6, 2500, 'booked', '2026-01-31 16:35:02'),
(28, 2, 1, 1, 1, 7, 2500, 'available', '2026-01-31 16:35:02'),
(29, 2, 1, 1, 1, 8, 2500, 'available', '2026-01-31 16:35:02'),
(30, 2, 1, 1, 1, 9, 2500, 'available', '2026-01-31 16:35:02'),
(31, 2, 1, 1, 1, 10, 2500, 'booked', '2026-01-31 16:35:02'),
(32, 2, 1, 1, 1, 11, 2500, 'booked', '2026-01-31 16:35:02'),
(33, 2, 1, 1, 1, 12, 2500, 'available', '2026-01-31 16:35:02'),
(34, 8, 2, 1, 3, 201, 10000, 'booked', '2026-02-04 12:52:48'),
(35, 8, 2, 1, 3, 203, 10000, 'booked', '2026-02-04 12:52:48'),
(36, 8, 2, 1, 3, 204, 10000, 'booked', '2026-02-04 12:52:48'),
(37, 10, 4, 2, NULL, 101, 22500, 'available', '2026-02-17 11:49:30'),
(38, 10, 4, 2, NULL, 102, 23000, 'available', '2026-02-17 11:49:30'),
(39, 10, 4, 2, NULL, 103, 23500, 'available', '2026-02-17 11:49:30'),
(40, 10, 4, 2, NULL, 104, 24000, 'available', '2026-02-17 11:49:30'),
(41, 10, 4, 2, NULL, 105, 24500, 'available', '2026-02-17 11:49:30'),
(42, 10, 5, 2, NULL, 101, 18500, 'available', '2026-02-17 11:49:30'),
(43, 10, 5, 2, NULL, 102, 19000, 'available', '2026-02-17 11:49:30'),
(44, 10, 5, 2, NULL, 103, 19500, 'available', '2026-02-17 11:49:30'),
(45, 10, 5, 2, NULL, 104, 20000, 'available', '2026-02-17 11:49:30'),
(46, 10, 5, 2, NULL, 105, 20500, 'available', '2026-02-17 11:49:30');

-- --------------------------------------------------------

--
-- Table structure for table `system_maintenance_requests`
--

CREATE TABLE `system_maintenance_requests` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `room_no` int(11) DEFAULT NULL,
  `category` enum('Plumbing','Electrical','Carpentry','Painting','Appliance','Pest Control','General','Other') NOT NULL DEFAULT 'General',
  `priority` enum('Low','Medium','High','Emergency') NOT NULL DEFAULT 'Medium',
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `photo_path` varchar(500) DEFAULT NULL,
  `status` enum('Open','In Progress','Resolved','Closed','Cancelled') NOT NULL DEFAULT 'Open',
  `assigned_vendor_id` int(11) DEFAULT NULL,
  `landlord_notes` text DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_maintenance_requests`
--

INSERT INTO `system_maintenance_requests` (`id`, `tenant_id`, `client_id`, `hostel_id`, `room_no`, `category`, `priority`, `subject`, `description`, `photo_path`, `status`, `assigned_vendor_id`, `landlord_notes`, `resolution_notes`, `resolved_at`, `created_at`, `updated_at`) VALUES
(1, 8, 23, 1, 10, 'Pest Control', 'Emergency', 'cockroach issue', 'buana kuna cockroaches in my kitchen area, please sort this issue out', 'uploads/maintenance/maint_1773743475_23.png', 'Open', 4, NULL, NULL, NULL, '2026-03-17 10:31:15', '2026-03-17 10:31:15');

-- --------------------------------------------------------

--
-- Table structure for table `system_vendors`
--

CREATE TABLE `system_vendors` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `specialty` varchar(100) NOT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `jobs_completed` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_vendors`
--

INSERT INTO `system_vendors` (`id`, `tenant_id`, `name`, `phone`, `email`, `specialty`, `rating`, `jobs_completed`, `status`, `created_at`) VALUES
(1, NULL, 'Mwangi Plumbing Services', '0722111222', 'mwangi@plumbers.co.ke', 'Plumbing', 4.5, 32, 'active', '2026-03-04 14:59:49'),
(2, NULL, 'Kamau Electrical Works', '0733222333', 'kamau@electricals.co.ke', 'Electrical', 4.2, 18, 'active', '2026-03-04 14:59:49'),
(3, NULL, 'Njoroge General Repairs', '0744333444', 'njoroge@repairs.co.ke', 'General', 4.0, 45, 'active', '2026-03-04 14:59:49'),
(4, NULL, 'Wanjiku Pest Control', '0755444555', 'wanjiku@pest.co.ke', 'Pest Control', 4.8, 22, 'active', '2026-03-04 14:59:49');

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `status` enum('active','suspended','pending') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `name`, `code`, `admin_email`, `status`, `created_at`) VALUES
(1, 'Default Hostel', 'DEFAULT', 'admin@mail.com', 'active', '2026-01-29 11:24:38'),
(2, 'NANCY APARTMENTS', '', '', '', '2026-01-29 13:06:17'),
(8, 'Grace apartments', 'HOSTEL ', '', '', '2026-02-04 11:05:20'),
(10, 'Demo Landlord', '26ED83AF', 'landlord@demo.com', 'active', '2026-02-17 11:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `userlog`
--

CREATE TABLE `userlog` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `userEmail` varchar(255) NOT NULL,
  `userIp` varbinary(16) NOT NULL,
  `city` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `loginTime` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `userlog`
--

INSERT INTO `userlog` (`id`, `tenant_id`, `userId`, `userEmail`, `userIp`, `city`, `country`, `loginTime`) VALUES
(9, 1, 10, 'terry@mail.com', 0x3a3a31, '', '', '2021-03-05 04:12:00'),
(10, 1, 10, 'terry@mail.com', 0x3a3a31, '', '', '2021-03-05 04:14:44'),
(11, 1, 21, 'ross@mail.com', 0x3a3a31, '', '', '2021-03-05 04:19:52'),
(12, 1, 21, 'ross@mail.com', 0x3a3a31, '', '', '2021-03-05 08:53:33'),
(13, 1, 21, 'ross@mail.com', 0x3a3a31, '', '', '2021-03-05 17:35:34'),
(14, 1, 21, 'ross@mail.com', 0x3a3a31, '', '', '2021-03-06 02:43:01'),
(15, 1, 21, 'ross@mail.com', 0x3a3a31, '', '', '2021-03-06 15:18:49'),
(16, 1, 21, 'ross@mail.com', 0x3a3a31, '', '', '2021-03-07 09:35:23'),
(17, 1, 21, 'ross@mail.com', 0x3a3a31, '', '', '2021-03-07 09:59:55'),
(18, 1, 22, 'colin@gmail.com', 0x3a3a31, '', '', '2021-06-16 14:51:24'),
(19, 1, 22, 'colin@gmail.com', 0x3a3a31, '', '', '2021-12-12 15:31:50'),
(20, 1, 22, 'colin@gmail.com', 0x3a3a31, '', '', '2022-04-02 16:01:31'),
(21, 1, 21, 'ross@mail.com', 0x3a3a31, '', '', '2022-04-02 16:52:47'),
(22, 1, 20, 'richards@mail.com', 0x3a3a31, '', '', '2022-04-03 13:15:00'),
(23, 1, 24, 'jennifer@mail.com', 0x3a3a31, '', '', '2022-04-03 14:32:09'),
(24, 1, 24, 'jennifer@mail.com', 0x3a3a31, '', '', '2022-04-03 14:34:17'),
(25, 1, 19, 'bruce@mail.com', 0x3a3a31, '', '', '2022-04-03 14:44:31'),
(26, 1, 27, 'nancy@mail.com', 0x3a3a31, '', '', '2022-04-03 15:00:46'),
(27, 1, 32, 'liamoore@mail.com', 0x3a3a31, '', '', '2022-04-03 15:48:35'),
(28, 1, 32, 'liamoore@mail.com', 0x3a3a31, '', '', '2022-04-03 15:51:34'),
(29, NULL, 2, 'admin@mail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-01-29 11:29:14'),
(30, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-05 10:21:44'),
(31, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-05 10:28:52'),
(33, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-05 10:32:55'),
(34, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-08 07:10:53'),
(35, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-08 07:12:28'),
(36, NULL, 19, 'nancy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-08 07:20:29'),
(40, NULL, 19, 'nancy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 11:47:38'),
(41, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 11:48:01'),
(42, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 11:56:56'),
(43, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 12:02:21'),
(44, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 12:03:35'),
(45, NULL, 19, 'nancy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 12:12:16'),
(46, NULL, 25, 'taibrown245@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 13:18:21'),
(47, NULL, 25, 'taibrown245@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 13:37:55'),
(48, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 13:38:39'),
(49, NULL, 19, 'nancy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 13:38:56'),
(50, NULL, 26, 'taibrown245@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 13:58:14'),
(51, NULL, 26, 'taibrown245@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 14:40:14'),
(52, NULL, 26, 'taibrown245@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 19:28:41'),
(53, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 19:29:06'),
(54, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 20:25:15'),
(55, NULL, 26, 'admin@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 20:34:58'),
(56, NULL, 26, 'admin@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 20:41:06'),
(57, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 20:42:55'),
(58, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 20:43:16'),
(59, NULL, 26, 'caretaker@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 20:46:48'),
(60, NULL, 23, 'STARK@GMAIL.COM', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 20:47:30'),
(61, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 20:53:40'),
(62, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 20:56:36'),
(63, NULL, 23, 'STARK@GMAIL.COM', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 20:57:48'),
(64, NULL, 26, 'caretaker@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 21:00:37'),
(65, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-16 21:52:15'),
(66, NULL, 23, 'STARK@GMAIL.COM', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-17 10:28:32'),
(67, NULL, 26, 'caretaker@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-17 10:31:47'),
(68, NULL, 26, 'caretaker@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-17 10:35:33'),
(69, NULL, 26, 'caretaker@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-17 10:51:58'),
(70, NULL, 23, 'STARK@GMAIL.COM', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-17 10:53:01'),
(71, NULL, 23, 'STARK@GMAIL.COM', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-17 12:08:43'),
(72, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-17 12:09:04'),
(73, NULL, 23, 'STARK@GMAIL.COM', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-17 13:08:00'),
(74, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-18 15:58:38'),
(75, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-18 16:03:04'),
(76, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-18 16:03:29'),
(77, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-18 16:10:25'),
(78, NULL, 26, 'caretaker@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-18 16:14:44'),
(79, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-18 16:17:03'),
(80, NULL, 19, 'nancy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-18 16:23:08'),
(81, NULL, 26, 'caretaker@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-18 16:23:47'),
(82, NULL, 23, 'STARK@GMAIL.COM', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-18 16:24:07'),
(83, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-19 11:05:50'),
(84, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-19 11:20:31'),
(85, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-19 11:24:46'),
(86, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-19 11:37:01'),
(87, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-19 11:41:34'),
(88, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-19 12:09:04'),
(89, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-19 12:16:03'),
(90, NULL, 22, 'thomas@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-19 12:19:48'),
(91, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-19 13:11:20'),
(92, NULL, 19, 'nancy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-19 13:14:23'),
(93, NULL, 21, 'tracy@gmail.com', 0x3a3a31, 'Unknown', 'Unknown', '2026-03-19 13:20:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','landlord','client','caretaker') NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `id_no` varchar(50) DEFAULT NULL,
  `status` enum('active','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `original_id` int(11) DEFAULT NULL,
  `original_table` varchar(50) DEFAULT NULL,
  `assigned_hostel_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `tenant_id`, `email`, `password`, `role`, `full_name`, `first_name`, `middle_name`, `last_name`, `gender`, `contact_no`, `profile_pic`, `id_no`, `status`, `created_at`, `original_id`, `original_table`, `assigned_hostel_id`) VALUES
(11, 1, 'nancy@mail.com', 'e10adc3949ba59abbe56e057f20f883e', 'client', 'Nancy Vasquez', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-01-29 11:24:57', 27, 'userregistration', NULL),
(19, 2, 'nancy@gmail.com', '912a705ab8ca568793facff765903fbe', 'landlord', 'Nancy Vasquez', 'Nancy', 'hsh', 'vasquez', 'Female', '0788748843', NULL, '5333564', 'active', '2026-01-29 13:06:17', NULL, NULL, NULL),
(21, 2, 'tracy@gmail.com', '912a705ab8ca568793facff765903fbe', 'admin', 'tracy mia', 'tracy', 'doe', 'mia', 'Female', '0791281244', 'user_21_1771409805.jpg', '4889289', 'active', '2026-02-04 10:14:31', NULL, 'landlord_registration', NULL),
(22, 8, 'thomas@gmail.com', '912a705ab8ca568793facff765903fbe', 'landlord', 'grace thomas', NULL, NULL, NULL, NULL, NULL, 'user_22_1771499350.jpeg', NULL, 'active', '2026-02-04 11:05:20', NULL, NULL, NULL),
(23, 8, 'STARK@GMAIL.COM', '912a705ab8ca568793facff765903fbe', 'client', 'STARK TONY LOVER', 'STARK', 'TONY', 'LOVER', 'Others', '0789654223', 'user_23_1771330063.jpg', '3424563', 'active', '2026-02-05 05:34:36', NULL, 'landlord_registration', NULL),
(26, 2, 'caretaker@gmail.com', '0192023a7bbd73250516f069df18b500', 'caretaker', 'red  mummy', NULL, NULL, NULL, NULL, NULL, 'user_26_1773685553.png', NULL, 'active', '2026-03-16 13:57:56', NULL, 'caretaker_registration', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_activity_logs`
--

INSERT INTO `user_activity_logs` (`id`, `user_id`, `user_email`, `role`, `action`, `details`, `ip_address`, `created_at`) VALUES
(7, 23, 'STARK@GMAIL.COM', 'Client', 'Login', 'User logged in successfully', '::1', '2026-03-05 10:30:53'),
(9, 23, 'STARK@GMAIL.COM', 'Client', 'Book Room', 'Client booked room 10 (Booking ID: 1)', '::1', '2026-03-08 06:57:37'),
(15, 19, 'nancy@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-08 07:20:30'),
(16, 23, 'STARK@GMAIL.COM', 'Client', 'Login', 'User logged in successfully', '::1', '2026-03-08 08:05:04'),
(17, 23, 'STARK@GMAIL.COM', 'Client', 'Logout', 'Client logged out', '::1', '2026-03-08 08:05:24'),
(18, 19, 'nancy@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-08 09:08:17'),
(19, 23, 'STARK@GMAIL.COM', 'Client', 'Login', 'User logged in successfully', '::1', '2026-03-14 13:45:42'),
(20, 23, 'STARK@GMAIL.COM', 'Client', 'Logout', 'Client logged out', '::1', '2026-03-14 13:49:47'),
(21, 23, 'STARK@GMAIL.COM', 'Client', 'Login', 'User logged in successfully', '::1', '2026-03-16 11:44:47'),
(22, 23, 'STARK@GMAIL.COM', 'Client', 'Logout', 'Client logged out', '::1', '2026-03-16 11:47:24'),
(23, 19, 'nancy@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-16 11:47:38'),
(31, 19, 'nancy@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-16 12:12:16'),
(32, 19, 'nancy@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-16 13:18:14'),
(33, 25, 'taibrown245@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-16 13:18:21'),
(34, 25, 'taibrown245@gmail.com', 'Caretaker', 'Logout', 'User logged out', '::1', '2026-03-16 13:26:04'),
(35, 25, 'taibrown245@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-16 13:37:55'),
(36, 25, 'taibrown245@gmail.com', 'Caretaker', 'Logout', 'User logged out', '::1', '2026-03-16 13:38:23'),
(39, 19, 'nancy@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-16 13:38:56'),
(40, 19, 'nancy@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-16 13:58:08'),
(41, 26, 'taibrown245@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-16 13:58:14'),
(42, 26, 'taibrown245@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-16 14:40:14'),
(43, 26, 'taibrown245@gmail.com', 'Caretaker', 'Logout', 'User logged out', '::1', '2026-03-16 18:26:39'),
(44, 26, 'taibrown245@gmail.com', 'Caretaker', 'Logout', 'User logged out', '::1', '2026-03-16 19:27:31'),
(45, 26, 'taibrown245@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-16 19:28:41'),
(46, 26, 'taibrown245@gmail.com', 'Caretaker', 'Logout', 'User logged out', '::1', '2026-03-16 19:28:53'),
(48, 22, 'thomas@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-16 20:25:05'),
(51, 26, 'admin@gmail.com', 'Admin', 'Login', 'User logged in successfully', '::1', '2026-03-16 20:34:58'),
(52, 26, 'admin@gmail.com', 'Admin', 'Logout', 'User logged out', '::1', '2026-03-16 20:35:14'),
(53, 26, 'admin@gmail.com', 'Admin', 'Login', 'User logged in successfully', '::1', '2026-03-16 20:41:06'),
(54, 26, 'admin@gmail.com', 'Admin', 'Logout', 'User logged out', '::1', '2026-03-16 20:42:44'),
(59, 26, 'caretaker@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-16 20:46:48'),
(60, 26, 'caretaker@gmail.com', 'Caretaker', 'Logout', 'User logged out', '::1', '2026-03-16 20:47:18'),
(61, 23, 'STARK@GMAIL.COM', 'Client', 'Login', 'User logged in successfully', '::1', '2026-03-16 20:47:30'),
(64, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-16 20:56:36'),
(65, 22, 'thomas@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-16 20:57:11'),
(66, 23, 'STARK@GMAIL.COM', 'Client', 'Login', 'User logged in successfully', '::1', '2026-03-16 20:57:48'),
(67, 23, 'STARK@GMAIL.COM', 'Client', 'Logout', 'Client logged out', '::1', '2026-03-16 20:58:40'),
(68, 26, 'caretaker@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-16 21:00:37'),
(69, 26, 'caretaker@gmail.com', 'Caretaker', 'Logout', 'User logged out', '::1', '2026-03-16 21:01:37'),
(70, 23, 'STARK@GMAIL.COM', 'Client', 'Logout', 'Client logged out', '::1', '2026-03-16 21:30:10'),
(73, 23, 'STARK@GMAIL.COM', 'Client', 'Login', 'User logged in successfully', '::1', '2026-03-17 10:28:32'),
(74, 23, 'STARK@GMAIL.COM', 'Client', 'Logout', 'Client logged out', '::1', '2026-03-17 10:31:38'),
(75, 26, 'caretaker@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-17 10:31:47'),
(76, 26, 'caretaker@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-17 10:35:33'),
(77, 26, 'caretaker@gmail.com', 'Caretaker', 'Logout', 'Client logged out', '::1', '2026-03-17 10:51:53'),
(78, 26, 'caretaker@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-17 10:51:58'),
(79, 26, 'caretaker@gmail.com', 'Caretaker', 'Logout', 'User logged out', '::1', '2026-03-17 10:52:54'),
(80, 23, 'STARK@GMAIL.COM', 'Client', 'Login', 'User logged in successfully', '::1', '2026-03-17 10:53:01'),
(81, 23, 'STARK@GMAIL.COM', 'Client', 'Login', 'User logged in successfully', '::1', '2026-03-17 12:08:43'),
(82, 23, 'STARK@GMAIL.COM', 'Client', 'Logout', 'Client logged out', '::1', '2026-03-17 12:08:53'),
(83, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-17 12:09:04'),
(84, 23, 'STARK@GMAIL.COM', 'Client', 'Login', 'User logged in successfully', '::1', '2026-03-17 13:08:00'),
(85, 22, 'thomas@gmail.com', 'Landlord', 'Logout', 'Client logged out', '::1', '2026-03-18 15:58:15'),
(86, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-18 15:58:38'),
(87, 22, 'thomas@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-18 16:02:55'),
(88, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-18 16:03:04'),
(89, 22, 'thomas@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-18 16:03:25'),
(90, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-18 16:03:29'),
(91, 22, 'thomas@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-18 16:10:21'),
(92, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-18 16:10:25'),
(93, 22, 'thomas@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-18 16:11:28'),
(94, 26, 'caretaker@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-18 16:14:44'),
(95, 26, 'caretaker@gmail.com', 'Caretaker', 'Logout', 'User logged out', '::1', '2026-03-18 16:16:53'),
(96, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-18 16:17:03'),
(97, 22, 'thomas@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-18 16:23:02'),
(98, 19, 'nancy@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-18 16:23:08'),
(99, 19, 'nancy@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-18 16:23:37'),
(100, 26, 'caretaker@gmail.com', 'Caretaker', 'Login', 'User logged in successfully', '::1', '2026-03-18 16:23:47'),
(101, 26, 'caretaker@gmail.com', 'Caretaker', 'Logout', 'User logged out', '::1', '2026-03-18 16:23:59'),
(102, 23, 'STARK@GMAIL.COM', 'Client', 'Login', 'User logged in successfully', '::1', '2026-03-18 16:24:07'),
(103, 23, 'STARK@GMAIL.COM', 'Client', 'Logout', 'Client logged out', '::1', '2026-03-19 10:46:53'),
(106, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-19 11:20:31'),
(107, 22, 'thomas@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-19 11:24:33'),
(109, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-19 11:37:01'),
(110, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-19 11:41:34'),
(112, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-19 12:09:04'),
(113, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-19 12:16:03'),
(114, 22, 'thomas@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-19 12:19:52'),
(117, 19, 'nancy@gmail.com', 'Landlord', 'Login', 'User logged in successfully', '::1', '2026-03-19 13:14:23'),
(118, 19, 'nancy@gmail.com', 'Landlord', 'Logout', 'User logged out', '::1', '2026-03-19 13:20:04');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `booking_status` (`booking_status`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `client_registration`
--
ALTER TABLE `client_registration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daycare_bookings`
--
ALTER TABLE `daycare_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `child_id` (`child_id`);

--
-- Indexes for table `hostels`
--
ALTER TABLE `hostels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `hostel_amenities`
--
ALTER TABLE `hostel_amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `amenity_id` (`amenity_id`);

--
-- Indexes for table `hostel_images`
--
ALTER TABLE `hostel_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `is_primary` (`is_primary`);

--
-- Indexes for table `hostel_services`
--
ALTER TABLE `hostel_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hostel_service` (`hostel_id`,`service_key`);

--
-- Indexes for table `hostel_types`
--
ALTER TABLE `hostel_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hostel_type_mapping`
--
ALTER TABLE `hostel_type_mapping`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `image_likes`
--
ALTER TABLE `image_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`image_id`,`session_id`),
  ADD KEY `image_id` (`image_id`);

--
-- Indexes for table `landlord_payment_methods`
--
ALTER TABLE `landlord_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `token` (`token`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `room_type_id` (`room_type_id`);

--
-- Indexes for table `system_maintenance_requests`
--
ALTER TABLE `system_maintenance_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_vendors`
--
ALTER TABLE `system_vendors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `userlog`
--
ALTER TABLE `userlog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_assigned_hostel` (`assigned_hostel_id`);

--
-- Indexes for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`client_id`,`hostel_id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_registration`
--
ALTER TABLE `client_registration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `daycare_bookings`
--
ALTER TABLE `daycare_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostels`
--
ALTER TABLE `hostels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `hostel_amenities`
--
ALTER TABLE `hostel_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `hostel_images`
--
ALTER TABLE `hostel_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `hostel_services`
--
ALTER TABLE `hostel_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `hostel_types`
--
ALTER TABLE `hostel_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `hostel_type_mapping`
--
ALTER TABLE `hostel_type_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `image_likes`
--
ALTER TABLE `image_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `landlord_payment_methods`
--
ALTER TABLE `landlord_payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `system_maintenance_requests`
--
ALTER TABLE `system_maintenance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_vendors`
--
ALTER TABLE `system_vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `userlog`
--
ALTER TABLE `userlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `children_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `daycare_bookings`
--
ALTER TABLE `daycare_bookings`
  ADD CONSTRAINT `daycare_bookings_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `children` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hostel_amenities`
--
ALTER TABLE `hostel_amenities`
  ADD CONSTRAINT `hostel_amenities_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_amenities_ibfk_2` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_images`
--
ALTER TABLE `hostel_images`
  ADD CONSTRAINT `hostel_images_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_type_mapping`
--
ALTER TABLE `hostel_type_mapping`
  ADD CONSTRAINT `hostel_type_mapping_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_type_mapping_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `hostel_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `image_likes`
--
ALTER TABLE `image_likes`
  ADD CONSTRAINT `image_likes_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `hostel_images` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_assigned_hostel` FOREIGN KEY (`assigned_hostel_id`) REFERENCES `hostels` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
