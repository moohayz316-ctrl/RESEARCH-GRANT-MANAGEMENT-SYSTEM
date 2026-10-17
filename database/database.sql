-- ============================================================
-- Research Grant Management System - Complete Database Schema
-- Generated on 2026-09-28 10:45:23
-- ============================================================

CREATE DATABASE IF NOT EXISTS `research_grants_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `research_grants_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Table structure for `audit_logs`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `entity_type` (`entity_type`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `audit_logs`
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
('1', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 16:16:30'),
('2', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 16:18:52'),
('3', '1', 'login', 'users', '1', 'User logged in', '192.168.121.139', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.7.6 Mobile/15E148 Safari/604.1', '2026-06-15 16:20:31'),
('4', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 16:21:00'),
('5', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-15 16:21:06'),
('6', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-16 21:33:14'),
('7', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 14:10:02'),
('8', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 19:25:50'),
('9', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 19:27:10'),
('10', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 19:37:53'),
('11', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 19:50:30'),
('12', '2', 'register', 'users', '2', 'New user registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 19:51:29'),
('13', '2', 'login', 'users', '2', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 19:51:44'),
('14', '2', 'logout', 'users', '2', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 19:53:00'),
('15', '2', 'login', 'users', '2', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 19:54:28'),
('16', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 19:58:38'),
('17', '2', 'logout', 'users', '2', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 20:27:44'),
('18', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 20:27:58'),
('19', '1', 'create', 'users', '3', 'Created user: Mahi Yunusa (reviewer)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 20:32:06'),
('20', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 20:35:28'),
('21', '3', 'login', 'users', '3', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 20:35:30'),
('22', '3', 'logout', 'users', '3', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 20:35:40'),
('23', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 20:37:32'),
('24', '1', 'create', 'grant_categories', '7', 'Created category: C++ Programming', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 20:38:40'),
('25', '1', 'create', 'grant_categories', '8', 'Created category: ntermediate', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 21:23:32'),
('26', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 21:26:06'),
('27', '2', 'login', 'users', '2', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 21:26:16'),
('28', '2', 'logout', 'users', '2', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 21:26:44'),
('29', '3', 'login', 'users', '3', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 21:26:53'),
('30', '3', 'logout', 'users', '3', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 21:27:22'),
('31', '2', 'login', 'users', '2', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-17 21:35:20'),
('32', '4', 'register', 'users', '4', 'New user registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:42:29'),
('33', '4', 'login', 'users', '4', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:42:53'),
('34', '4', 'logout', 'users', '4', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:43:59'),
('35', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:44:14'),
('36', '1', 'create', 'grants', '1', 'Created grant: tertiary education trust fund (tetfung)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:51:07'),
('37', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:51:17'),
('38', '4', 'login', 'users', '4', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:51:41'),
('39', '4', 'proposal_submitted', 'proposals', '1', 'Proposal submitted for grant: tertiary education trust fund (tetfung)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:53:17'),
('40', '4', 'logout', 'users', '4', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:53:26'),
('41', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:53:39'),
('42', '1', 'assign_reviewer', 'proposal_reviews', '1', 'Assigned reviewer to proposal', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:54:03'),
('43', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:54:09'),
('44', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:54:25'),
('45', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:54:44'),
('46', '3', 'login', 'users', '3', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:54:50'),
('47', '3', 'submit_review', 'proposal_reviews', '1', 'Review submitted for proposal #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:56:13'),
('48', '3', 'logout', 'users', '3', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:56:20'),
('49', '4', 'login', 'users', '4', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:56:30'),
('50', '4', 'logout', 'users', '4', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:57:03');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
('51', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:57:10'),
('52', '1', 'approve', 'proposals', '1', 'Approved proposal, created award #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:57:29'),
('53', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:57:33'),
('54', '3', 'login', 'users', '3', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:57:43'),
('55', '3', 'logout', 'users', '3', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 08:58:00'),
('56', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:07:19'),
('57', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:07:48'),
('58', '4', 'login', 'users', '4', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:07:53'),
('59', '4', 'logout', 'users', '4', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:08:42'),
('60', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:08:49'),
('61', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:09:02'),
('62', '3', 'login', 'users', '3', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:09:06'),
('63', '3', 'logout', 'users', '3', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:09:58'),
('64', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:10:04'),
('65', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:13:31'),
('66', '2', 'login', 'users', '2', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:13:40'),
('67', '2', 'logout', 'users', '2', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:14:20'),
('68', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:14:31'),
('69', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:14:46'),
('70', '2', 'login', 'users', '2', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:15:15'),
('71', '2', 'logout', 'users', '2', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:15:43'),
('72', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:15:54'),
('73', '1', 'update', 'grants', '1', 'Updated grant: tertiary education trust fund (tetfung)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:16:44'),
('74', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:16:50'),
('75', '2', 'login', 'users', '2', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:17:00'),
('76', '2', 'logout', 'users', '2', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:18:02'),
('77', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:18:14'),
('78', '1', 'update', 'grants', '1', 'Updated grant: tertiary education trust fund (tetfung)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:19:55'),
('79', '1', 'create', 'grants', '2', 'Created grant: education', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:21:01'),
('80', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:21:11'),
('81', '2', 'login', 'users', '2', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:21:20'),
('82', '2', 'proposal_submitted', 'proposals', '2', 'Proposal submitted for grant: education', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:27:27'),
('83', '2', 'logout', 'users', '2', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:28:34'),
('84', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-08-04 11:28:50'),
('85', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:37:36'),
('86', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:38:16'),
('87', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:38:32'),
('88', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:39:25'),
('89', '4', 'login', 'users', '4', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:39:35'),
('90', '4', 'logout', 'users', '4', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:39:58'),
('91', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:40:19'),
('92', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-22 13:41:32'),
('93', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 22:22:56'),
('94', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 22:25:19'),
('95', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 22:25:31'),
('96', '1', 'create', 'users', '5', 'Created user: Autho300 Abdullahi Autho300 Autho300 Abdullahi Autho300 (grant_manager)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 22:27:48'),
('97', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 22:27:58'),
('98', '5', 'login', 'users', '5', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 22:28:00'),
('99', '5', 'login', 'users', '5', 'User logged in', '::1', NULL, '2026-09-26 22:39:31'),
('100', '5', 'logout', 'users', '5', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 22:45:13');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
('101', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 22:53:58'),
('102', '1', 'create', 'grants', '3', 'Created grant: edrtfyu7i9o07ygt6rtf5tr5ftrf5t5r', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 22:56:24'),
('103', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 23:02:12'),
('104', '3', 'login', 'users', '3', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 23:02:21'),
('105', '3', 'logout', 'users', '3', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 23:02:51'),
('106', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 23:03:09'),
('107', '1', 'approve', 'proposals', '2', 'Approved proposal, created award #2, approved amount: 100000.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-26 23:20:00'),
('108', '1', 'disburse_fund', 'fund_disbursements', '2', 'Disbursed 50000 for award #1 (First Instalment)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 11:12:51'),
('109', '1', 'disburse_fund', 'fund_disbursements', '3', 'Disbursed 10000 for award #2 (First Instalment)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 11:17:49'),
('110', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 11:22:38'),
('111', '4', 'login', 'users', '4', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 11:22:42'),
('112', '4', 'logout', 'users', '4', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 11:23:16'),
('113', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 11:26:59'),
('114', '1', 'logout', 'users', '1', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 11:27:19'),
('115', '1', 'login', 'users', '1', 'User logged in', NULL, NULL, '2026-09-28 11:40:17'),
('116', '4', 'login', 'users', '4', 'User logged in', NULL, NULL, '2026-09-28 11:40:18'),
('117', '3', 'login', 'users', '3', 'User logged in', NULL, NULL, '2026-09-28 11:40:18'),
('118', '1', 'login', 'users', '1', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.22621.6133', '2026-09-28 11:41:55'),
('119', '1', 'login', 'users', '1', 'User logged in', '::1', NULL, '2026-09-28 11:42:34');

-- ------------------------------------------------------------
-- Table structure for `final_reports`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `final_reports`;
CREATE TABLE `final_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `award_id` int(11) NOT NULL,
  `researcher_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executive_summary` text COLLATE utf8mb4_unicode_ci,
  `introduction` text COLLATE utf8mb4_unicode_ci,
  `methodology` text COLLATE utf8mb4_unicode_ci,
  `results` text COLLATE utf8mb4_unicode_ci,
  `discussion` text COLLATE utf8mb4_unicode_ci,
  `conclusion` text COLLATE utf8mb4_unicode_ci,
  `recommendations` text COLLATE utf8mb4_unicode_ci,
  `references` text COLLATE utf8mb4_unicode_ci,
  `financial_report` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `review_comment` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `award_id` (`award_id`),
  KEY `researcher_id` (`researcher_id`),
  CONSTRAINT `fk_final_award` FOREIGN KEY (`award_id`) REFERENCES `grant_awards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_final_researcher` FOREIGN KEY (`researcher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table structure for `fund_disbursements`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `fund_disbursements`;
CREATE TABLE `fund_disbursements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `award_id` int(11) NOT NULL,
  `grant_id` int(11) NOT NULL,
  `researcher_id` int(11) NOT NULL,
  `disbursed_by` int(11) DEFAULT NULL,
  `stage` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'First Instalment',
  `disbursed_amount` decimal(15,2) NOT NULL,
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disbursement_date` date NOT NULL,
  `status` enum('scheduled','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paid',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `award_id` (`award_id`),
  KEY `researcher_id` (`researcher_id`),
  KEY `status` (`status`),
  KEY `fk_disb_by` (`disbursed_by`),
  CONSTRAINT `fk_disb_award` FOREIGN KEY (`award_id`) REFERENCES `grant_awards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_disb_by` FOREIGN KEY (`disbursed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_disb_researcher` FOREIGN KEY (`researcher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `fund_disbursements`
INSERT INTO `fund_disbursements` (`id`, `award_id`, `grant_id`, `researcher_id`, `disbursed_by`, `stage`, `disbursed_amount`, `payment_reference`, `disbursement_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
('2', '1', '1', '4', '1', 'First Instalment', '50000.00', 'DBG-001', '2026-09-27', 'paid', 'debug', '2026-09-27 11:12:51', '2026-09-27 11:12:51'),
('3', '2', '2', '2', '1', 'First Instalment', '10000.00', 'scdfghjkl;jhggyujiko', '2026-09-27', 'paid', 'awsdrftuhjikopl;lkjhugytfdr', '2026-09-27 11:17:49', '2026-09-27 11:17:49');

-- ------------------------------------------------------------
-- Table structure for `grant_awards`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `grant_awards`;
CREATE TABLE `grant_awards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proposal_id` int(11) NOT NULL,
  `grant_id` int(11) NOT NULL,
  `researcher_id` int(11) NOT NULL,
  `awarded_amount` decimal(15,2) NOT NULL,
  `approved_amount` decimal(15,2) DEFAULT NULL COMMENT 'Final admin-approved disbursable amount',
  `total_disbursed` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Running sum of all disbursements',
  `award_date` date NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','completed','terminated','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `terms_conditions` text COLLATE utf8mb4_unicode_ci,
  `approval_letter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `awarded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `proposal_id` (`proposal_id`),
  KEY `grant_id` (`grant_id`),
  KEY `researcher_id` (`researcher_id`),
  KEY `status` (`status`),
  KEY `fk_awards_awarder` (`awarded_by`),
  CONSTRAINT `fk_awards_awarder` FOREIGN KEY (`awarded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_awards_grant` FOREIGN KEY (`grant_id`) REFERENCES `grants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_awards_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_awards_researcher` FOREIGN KEY (`researcher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `grant_awards`
INSERT INTO `grant_awards` (`id`, `proposal_id`, `grant_id`, `researcher_id`, `awarded_amount`, `approved_amount`, `total_disbursed`, `award_date`, `start_date`, `end_date`, `status`, `terms_conditions`, `approval_letter`, `awarded_by`, `created_at`, `updated_at`) VALUES
('1', '1', '1', '4', '1000000.00', NULL, '50000.00', '2026-07-05', '2026-07-05', '2027-07-05', 'active', NULL, NULL, '1', '2026-07-05 08:57:29', '2026-09-27 11:12:51'),
('2', '2', '2', '2', '100000.00', '100000.00', '10000.00', '2026-09-26', '2026-09-26', '2027-09-26', 'active', NULL, NULL, '1', '2026-09-26 23:20:00', '2026-09-27 11:17:49');

-- ------------------------------------------------------------
-- Table structure for `grant_categories`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `grant_categories`;
CREATE TABLE `grant_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `grant_categories`
INSERT INTO `grant_categories` (`id`, `name`, `description`, `icon`, `color`, `is_active`, `created_at`, `updated_at`) VALUES
('1', 'Science & Technology', 'Research in scientific and technological fields', 'bi bi-rocket-takeoff', '#0D6EFD', '1', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('2', 'Medical & Health', 'Medical and health-related research', 'bi bi-heart-pulse', '#DC3545', '1', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('3', 'Social Sciences', 'Social science and humanities research', 'bi bi-people', '#198754', '1', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('4', 'Environmental Studies', 'Environmental and climate research', 'bi bi-tree', '#20C997', '1', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('5', 'Engineering', 'Engineering and applied sciences', 'bi bi-gear', '#FD7E14', '1', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('6', 'Education', 'Educational research and pedagogy', 'bi bi-book', '#6F42C1', '1', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('7', 'C++ Programming', 'DataTables warning: table id=DataTables_Table_0 - Incorrect column count. For more information about this error, please see https://datatables.net/tn/18', '', '#000000', '1', '2026-06-17 20:38:40', '2026-06-17 20:38:40'),
('8', 'ntermediate', '', '', '#000000', '1', '2026-06-17 21:23:32', '2026-06-17 21:23:32');

-- ------------------------------------------------------------
-- Table structure for `grants`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `grants`;
CREATE TABLE `grants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `objectives` text COLLATE utf8mb4_unicode_ci,
  `eligibility_criteria` text COLLATE utf8mb4_unicode_ci,
  `funding_amount` decimal(15,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `status` enum('draft','open','closed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `application_start_date` date DEFAULT NULL,
  `application_end_date` date DEFAULT NULL,
  `review_deadline` date DEFAULT NULL,
  `max_applications` int(11) DEFAULT NULL,
  `guidelines_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `is_published` (`is_published`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `fk_grants_category` FOREIGN KEY (`category_id`) REFERENCES `grant_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_grants_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `grants`
INSERT INTO `grants` (`id`, `title`, `slug`, `description`, `objectives`, `eligibility_criteria`, `funding_amount`, `category_id`, `status`, `application_start_date`, `application_end_date`, `review_deadline`, `max_applications`, `guidelines_file`, `is_published`, `created_by`, `created_at`, `updated_at`) VALUES
('1', 'tertiary education trust fund (tetfung)', 'tertiary-education-trust-fund-tetfung', 'institution based research grant', 'Provide funding for innovative research projects that address national development challenges.\r\n3. Encourage lecturers and researchers to produce impactful research with practical applications.', '1. Applicant must be a full-time academic or researcher in an accredited tertiary institution.\r\n2. The institution must be eligible to receive the grant.\r\n3. The proposed research must address a relevant academic, scientific, technological, or societal problem.\r\n4. A complete research proposal must be submitted before the application deadline.', '1000000.00', '7', 'open', '2026-08-04', '2026-07-08', '2026-08-04', '10', '6a4a0ceb01b8b_1783237867.docx', '1', '1', '2026-07-05 08:51:07', '2026-08-04 11:19:55'),
('2', 'education', 'education', '4.3.7 Proposal Submission Module\r\n\r\nResearchers use this module to complete proposal forms, upload required documents, and submit applications for grant consideration.\r\n\r\nFigure 4.8: Proposal Submission Page', '4.3.7 Proposal Submission Module\r\n\r\nResearchers use this module to complete proposal forms, upload required documents, and submit applications for grant consideration.\r\n\r\nFigure 4.8: Proposal Submission Page', '4.3.7 Proposal Submission Module\r\n\r\nResearchers use this module to complete proposal forms, upload required documents, and submit applications for grant consideration.\r\n\r\nFigure 4.8: Proposal Submission Page', '3000000.00', '6', 'open', '2026-08-04', '2026-08-29', '2026-08-31', '30', NULL, '1', '1', '2026-08-04 11:21:01', '2026-08-04 11:21:01'),
('3', 'edrtfyu7i9o07ygt6rtf5tr5ftrf5t5r', 'edrtfyu7i9o07ygt6rtf5tr5ftrf5t5r', 'r5y6y77y777y78y7y8u7u8yu8u8tftfgyggygygyyy', 'dtrrdrdrttrddtrdtrdrdrdrdrddrrrttrddrdrdrdrdrtdrdrdrdrdrtrd', 'yuhgyyhmjjj', '100000000.00', '6', 'open', '2026-09-26', '2026-09-28', '2026-09-28', '10', '6ab83f8893f68_1790459784.pdf', '1', '1', '2026-09-26 22:56:24', '2026-09-26 22:56:24');

-- ------------------------------------------------------------
-- Table structure for `notifications`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `sent_via_email` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `notifications`
INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `sent_via_email`, `created_at`) VALUES
('1', '2', 'welcome', 'Welcome to MUH Research Grant System', 'Your account has been created successfully. Please complete your profile.', 'http://localhost/research/researcher/pages/profile.php', '0', '0', '2026-06-17 19:51:29'),
('2', '3', 'account', 'Account Created', 'Your account has been created by an administrator.', 'http://localhost/research/login.php', '0', '0', '2026-06-17 20:32:06'),
('3', '4', 'welcome', 'Welcome to MUH Research Grant System', 'Your account has been created successfully. Please complete your profile.', 'http://localhost/research/researcher/pages/profile.php', '0', '0', '2026-07-05 08:42:29'),
('4', '4', 'review_submitted', 'Review Completed', 'Your proposal \"daniska@gmail.com\" has been reviewed.', 'http://localhost/research/researcher/pages/my_proposals.php', '0', '0', '2026-07-05 08:56:13'),
('5', '5', 'account', 'Account Created', 'Your account has been created by an administrator.', 'http://localhost/research/login.php', '0', '0', '2026-09-26 22:27:48'),
('6', '2', 'grant', 'New Grant Opportunity: edrtfyu7i9o07ygt6rtf5tr5ftrf5t5r', 'A new grant opportunity has been published: r5y6y77y777y78y7y8u7u8yu8u8tftfgyggygygyyy', 'http://localhost/research/researcher/pages/grants.php', '0', '0', '2026-09-26 22:56:24'),
('7', '4', 'grant', 'New Grant Opportunity: edrtfyu7i9o07ygt6rtf5tr5ftrf5t5r', 'A new grant opportunity has been published: r5y6y77y777y78y7y8u7u8yu8u8tftfgyggygygyyy', 'http://localhost/research/researcher/pages/grants.php', '0', '0', '2026-09-26 22:56:24'),
('8', '2', 'grant_awarded', 'Proposal Approved & Grant Awarded!', 'Congratulations! Your research proposal \"AI-Based Smart Irrigation System for Smallholder Farmers\" for \"education\" has been approved. An official grant award has been issued.', 'http://localhost/research/researcher/pages/my_proposals.php', '0', '0', '2026-09-26 23:20:00'),
('9', '4', 'fund_disbursed', 'Fund Disbursement Recorded', 'A fund disbursement of NGN50,000.00 (First Instalment) has been recorded for your project. Reference: DBG-001. Note: Actual fund transfer is carried out by the institution through its authorized financial channels.', 'http://localhost/research/researcher/pages/funding_history.php', '0', '0', '2026-09-27 11:12:51'),
('10', '2', 'fund_disbursed', 'Fund Disbursement Recorded', 'A fund disbursement of NGN10,000.00 (First Instalment) has been recorded for your project. Reference: scdfghjkl;jhggyujiko. Note: Actual fund transfer is carried out by the institution through its authorized financial channels.', 'http://localhost/research/researcher/pages/funding_history.php', '0', '0', '2026-09-27 11:17:49');

-- ------------------------------------------------------------
-- Table structure for `progress_reports`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `progress_reports`;
CREATE TABLE `progress_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `award_id` int(11) NOT NULL,
  `researcher_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `report_type` enum('monthly','quarterly','biannual','annual','final') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'quarterly',
  `report_period_start` date DEFAULT NULL,
  `report_period_end` date DEFAULT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `achievements` text COLLATE utf8mb4_unicode_ci,
  `challenges` text COLLATE utf8mb4_unicode_ci,
  `next_steps` text COLLATE utf8mb4_unicode_ci,
  `financial_update` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','submitted','reviewed','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `review_comment` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `award_id` (`award_id`),
  KEY `researcher_id` (`researcher_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_progress_award` FOREIGN KEY (`award_id`) REFERENCES `grant_awards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_progress_researcher` FOREIGN KEY (`researcher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table structure for `project_milestones`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `project_milestones`;
CREATE TABLE `project_milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `award_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `due_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed','overdue') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `deliverables` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `award_id` (`award_id`),
  CONSTRAINT `fk_milestones_award` FOREIGN KEY (`award_id`) REFERENCES `grant_awards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table structure for `proposal_documents`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `proposal_documents`;
CREATE TABLE `proposal_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proposal_id` int(11) NOT NULL,
  `document_type` enum('proposal','budget','supporting','ethics','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `proposal_id` (`proposal_id`),
  CONSTRAINT `fk_proposal_docs_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `proposal_documents`
INSERT INTO `proposal_documents` (`id`, `proposal_id`, `document_type`, `file_name`, `file_path`, `file_size`, `file_type`, `uploaded_at`) VALUES
('1', '1', 'proposal', '6a4a0d6d4d25b_1783237997.docx', '6a4a0d6d4d25b_1783237997.docx', '36126', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-07-05 08:53:17'),
('2', '1', 'budget', '6a4a0d6d4f1d6_1783237997.docx', '6a4a0d6d4f1d6_1783237997.docx', '36126', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-07-05 08:53:17'),
('3', '1', 'supporting', '6a4a0d6d5082a_1783237997.docx', '6a4a0d6d5082a_1783237997.docx', '36126', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-07-05 08:53:17'),
('4', '2', 'proposal', '6a71be8fbb992_1785839247.docx', '6a71be8fbb992_1785839247.docx', '18057', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-08-04 11:27:27'),
('5', '2', 'budget', '6a71be8fbddd8_1785839247.docx', '6a71be8fbddd8_1785839247.docx', '18057', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-08-04 11:27:27'),
('6', '2', 'supporting', '6a71be8fbedbd_1785839247.docx', '6a71be8fbedbd_1785839247.docx', '18057', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '2026-08-04 11:27:27');

-- ------------------------------------------------------------
-- Table structure for `proposal_reviews`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `proposal_reviews`;
CREATE TABLE `proposal_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proposal_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `recommendation` enum('approve','reject','revision') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recommended_amount` decimal(15,2) DEFAULT NULL COMMENT 'Funding amount recommended by reviewer',
  `evaluation_report` text COLLATE utf8mb4_unicode_ci,
  `status` enum('assigned','in_progress','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'assigned',
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `proposal_id` (`proposal_id`),
  KEY `reviewer_id` (`reviewer_id`),
  KEY `status` (`status`),
  KEY `fk_reviews_assigner` (`assigned_by`),
  CONSTRAINT `fk_reviews_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_reviews_proposal` FOREIGN KEY (`proposal_id`) REFERENCES `proposals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reviews_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `proposal_reviews`
INSERT INTO `proposal_reviews` (`id`, `proposal_id`, `reviewer_id`, `assigned_by`, `score`, `comments`, `recommendation`, `recommended_amount`, `evaluation_report`, `status`, `assigned_at`, `completed_at`, `created_at`, `updated_at`) VALUES
('1', '1', '3', '1', '99.00', 'Provide funding for innovative research projects that address national development challenges.\r\n3. Encourage lecturers and researchers to produce impactful research with practical applications.', 'approve', NULL, 'Provide funding for innovative research projects that address national development challenges.\r\n3. Encourage lecturers and researchers to produce impactful research with practical applications.', 'completed', '2026-07-05 08:54:03', '2026-07-05 08:56:13', '2026-07-05 08:54:03', '2026-07-05 08:56:13');

-- ------------------------------------------------------------
-- Table structure for `proposals`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `proposals`;
CREATE TABLE `proposals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grant_id` int(11) NOT NULL,
  `researcher_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abstract` text COLLATE utf8mb4_unicode_ci,
  `problem_statement` text COLLATE utf8mb4_unicode_ci,
  `methodology` text COLLATE utf8mb4_unicode_ci,
  `expected_outcomes` text COLLATE utf8mb4_unicode_ci,
  `budget_breakdown` text COLLATE utf8mb4_unicode_ci,
  `budget_amount` decimal(15,2) DEFAULT NULL,
  `status` enum('draft','submitted','under_review','approved','rejected','revision_needed','withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `review_score` decimal(5,2) DEFAULT NULL,
  `review_comment` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` datetime DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `grant_id` (`grant_id`),
  KEY `researcher_id` (`researcher_id`),
  KEY `status` (`status`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `fk_proposals_grant` FOREIGN KEY (`grant_id`) REFERENCES `grants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_proposals_researcher` FOREIGN KEY (`researcher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_proposals_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `proposals`
INSERT INTO `proposals` (`id`, `grant_id`, `researcher_id`, `title`, `abstract`, `problem_statement`, `methodology`, `expected_outcomes`, `budget_breakdown`, `budget_amount`, `status`, `review_score`, `review_comment`, `submitted_at`, `reviewed_at`, `reviewed_by`, `created_at`, `updated_at`) VALUES
('1', '1', '4', 'daniska@gmail.com', 'Provide funding for innovative research projects that address national development challenges.\r\n3. Encourage lecturers and researchers to produce impactful research with practical applications.', '1. Applicant must be a full-time academic or researcher in an accredited tertiary institution.\r\n2. The institution must be eligible to receive the grant.\r\n3. The proposed research must address a relevant academic, scientific, technological, or societal problem.\r\n4. A complete research proposal must be submitted before the application deadline.', 'Provide funding for innovative research projects that address national development challenges.\r\n3. Encourage lecturers and researchers to produce impactful research with practical applications.', 'Provide funding for innovative research projects that address national development challenges.\r\n3. Encourage lecturers and researchers to produce impactful research with practical applications.', '1. Applicant must be a full-time academic or researcher in an accredited tertiary institution.\r\n2. The institution must be eligible to receive the grant.\r\n3. The proposed research must address a relevant academic, scientific, technological, or societal problem.\r\n4. A complete research proposal must be submitted before the application deadline.', '10000.00', 'approved', '99.00', NULL, '2026-07-05 07:53:17', '2026-07-05 08:57:29', '1', '2026-07-05 08:53:17', '2026-07-05 08:57:29'),
('2', '2', '2', 'AI-Based Smart Irrigation System for Smallholder Farmers', '# Abstract\r\n\r\nAgriculture remains one of the most important sectors of the economy, yet many smallholder farmers continue to face challenges related to inefficient water management, increasing climate variability, and declining crop productivity. This project proposes the development of an **Artificial Intelligence (AI)-Based Smart Irrigation System** that utilizes Internet of Things (IoT) sensors, machine learning algorithms, and real-time environmental data to optimize irrigation scheduling and water usage.\r\n\r\nThe proposed system will continuously monitor soil moisture, temperature, humidity, and weather conditions to determine the precise amount of water required for different crops. By automating irrigation decisions, the system aims to reduce water wastage, improve crop yields, lower production costs, and promote sustainable agricultural practices. The project will involve system design, prototype development, field testing, performance evaluation, and farmer training to ensure effective adoption.\r\n\r\nThe expected outcomes include improved irrigation efficiency, increased agricultural productivity, enhanced food security, and reduced environmental impact. The project aligns with national priorities in agricultural innovation, climate-smart farming, and digital transformation, making it a valuable solution for supporting sustainable rural development.', '# Problem Statement\r\n\r\nAgriculture is a major source of livelihood for millions of smallholder farmers, yet inefficient irrigation practices continue to limit agricultural productivity and waste valuable water resources. Many farmers rely on fixed irrigation schedules or manual observations rather than accurate, real-time data on soil moisture and weather conditions. As a result, crops are often overwatered or underwatered, leading to reduced yields, increased production costs, and unnecessary depletion of water resources.\r\n\r\nClimate change has further intensified these challenges through irregular rainfall patterns, prolonged droughts, and rising temperatures, making traditional irrigation methods less effective. In many rural communities, the lack of affordable smart irrigation technologies and limited access to data-driven decision-making tools prevent farmers from optimizing water use and improving farm productivity.\r\n\r\nThere is therefore a need for an intelligent, cost-effective irrigation system that can monitor environmental conditions in real time, analyze data using Artificial Intelligence (AI), and automatically recommend or control irrigation schedules. Such a solution will help farmers conserve water, improve crop yields, reduce operational costs, and promote sustainable agricultural practices while strengthening food security and climate resilience.', '# Methodology\r\n\r\nThe project will adopt a systematic research and development approach to design, develop, implement, and evaluate an AI-Based Smart Irrigation System for smallholder farmers. The methodology consists of the following phases:\r\n\r\n## 1. Requirement Analysis\r\n\r\nA comprehensive needs assessment will be conducted through literature review, stakeholder consultations, and field surveys to identify the irrigation challenges faced by smallholder farmers. Functional and non-functional system requirements will be documented.\r\n\r\n## 2. System Design\r\n\r\nThe overall architecture of the smart irrigation system will be designed, including IoT sensors for monitoring soil moisture, temperature, humidity, and weather conditions. The database structure, communication protocols, and user interface will also be designed.\r\n\r\n## 3. Data Collection\r\n\r\nEnvironmental and agricultural data will be collected from sensors and external weather services. Historical irrigation and crop performance data will also be gathered to support the development of predictive machine learning models.\r\n\r\n## 4. AI Model Development\r\n\r\nMachine learning algorithms will be developed and trained using the collected data to predict irrigation requirements based on soil conditions, weather forecasts, crop type, and growth stage. The models will be evaluated and optimized to improve prediction accuracy.\r\n\r\n## 5. System Development\r\n\r\nThe smart irrigation application will be developed by integrating IoT devices, AI models, databases, and a web-based monitoring dashboard. The system will support automated irrigation scheduling and real-time monitoring.\r\n\r\n## 6. Testing and Validation\r\n\r\nThe developed system will undergo unit testing, integration testing, system testing, and user acceptance testing. Field trials will be conducted on selected farms to evaluate system performance, irrigation efficiency, water conservation, and crop productivity.\r\n\r\n## 7. Deployment\r\n\r\nFollowing successful testing, the system will be deployed in pilot farming communities. Installation, configuration, and operational support will be provided to participating farmers.\r\n\r\n## 8. Training and Evaluation\r\n\r\nTraining sessions will be organized for farmers and agricultural extension officers on system operation and maintenance. The effectiveness of the solution will be assessed using key performance indicators such as water savings, crop yield improvement, user satisfaction, and overall system reliability.\r\n\r\n## Expected Outcome\r\n\r\nThe methodology is expected to deliver a reliable and intelligent irrigation system capable of optimizing water usage, improving crop productivity, reducing operational costs, and supporting sustainable agricultural practices through data-driven decision-making.', '# Expected Outcomes\r\n\r\nThe successful implementation of the **AI-Based Smart Irrigation System** is expected to deliver significant benefits to farmers, agricultural stakeholders, and the wider community. The anticipated outcomes include:\r\n\r\n1. **Improved Water Use Efficiency**\r\n\r\n   * Optimize irrigation schedules using real-time environmental data.\r\n   * Reduce unnecessary water consumption and wastage.\r\n\r\n2. **Increased Agricultural Productivity**\r\n\r\n   * Improve crop growth and yield through precise irrigation management.\r\n   * Minimize crop stress caused by overwatering or underwatering.\r\n\r\n3. **Reduced Production Costs**\r\n\r\n   * Lower water and energy consumption.\r\n   * Reduce labor costs associated with manual irrigation.\r\n\r\n4. **Enhanced Decision-Making**\r\n\r\n   * Provide farmers with data-driven recommendations for irrigation planning.\r\n   * Enable timely responses to changing weather and soil conditions.\r\n\r\n5. **Environmental Sustainability**\r\n\r\n   * Promote responsible use of water resources.\r\n   * Reduce the environmental impact of inefficient irrigation practices.\r\n   * Support climate-smart agricultural practices.\r\n\r\n6. **Improved Food Security**\r\n\r\n   * Increase agricultural output to support local food production.\r\n   * Strengthen the resilience of farming communities against climate variability.\r\n\r\n7. **Technology Adoption**\r\n\r\n   * Encourage the use of Artificial Intelligence (AI) and Internet of Things (IoT) technologies in agriculture.\r\n   * Increase farmers&#039; digital literacy through training and practical application.\r\n\r\n8. **Scalable and Replicable Solution**\r\n\r\n   * Develop a smart irrigation model that can be adapted to different crops, regions, and farming conditions.\r\n   * Provide a foundation for future agricultural innovation projects.\r\n\r\n## Project Deliverables\r\n\r\nUpon completion of the project, the following deliverables are expected:\r\n\r\n* A fully functional AI-Based Smart Irrigation System.\r\n* IoT-enabled soil moisture and environmental monitoring devices.\r\n* A web-based monitoring and management dashboard.\r\n* Machine learning models for irrigation prediction and scheduling.\r\n* Technical documentation and user manuals.\r\n* Training materials for farmers and extension officers.\r\n* Pilot implementation and evaluation report.\r\n* Final project report with recommendations for large-scale deployment.\r\n\r\n## Success Indicators\r\n\r\nThe project&#039;s success will be measured using the following key performance indicators (KPIs):\r\n\r\n* At least **30% reduction** in water consumption.\r\n* Up to **20% increase** in crop yield.\r\n* Improved irrigation scheduling accuracy.\r\n* High user satisfaction among participating farmers.\r\n* Successful deployment and operation during the pilot phase.\r\n* Positive environmental and economic impact on participating farms.', '# Budget Breakdown\r\n\r\n**Project Title:** AI-Based Smart Irrigation System for Smallholder Farmers\r\n\r\n**Total Budget:** **₦100,000**\r\n\r\n| S/N | Budget Item                 | Description                                                                        |   Amount (₦) |\r\n| --: | --------------------------- | ---------------------------------------------------------------------------------- | -----------: |\r\n|   1 | Research and Planning       | Literature review, field assessment, and project planning                          |       10,000 |\r\n|   2 | Hardware Components         | Soil moisture sensor, temperature sensor, microcontroller, cables, and accessories |       30,000 |\r\n|   3 | Software Development        | Development of the AI application and system integration                           |       20,000 |\r\n|   4 | Data Collection and Testing | Field testing, internet access, and data acquisition                               |       10,000 |\r\n|   5 | Transportation              | Travel for field visits and project monitoring                                     |        8,000 |\r\n|   6 | Training and Demonstration  | Training materials and farmer awareness sessions                                   |        7,000 |\r\n|   7 | Documentation and Printing  | Reports, manuals, and printing of project documents                                |        5,000 |\r\n|   8 | Contingency                 | Unforeseen expenses (5% of total budget)                                           |       10,000 |\r\n|     | **Total**                   |                                                                                    | **₦100,000** |\r\n\r\n## Budget Justification\r\n\r\n* **Research and Planning (₦10,000):** Covers preliminary research, project planning, and stakeholder consultations.\r\n* **Hardware Components (₦30,000):** Used to procure essential sensors, a microcontroller, and related electronic components required for the prototype.\r\n* **Software Development (₦20,000):** Supports the development and integration of the AI-based irrigation software.\r\n* **Data Collection and Testing (₦10,000):** Covers internet access, field testing, and collection of environmental data.\r\n* **Transportation (₦8,000):** Facilitates travel to project sites for installation, monitoring, and evaluation.\r\n* **Training and Demonstration (₦7,000):** Provides training materials and practical demonstrations for end users.\r\n* **Documentation and Printing (₦5,000):** Covers the preparation and printing of technical reports and user manuals.\r\n* **Contingency (₦10,000):** Reserved for unexpected expenses that may arise during project implementation.', '100000.00', 'approved', NULL, NULL, '2026-08-04 10:27:27', '2026-09-26 23:20:00', '1', '2026-08-04 11:27:27', '2026-09-26 23:20:00');

-- ------------------------------------------------------------
-- Table structure for `system_settings`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_group` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `system_settings`
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `created_at`, `updated_at`) VALUES
('1', 'site_name', 'Research Grant Management System', 'general', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('2', 'site_description', 'A comprehensive platform for managing research grants, proposals, and awards.', 'general', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('3', 'site_email', 'info@researchgrants.com', 'general', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('4', 'site_phone', '+1 (555) 123-4567', 'general', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('5', 'site_address', '123 University Avenue, Academic City, AC 12345', 'general', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('6', 'currency_symbol', '$', 'general', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('7', 'max_file_size', '10485760', 'general', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('8', 'allowed_file_types', 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif', 'general', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('9', 'submission_deadline_reminder_days', '7', 'notifications', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('10', 'auto_approve_reviewers', '0', 'review', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('11', 'min_reviewers_per_proposal', '2', 'review', '2026-06-15 14:29:24', '2026-06-15 14:29:24'),
('12', 'max_review_score', '100', 'review', '2026-06-15 14:29:24', '2026-06-15 14:29:24');

-- ------------------------------------------------------------
-- Table structure for `users`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','researcher','reviewer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'researcher',
  `profile_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institution` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biography` text COLLATE utf8mb4_unicode_ci,
  `orcid_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_scholar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `research_interests` text COLLATE utf8mb4_unicode_ci,
  `qualification` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cv_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expertise_area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewer_status` enum('pending','active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` datetime DEFAULT NULL,
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `users`
INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `role`, `profile_picture`, `phone`, `department`, `institution`, `biography`, `orcid_id`, `google_scholar`, `research_interests`, `qualification`, `cv_file`, `expertise_area`, `reviewer_status`, `is_active`, `email_verified`, `email_verified_at`, `verification_token`, `reset_token`, `reset_token_expires`, `last_login`, `created_at`, `updated_at`) VALUES
('1', 'admin', 'admin@researchgrants.com', '$2a$12$v72cxjR0nWsWRjkTjEC4huM4vvKlx740ffyD4ArGK3NOMxm.9OD/m', 'System', 'Administrator', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '1', '1', NULL, NULL, NULL, NULL, '2026-09-28 11:42:34', '2026-06-15 14:29:24', '2026-09-28 11:42:34'),
('2', 'yz210', 'yz@gmail.com', '$2y$10$X0sR28nXTxO9ill1NIq4ge18GTpZ7eKo1Lthzpi.g7g/uOvms57PC', 'muh', 'yz', 'researcher', NULL, '+234 802 987 6543', 'Faculty of Renewable Energy & Engineering', 'Bayero University, Kano', 'Lead researcher at Center for Renewable Energy Systems focusing on decentralized community solar microgrids.', '0000-0003-4567-8901', 'https://scholar.google.com/citations?user=sample456', 'Solar Photovoltaic Systems, Energy Storage, Smart Grid Automation', 'M.Sc. in Renewable Systems', NULL, 'Solar Energy & Smart Grids', 'pending', '1', '0', NULL, '69b9387e2ee5435707b4c91f0a289787d77b13c6da1d27cd4fe9e7024985b505', NULL, NULL, '2026-08-04 12:21:20', '2026-06-17 19:51:29', '2026-09-26 22:52:49'),
('3', 'mahi.yunusa360', 'mahi@gmail.com', '$2y$10$hDGTjuX/.57J9SdKIGYGIul.BPorYtV3u4TZFzPnwtm/O3S2k4.YK', 'Mahi', 'Yunusa', 'reviewer', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, 'software engineer', 'pending', '1', '1', NULL, NULL, NULL, NULL, '2026-09-28 11:40:18', '2026-06-17 20:32:06', '2026-09-28 11:40:18'),
('4', 'daniska001', 'daniska@gmail.com', '$2y$10$qlEE3/07MsJhi9sCS/FyPu9sP8vzh4XXWvVjZ.PZrL292m22Bv3WW', 'muha', 'dan iska', 'researcher', NULL, '+234 803 123 4567', 'Department of Computer Science', 'Ahmadu Bello University, Zaria', 'Senior research fellow with over 8 years experience in applying deep learning models for precision agriculture and sensor networks.', '0000-0002-1825-0097', 'https://scholar.google.com/citations?user=sample123', 'Machine Learning, IoT Sensor Networks, Climate-Smart Agriculture, Big Data Analytics', 'Ph.D. in Computer Science', NULL, 'Artificial Intelligence & Agri-Tech', 'pending', '1', '0', NULL, '80c07d45ba9a4bb80dd870d49d87693ff9cdb143d243199b49325cc521bd3e8c', NULL, NULL, '2026-09-28 11:40:18', '2026-07-05 08:42:29', '2026-09-28 11:40:18'),
('5', 'autho300 abdullahi autho300.autho300 abdullahi autho300132', 'grant@gmail.com', '$2y$10$9Mt3G060i.f/q5oyGVzxfeFYKsPe5xFtM6Goy3vUuujaYjHfrQywi', 'Autho300 Abdullahi Autho300', 'Autho300 Abdullahi Autho300', 'admin', NULL, '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '', 'pending', '1', '1', NULL, NULL, NULL, NULL, '2026-09-26 22:39:31', '2026-09-26 22:27:48', '2026-09-27 11:21:20');

SET FOREIGN_KEY_CHECKS = 1;
