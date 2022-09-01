/*
 Navicat Premium Data Transfer

 Source Server         : localhost_1
 Source Server Type    : MySQL
 Source Server Version : 100424
 Source Host           : localhost:3306
 Source Schema         : geniusbank_db

 Target Server Type    : MySQL
 Target Server Version : 100424
 File Encoding         : 65001

 Date: 21/07/2022 10:22:11
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for account_processes
-- ----------------------------
DROP TABLE IF EXISTS `account_processes`;
CREATE TABLE `account_processes`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `details` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of account_processes
-- ----------------------------
INSERT INTO `account_processes` VALUES (1, 'All Done', 'It is a secure way to authenticate whether the customer who is making an online purchase is the rightful owner of the debit card being used.', '2022-02-21 05:29:32', '2022-03-15 00:03:39');
INSERT INTO `account_processes` VALUES (2, 'Pay For Your Transfer', 'It is a secure way to authenticate whether the customer who is making an online purchase is the rightful owner of the debit card being used.', '2022-02-21 05:29:57', '2022-03-15 00:03:29');
INSERT INTO `account_processes` VALUES (3, 'Set Up A Transfer', 'It is a secure way to authenticate whether the customer who is making an online purchase is the rightful owner of the debit card being used.', '2022-02-21 05:30:19', '2022-03-15 00:03:18');
INSERT INTO `account_processes` VALUES (4, 'Tell Us About Your Business', 'It is a secure way to authenticate whether the customer who is making an online purchase is the rightful owner of the debit card being used.', '2022-02-21 05:30:39', '2022-03-15 00:03:07');
INSERT INTO `account_processes` VALUES (5, 'Register in Minutes', 'You can register this system within a few minutes. Go to Register page for registration.', '2022-02-21 05:30:56', '2022-03-15 00:02:14');

-- ----------------------------
-- Table structure for admin_languages
-- ----------------------------
DROP TABLE IF EXISTS `admin_languages`;
CREATE TABLE `admin_languages`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(4) NOT NULL DEFAULT 0,
  `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rtl` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_languages
-- ----------------------------
INSERT INTO `admin_languages` VALUES (1, 1, 'En', '1603880510hWH6gk7S.json', '1603880510hWH6gk7S', 0, NULL, NULL);

-- ----------------------------
-- Table structure for admin_user_conversations
-- ----------------------------
DROP TABLE IF EXISTS `admin_user_conversations`;
CREATE TABLE `admin_user_conversations`  (
  `id` int(191) NOT NULL AUTO_INCREMENT,
  `subject` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(191) NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for admin_user_messages
-- ----------------------------
DROP TABLE IF EXISTS `admin_user_messages`;
CREATE TABLE `admin_user_messages`  (
  `id` int(191) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(191) NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(191) NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 50 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for admins
-- ----------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  `tenant_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `zip` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `city` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `country_id` int(11) NULL DEFAULT NULL,
  `vat` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `plan_id` int(11) NULL DEFAULT NULL,
  `section` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `admins_email_unique`(`email`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 76 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admins
-- ----------------------------
INSERT INTO `admins` VALUES (1, 'Supper Admin', 'admin@gmail.com', '01629552892', '1639300861admin.jpg', '$2y$10$NSxBfIBeDdxRjisT83p/0uN4GN4LcbYvKzuazAfyekwPffExwBUpO', 1, 'x9hxNawwQu0bPl7yNNaenNvwFxQ0I2s5JiFoArJwnZWyDJDYvPrV3fZYM0Qh', '2018-03-01 00:27:08', '2022-06-27 11:08:56', NULL, NULL, NULL, NULL, 127, NULL, 0, 'Manage Customers , Loan Management , DPS Management , FDR Management , Manage Escrow , Bank Transfer , Wire Transfer , Request Money , Withdraw , Deposit , Transactions , Deposits , Currency Setting , General Setting , Home page Setting , Email Setting , Language Manage');
INSERT INTO `admins` VALUES (10, 'violetfocus1', 'violetfocus0618@gmail.com', '123123123', 'onO5Lo131652995971.png', '$2y$10$7UkPuoJ9.P58z6iCjagG.uS3JF6Is/G4OwIshyUbngLE2R3R0djvC', 1, NULL, '2022-05-09 08:24:28', '2022-06-30 15:41:54', '43', '1234', 'Serbia', 'Noname address', 191, '11111', 1, 'Manage Customers , Loan Management , DPS Management , FDR Management , Manage Escrow , Bank Transfer , Wire Transfer , Request Money , Withdraw , Deposit , Transactions , Deposits , Currency Setting , General Setting , Home page Setting , Email Setting , Language Manage');
INSERT INTO `admins` VALUES (50, 'bank1', 'bank1@gmail.com', '', NULL, '$2y$10$RS2zxaXOEcwAvnbcnWJm/uOVQwpPYYwSNxC7gvwghvKMD0WZ1bYyW', 1, NULL, '2022-06-19 20:17:56', '2022-06-19 20:18:32', '50', NULL, NULL, NULL, NULL, NULL, 1, 'Sub Institutions management , Manage Customers , Loan Management , DPS Management , FDR Management , Manage Escrow , Bank Transfer , Wire Transfer , Request Money , Withdraw , Deposit , Transactions , Deposits , Currency Setting , General Setting , Home page Setting , Email Setting , Language Manage');
INSERT INTO `admins` VALUES (51, 'bank2', 'bank2@gmail.com', '12231231', 'mkdKchmi1656307846.png', '$2y$10$vobQJGxed0gvgjjUvc4dsOR0KBnjb81H0SFKGRS9rjTMHCbicxkA2', 1, NULL, '2022-06-20 00:13:16', '2022-07-14 15:43:53', '51', NULL, NULL, NULL, 1, NULL, 1, 'Sub Institutions management , Manage Customers , Loan Management , DPS Management , FDR Management , Manage Escrow , Bank Transfer , Wire Transfer , Request Money , Withdraw , Deposit , Transactions , Deposits , Currency Setting , General Setting , Home page Setting , Email Setting , KYC Management , Language Manage');
INSERT INTO `admins` VALUES (75, 'aaaa', 'aaaa@gmail.com', '', NULL, '$2y$10$7QWIpdix.ymy3uUUq5zwYuLyjq.o0vawppYdkI.fYiumUTXP.ofaG', 1, NULL, '2022-07-21 08:18:12', '2022-07-21 08:27:34', '75', NULL, NULL, NULL, NULL, NULL, NULL, 'Sub Institutions management , Manage Customers , Loan Management , DPS Management , FDR Management , Manage Escrow , Bank Transfer , Wire Transfer , Request Money , Withdraw , Deposit , Transactions , Currency Setting , Home page Setting , KYC Management , Language Manage');

-- ----------------------------
-- Table structure for bank_plans
-- ----------------------------
DROP TABLE IF EXISTS `bank_plans`;
CREATE TABLE `bank_plans`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `amount` double NULL DEFAULT NULL,
  `daily_send` double NULL DEFAULT NULL,
  `monthly_send` double NULL DEFAULT NULL,
  `daily_receive` double NULL DEFAULT NULL,
  `monthly_receive` double NULL DEFAULT NULL,
  `daily_withdraw` double NULL DEFAULT NULL,
  `monthly_withdraw` double NULL DEFAULT NULL,
  `loan_amount` double NULL DEFAULT NULL,
  `attribute` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `days` int(11) NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of bank_plans
-- ----------------------------
INSERT INTO `bank_plans` VALUES (1, 'Free', 0, 10000, 10000, 10000, 100000, 100000, 100000, 100000, NULL, 30, '2022-06-07 21:21:12', '2022-06-07 21:21:12');
INSERT INTO `bank_plans` VALUES (13, 'Standard', 50, 1000000, 1000000, 1000000, 1000000, 1000000, 1000000, 1000000, NULL, 30, '2022-06-18 04:55:10', '2022-06-18 08:20:18');
INSERT INTO `bank_plans` VALUES (14, 'Professional', 100, 10000, 10000, 10000, 10000, 10000, 10000, 10000, NULL, 90, '2022-06-18 08:20:57', '2022-06-18 08:20:57');

-- ----------------------------
-- Table structure for blog_categories
-- ----------------------------
DROP TABLE IF EXISTS `blog_categories`;
CREATE TABLE `blog_categories`  (
  `id` int(191) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of blog_categories
-- ----------------------------
INSERT INTO `blog_categories` VALUES (2, 'Tickets', 'tickets');
INSERT INTO `blog_categories` VALUES (3, 'Support', 'support');
INSERT INTO `blog_categories` VALUES (4, 'Transactions', 'transactions');
INSERT INTO `blog_categories` VALUES (5, 'Withdraw', 'withdraw');
INSERT INTO `blog_categories` VALUES (6, 'Deposit', 'deposit');
INSERT INTO `blog_categories` VALUES (7, 'Banking', 'banking');

-- ----------------------------
-- Table structure for blogs
-- ----------------------------
DROP TABLE IF EXISTS `blogs`;
CREATE TABLE `blogs`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` int(191) NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `source` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `meta_tag` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `meta_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `tags` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 31 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of blogs
-- ----------------------------
INSERT INTO `blogs` VALUES (24, 7, 'Four steps for a cloud-ready talent system in banking', 'four-steps-for-a-cloud-ready-talent-system-in-banking', 'With cloud skills of all kinds in hot demand across many industries, banks are having to come to grips with an uncomfortable truth. Hiring, on its own, will not provide them with the skills they need to maximize the value of the cloud. \r\n<br><br><br>\r\nMy previous post looked at the specific must-have skills identified by Accenture analysis for cloud success. In this post, I’ll walk through our four-step framework for growing these skills within an organization. Here’s a high-level visualization: \r\n<br><br><br>\r\nThe second is that the first two steps in the framework focus on the organization as a whole and the market, while steps three and four zoom in on the people within the organization. Alternating between these two frames of analysis is no accident. \r\n<br><br><br>', 'yEVwvno41647249536.jpg', 'www.geniusocean.com', 62, 1, 'dhfghfg', NULL, 'hfhf,sfsg,fsg,qrw', '2019-01-03 07:03:37');
INSERT INTO `blogs` VALUES (29, 4, 'Finance now! Opportunities for embedded lending and leasing', 'finance-now-opportunities-for-embedded-lending-and-leasing', 'When I was shopping online recently, a new option appeared on the checkout screen that presented me the opportunity to finance my purchase. This new offering is becoming pervasive across all asset types, from laptops to lift trucks, and including services such as vacation travel, on B2C, B2B and even P2P shopping sites. \r\n<br><br><br>\r\nThe goal is to make it easier for customers to access financial services as they go about their daily lives. Rather than having to arrange financing or defer purchasing, buyers can finance in the moment and businesses can receive payment quickly. \r\n<br><br><br>\r\nEmbedded specialty financing—lending and leasing—is coming. In fact, I think it’s a trend that’s likely to change the finance landscape within the next five years. So, auto and equipment finance companies need to start thinking now about how they can capitalize on it for the best advantage.', 'cgYNpliD1647249636.jpg', 'genius', 0, 1, NULL, NULL, NULL, '2022-03-14 23:20:36');
INSERT INTO `blogs` VALUES (30, 5, 'What are “cloud skills” for banks, anyway?', 'what-are-cloud-skills-for-banks-anyway', 'Most banks are making significant investments in the cloud and planning to migrate core computing workloads in the coming years. Our research on “Mainframe Migration to the Cloud in Banking”—which we outline in the current issue of the Banking Cloud Altimeter—surveyed 150 banking executives across 16 countries and found that 82% of large banks that are planning to or are in the process of moving at least half of their mainframe workloads to the cloud. Of those aiming to move 75% or more of their workloads to the cloud, most plan to achieve that goal within the next five years.iv> \r\n<br><br><br>\r\nMore Accenture analysis has found that banking is ahead of most industries in its cloud adoption, which includes sourcing and training the skills needed to maximize the cloud’s potential. In terms of cloud adoption, banking leads industries like insurance, automotive and travel while trailing only a few high-tech industries like software.  \r\n<br><br><br>\r\nBut hiring, on its own, is not a viable solution to the talent problem. Demand for both cloud technologists and cloud fluency across the entire organization is rising in banking and beyond. There is not enough external cloud talent to meet the demand.', 'uqb6sKZm1647249780.jpg', 'genius', 0, 1, NULL, NULL, NULL, '2022-03-14 23:23:00');

-- ----------------------------
-- Table structure for contacts
-- ----------------------------
DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `dob` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `personal_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `c_phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `c_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `c_address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `c_city` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `c_zip_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `id_number` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `date_of_issue` datetime(0) NULL DEFAULT NULL,
  `issued_authority` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  `date_of_expire` datetime(0) NULL DEFAULT NULL,
  `c_country` int(11) NULL DEFAULT NULL,
  `contact` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 36 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of contacts
-- ----------------------------
INSERT INTO `contacts` VALUES (31, 51, 'Aleksandar Todorovic', '2022-06-21', '234', '234234', 'aleksandart450@gmail.com', 'Konatice Noname street 378', 'Konatice', '11506', NULL, NULL, NULL, NULL, NULL, NULL, 190, 'Onwer111');
INSERT INTO `contacts` VALUES (32, 10, 'Aleksandar Todorovic', '2022-05-30', '234', '234234', 'aleksandart450@gmail.com', 'Konatice Noname street 378', 'Konatice', '11506', NULL, NULL, NULL, NULL, NULL, NULL, 190, 'Onwer');

-- ----------------------------
-- Table structure for counters
-- ----------------------------
DROP TABLE IF EXISTS `counters`;
CREATE TABLE `counters`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `count` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `is_money` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of counters
-- ----------------------------
INSERT INTO `counters` VALUES (1, 'Total Deposit', 'fas fa-dollar-sign', '890', 1, '2022-02-20 23:56:47', '2022-02-21 00:12:43');
INSERT INTO `counters` VALUES (2, 'Total Withdraw', 'fas fa-money-check-alt', '456', 1, '2022-02-21 00:13:58', '2022-02-21 00:13:58');
INSERT INTO `counters` VALUES (3, 'Total Transactions', 'fas fa-exchange-alt', '96', 0, '2022-02-21 00:15:03', '2022-02-21 00:15:03');
INSERT INTO `counters` VALUES (4, 'Happy Clients', 'fas fa-users', '890', 0, '2022-02-21 00:16:18', '2022-02-21 00:16:18');

-- ----------------------------
-- Table structure for countries
-- ----------------------------
DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_code` int(11) NOT NULL,
  `postcode_required` tinyint(4) NOT NULL DEFAULT 0,
  `is_eu` tinyint(4) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `countries_name_unique`(`name`) USING BTREE,
  UNIQUE INDEX `countries_iso2_unique`(`iso2`) USING BTREE,
  UNIQUE INDEX `countries_iso3_unique`(`iso3`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 250 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of countries
-- ----------------------------
INSERT INTO `countries` VALUES (1, 'Andorra', 'AD', 'AND', 376, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (2, 'United Arab Emirates', 'AE', 'ARE', 971, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (3, 'Afghanistan', 'AF', 'AFG', 93, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (4, 'Antigua and Barbuda', 'AG', 'ATG', 1268, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (5, 'Anguilla', 'AI', 'AIA', 1264, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (6, 'Albania', 'AL', 'ALB', 355, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (7, 'Armenia', 'AM', 'ARM', 374, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (8, 'Angola', 'AO', 'AGO', 244, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (9, 'Antarctica', 'AQ', 'ATA', 672, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (10, 'Argentina', 'AR', 'ARG', 54, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (11, 'American Samoa', 'AS', 'ASM', 1684, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (12, 'Austria', 'AT', 'AUT', 43, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (13, 'Australia', 'AU', 'AUS', 61, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (14, 'Aruba', 'AW', 'ABW', 297, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (15, 'Åland Islands', 'AX', 'ALA', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (16, 'Azerbaijan', 'AZ', 'AZE', 994, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (17, 'Bosnia and Herzegovina', 'BA', 'BIH', 387, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (18, 'Barbados', 'BB', 'BRB', 1246, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (19, 'Bangladesh', 'BD', 'BGD', 880, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (20, 'Belgium', 'BE', 'BEL', 32, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (21, 'Burkina Faso', 'BF', 'BFA', 226, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (22, 'Bulgaria', 'BG', 'BGR', 359, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (23, 'Bahrain', 'BH', 'BHR', 973, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (24, 'Burundi', 'BI', 'BDI', 257, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (25, 'Benin', 'BJ', 'BEN', 229, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (26, 'Saint Barthélemy', 'BL', 'BLM', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (27, 'Bermuda', 'BM', 'BMU', 1441, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (28, 'Brunei Darussalam', 'BN', 'BRN', 673, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (29, 'Bolivia', 'BO', 'BOL', 591, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (30, 'Bonaire, Sint Eustatius and Saba', 'BQ', 'BES', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (31, 'Brazil', 'BR', 'BRA', 55, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (32, 'Bahamas', 'BS', 'BHS', 1242, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (33, 'Bhutan', 'BT', 'BTN', 975, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (34, 'Bouvet Island', 'BV', 'BVT', 44, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (35, 'Botswana', 'BW', 'BWA', 267, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (36, 'Belarus', 'BY', 'BLR', 375, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (37, 'Belize', 'BZ', 'BLZ', 501, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (38, 'Canada', 'CA', 'CAN', 1, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (39, 'Cocos (Keeling) Islands', 'CC', 'CCK', 61, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (40, 'Congo (Democratic Republic of the)', 'CD', 'COD', 243, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (41, 'Central African Republic', 'CF', 'CAF', 236, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (42, 'Congo', 'CG', 'COG', 242, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (43, 'Switzerland', 'CH', 'CHE', 41, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (44, 'Ivory Coast', 'CI', 'CIV', 225, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (45, 'Cook Islands', 'CK', 'COK', 682, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (46, 'Chile', 'CL', 'CHL', 56, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (47, 'Cameroon', 'CM', 'CMR', 237, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (48, 'China', 'CN', 'CHN', 86, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (49, 'Colombia', 'CO', 'COL', 57, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (50, 'Costa Rica', 'CR', 'CRI', 506, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (51, 'Cuba', 'CU', 'CUB', 53, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (52, 'Cape Verde', 'CV', 'CPV', 238, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (53, 'Curaçao', 'CW', 'CUW', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (54, 'Christmas Island', 'CX', 'CXR', 61, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (55, 'Cyprus', 'CY', 'CYP', 357, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (56, 'Czech Republic', 'CZ', 'CZE', 420, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (57, 'Germany', 'DE', 'DEU', 49, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (58, 'Djibouti', 'DJ', 'DJI', 253, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (59, 'Denmark', 'DK', 'DNK', 45, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (60, 'Dominica', 'DM', 'DMA', 1767, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (61, 'Dominican Republic', 'DO', 'DOM', 1809, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (62, 'Algeria', 'DZ', 'DZA', 213, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (63, 'Ecuador', 'EC', 'ECU', 593, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (64, 'Estonia', 'EE', 'EST', 372, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (65, 'Egypt', 'EG', 'EGY', 20, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (66, 'Western Sahara', 'EH', 'ESH', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (67, 'Eritrea', 'ER', 'ERI', 291, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (68, 'Spain', 'ES', 'ESP', 34, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (69, 'Ethiopia', 'ET', 'ETH', 251, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (70, 'Finland', 'FI', 'FIN', 358, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (71, 'Fiji', 'FJ', 'FJI', 679, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (72, 'Falkland Islands (Malvinas)', 'FK', 'FLK', 500, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (73, 'Micronesia (Federated States of)', 'FM', 'FSM', 691, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (74, 'Faroe Islands', 'FO', 'FRO', 298, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (75, 'France', 'FR', 'FRA', 33, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (76, 'Gabon', 'GA', 'GAB', 241, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (77, 'United Kingdom', 'GB', 'GBR', 44, 1, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (78, 'Grenada', 'GD', 'GRD', 1473, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (79, 'Georgia', 'GE', 'GEO', 995, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (80, 'French Guiana', 'GF', 'GUF', 594, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (81, 'Guernsey', 'GG', 'GGY', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (82, 'Ghana', 'GH', 'GHA', 233, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (83, 'Gibraltar', 'GI', 'GIB', 350, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (84, 'Greenland', 'GL', 'GRL', 299, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (85, 'Gambia', 'GM', 'GMB', 220, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (86, 'Guinea', 'GN', 'GIN', 224, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (87, 'Guadeloupe', 'GP', 'GLP', 590, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (88, 'Equatorial Guinea', 'GQ', 'GNQ', 240, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (89, 'Greece', 'GR', 'GRC', 30, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (90, 'South Georgia and the South Sandwich Islands', 'GS', 'SGS', 44, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (91, 'Guatemala', 'GT', 'GTM', 502, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (92, 'Guam', 'GU', 'GUM', 1671, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (93, 'Guinea-Bissau', 'GW', 'GNB', 245, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (94, 'Guyana', 'GY', 'GUY', 592, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (95, 'Hong Kong', 'HK', 'HKG', 852, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (96, 'Heard Island and McDonald Islands', 'HM', 'HMD', 44, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (97, 'Honduras', 'HN', 'HND', 504, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (98, 'Croatia (Hrvatska)', 'HR', 'HRV', 385, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (99, 'Haiti', 'HT', 'HTI', 509, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (100, 'Hungary', 'HU', 'HUN', 36, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (101, 'Indonesia', 'ID', 'IDN', 62, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (102, 'Ireland', 'IE', 'IRL', 353, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (103, 'Israel', 'IL', 'ISR', 972, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (104, 'Isle of Man', 'IM', 'IMN', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (105, 'India', 'IN', 'IND', 91, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (106, 'British Indian Ocean Territory', 'IO', 'IOT', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (107, 'Iraq', 'IQ', 'IRQ', 964, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (108, 'Iran (Islamic Republic of)', 'IR', 'IRN', 98, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (109, 'Iceland', 'IS', 'ISL', 354, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (110, 'Italy', 'IT', 'ITA', 39, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (111, 'Jersey', 'JE', 'JEY', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (112, 'Jamaica', 'JM', 'JAM', 1876, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (113, 'Jordan', 'JO', 'JOR', 962, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (114, 'Japan', 'JP', 'JPN', 81, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (115, 'Kenya', 'KE', 'KEN', 254, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (116, 'Kyrgyzstan', 'KG', 'KGZ', 996, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (117, 'Cambodia', 'KH', 'KHM', 855, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (118, 'Kiribati', 'KI', 'KIR', 686, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (119, 'Comoros', 'KM', 'COM', 269, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (120, 'Saint Kitts and Nevis', 'KN', 'KNA', 1869, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (121, 'Korea (Democratic People\'s Republic of)', 'KP', 'PRK', 850, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (122, 'Korea (Republic of)', 'KR', 'KOR', 82, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (123, 'Kuwait', 'KW', 'KWT', 965, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (124, 'Cayman Islands', 'KY', 'CYM', 1345, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (125, 'Kazakhstan', 'KZ', 'KAZ', 7, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (126, 'Lao People\'s Democratic Republic', 'LA', 'LAO', 856, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (127, 'Lebanon', 'LB', 'LBN', 961, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (128, 'Saint Lucia', 'LC', 'LCA', 1758, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (129, 'Liechtenstein', 'LI', 'LIE', 423, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (130, 'Sri Lanka', 'LK', 'LKA', 94, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (131, 'Liberia', 'LR', 'LBR', 231, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (132, 'Lesotho', 'LS', 'LSO', 266, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (133, 'Lithuania', 'LT', 'LTU', 370, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (134, 'Luxembourg', 'LU', 'LUX', 352, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (135, 'Latvia', 'LV', 'LVA', 371, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (136, 'Libya', 'LY', 'LBY', 218, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (137, 'Morocco', 'MA', 'MAR', 212, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (138, 'Monaco', 'MC', 'MCO', 377, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (139, 'Moldova (Republic of)', 'MD', 'MDA', 373, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (140, 'Montenegro', 'ME', 'MNE', 382, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (141, 'Saint Martin (French part)', 'MF', 'MAF', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (142, 'Madagascar', 'MG', 'MDG', 261, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (143, 'Marshall Islands', 'MH', 'MHL', 692, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (144, 'Macedonia', 'MK', 'MKD', 389, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (145, 'Mali', 'ML', 'MLI', 223, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (146, 'Myanmar', 'MM', 'MMR', 95, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (147, 'Mongolia', 'MN', 'MNG', 976, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (148, 'Macau', 'MO', 'MAC', 853, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (149, 'Northern Mariana Islands', 'MP', 'MNP', 1670, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (150, 'Martinique', 'MQ', 'MTQ', 596, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (151, 'Mauritania', 'MR', 'MRT', 222, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (152, 'Montserrat', 'MS', 'MSR', 1664, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (153, 'Malta', 'MT', 'MLT', 356, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (154, 'Mauritius', 'MU', 'MUS', 230, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (155, 'Maldives', 'MV', 'MDV', 960, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (156, 'Malawi', 'MW', 'MWI', 265, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (157, 'Mexico', 'MX', 'MEX', 52, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (158, 'Malaysia', 'MY', 'MYS', 60, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (159, 'Mozambique', 'MZ', 'MOZ', 258, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (160, 'Namibia', 'NA', 'NAM', 264, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (161, 'New Caledonia', 'NC', 'NCL', 687, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (162, 'Niger', 'NE', 'NER', 227, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (163, 'Norfolk Island', 'NF', 'NFK', 672, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (164, 'Nigeria', 'NG', 'NGA', 234, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (165, 'Nicaragua', 'NI', 'NIC', 505, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (166, 'Netherlands', 'NL', 'NLD', 31, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (167, 'Norway', 'NO', 'NOR', 47, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (168, 'Nepal', 'NP', 'NPL', 977, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (169, 'Nauru', 'NR', 'NRU', 674, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (170, 'Niue', 'NU', 'NIU', 683, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (171, 'New Zealand', 'NZ', 'NZL', 64, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (172, 'Oman', 'OM', 'OMN', 968, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (173, 'Panama', 'PA', 'PAN', 507, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (174, 'Peru', 'PE', 'PER', 51, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (175, 'French Polynesia', 'PF', 'PYF', 689, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (176, 'Papua New Guinea', 'PG', 'PNG', 675, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (177, 'Philippines', 'PH', 'PHL', 63, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (178, 'Pakistan', 'PK', 'PAK', 92, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (179, 'Poland', 'PL', 'POL', 48, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (180, 'Saint Pierre and Miquelon', 'PM', 'SPM', 508, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (181, 'Pitcairn', 'PN', 'PCN', 870, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (182, 'Puerto Rico', 'PR', 'PRI', 1, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (183, 'Palestine, State of', 'PS', 'PSE', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (184, 'Portugal', 'PT', 'PRT', 351, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (185, 'Palau', 'PW', 'PLW', 680, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (186, 'Paraguay', 'PY', 'PRY', 595, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (187, 'Qatar', 'QA', 'QAT', 974, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (188, 'Reunion', 'RE', 'REU', 262, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (189, 'Romania', 'RO', 'ROU', 40, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (190, 'Serbia', 'RS', 'SRB', 381, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (191, 'Russian Federation', 'RU', 'RUS', 7, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (192, 'Rwanda', 'RW', 'RWA', 250, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (193, 'Saudi Arabia', 'SA', 'SAU', 966, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (194, 'Solomon Islands', 'SB', 'SLB', 677, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (195, 'Seychelles', 'SC', 'SYC', 248, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (196, 'Sudan', 'SD', 'SDN', 249, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (197, 'Sweden', 'SE', 'SWE', 46, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (198, 'Singapore', 'SG', 'SGP', 65, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (199, 'Saint Helena, Ascension and Tristan da Cunha', 'SH', 'SHN', 290, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (200, 'Slovenia', 'SI', 'SVN', 386, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (201, 'Svalbard and Jan Mayen', 'SJ', 'SJM', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (202, 'Slovakia', 'SK', 'SVK', 421, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (203, 'Sierra Leone', 'SL', 'SLE', 232, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (204, 'San Marino', 'SM', 'SMR', 378, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (205, 'Senegal', 'SN', 'SEN', 221, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (206, 'Somalia', 'SO', 'SOM', 252, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (207, 'Suriname', 'SR', 'SUR', 597, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (208, 'South Sudan', 'SS', 'SSD', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (209, 'Sao Tome and Principe', 'ST', 'STP', 239, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (210, 'El Salvador', 'SV', 'SLV', 503, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (211, 'Sint Maarten (Dutch part)', 'SX', 'SXM', 0, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (212, 'Syrian Arab Republic', 'SY', 'SYR', 963, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (213, 'Swaziland', 'SZ', 'SWZ', 268, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (214, 'Turks and Caicos Islands', 'TC', 'TCA', 1649, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (215, 'Chad', 'TD', 'TCD', 235, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (216, 'French Southern Territories', 'TF', 'ATF', 44, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (217, 'Togo', 'TG', 'TGO', 228, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (218, 'Thailand', 'TH', 'THA', 66, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (219, 'Tajikistan', 'TJ', 'TJK', 992, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (220, 'Tokelau', 'TK', 'TKL', 690, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (221, 'Timor-Leste', 'TL', 'TLS', 670, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (222, 'Turkmenistan', 'TM', 'TKM', 993, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (223, 'Tunisia', 'TN', 'TUN', 216, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (224, 'Tonga', 'TO', 'TON', 676, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (225, 'Turkey', 'TR', 'TUR', 90, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (226, 'Trinidad and Tobago', 'TT', 'TTO', 1868, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (227, 'Tuvalu', 'TV', 'TUV', 688, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (228, 'Taiwan', 'TW', 'TWN', 886, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (229, 'Tanzania, United Republic of', 'TZ', 'TZA', 255, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (230, 'Ukraine', 'UA', 'UKR', 380, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (231, 'Uganda', 'UG', 'UGA', 256, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (232, 'United States Minor Outlying Islands', 'UM', 'UMI', 44, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (233, 'United States of America', 'US', 'USA', 1, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (234, 'Uruguay', 'UY', 'URY', 598, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (235, 'Uzbekistan', 'UZ', 'UZB', 998, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (236, 'Vatican City State', 'VA', 'VAT', 39, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (237, 'Saint Vincent and the Grenadines', 'VC', 'VCT', 1784, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (238, 'Venezuela', 'VE', 'VEN', 58, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (239, 'Virgin Islands (British)', 'VG', 'VGB', 1284, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (240, 'Virgin Islands (U.S.)', 'VI', 'VIR', 1340, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (241, 'Viet Nam', 'VN', 'VNM', 84, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (242, 'Vanuatu', 'VU', 'VUT', 678, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (243, 'Wallis and Futuna', 'WF', 'WLF', 681, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (244, 'Samoa', 'WS', 'WSM', 685, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (245, 'Yemen', 'YE', 'YEM', 967, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (246, 'Mayotte', 'YT', 'MYT', 262, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (247, 'South Africa', 'ZA', 'ZAF', 27, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (248, 'Zambia', 'ZM', 'ZMB', 260, 0, 0, 0, NULL, NULL);
INSERT INTO `countries` VALUES (249, 'Zimbabwe', 'ZW', 'ZWE', 263, 0, 0, 0, NULL, NULL);

-- ----------------------------
-- Table structure for currencies
-- ----------------------------
DROP TABLE IF EXISTS `currencies`;
CREATE TABLE `currencies`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `is_default` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 => default, 0 => not default',
  `symbol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `curr_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 => fiat, 2 => crypto',
  `status` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 => active, 0 => inactive',
  `rate` decimal(20, 10) UNSIGNED NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `currencies_symbol_unique`(`symbol`) USING BTREE,
  UNIQUE INDEX `currencies_code_unique`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of currencies
-- ----------------------------
INSERT INTO `currencies` VALUES (1, 1, '$', 'USD', 'United State Dollar', 1, 1, 1.0000000000, '2021-12-20 05:12:58', '2022-02-16 04:02:37');
INSERT INTO `currencies` VALUES (4, 0, '€', 'EUR', 'European Currency', 1, 1, 0.8790350000, '2021-12-20 05:12:58', '2022-02-16 04:02:35');
INSERT INTO `currencies` VALUES (5, 0, '£', 'GBP', 'Greate British Pound', 1, 1, 0.7376150000, '2021-12-21 01:45:51', '2022-02-16 04:02:35');
INSERT INTO `currencies` VALUES (6, 0, '৳', 'BDT', 'Bangladeshi Taka', 1, 1, 85.9261900000, '2021-12-21 01:48:53', '2022-02-16 04:02:35');
INSERT INTO `currencies` VALUES (9, 0, '₿', 'BTC', 'Bitcoin', 2, 1, 0.0000225900, '2021-12-21 01:48:53', '2022-02-16 04:02:36');
INSERT INTO `currencies` VALUES (10, 0, '₹', 'INR', 'Indian Rupee', 1, 1, 75.0096000000, '2022-01-26 03:28:23', '2022-02-16 04:02:35');
INSERT INTO `currencies` VALUES (11, 0, '¥', 'JPY', 'Japanese Yen', 1, 1, 115.6425010000, '2022-01-26 03:30:04', '2022-02-16 04:02:35');
INSERT INTO `currencies` VALUES (13, 0, '₦', 'NGN', 'Nigerian naira', 1, 1, 1.0000000000, '2022-02-06 06:41:35', '2022-02-16 04:02:35');
INSERT INTO `currencies` VALUES (17, 0, 'Ð', 'ETH', 'Ethereum', 2, 1, 0.0005300000, NULL, NULL);

-- ----------------------------
-- Table structure for disputes
-- ----------------------------
DROP TABLE IF EXISTS `disputes`;
CREATE TABLE `disputes`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `escrow_id` int(11) NOT NULL,
  `user_id` int(11) NULL DEFAULT NULL,
  `admin_id` int(11) NULL DEFAULT NULL,
  `message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for documents
-- ----------------------------
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ins_id` int(11) NULL DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `file` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 55 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of documents
-- ----------------------------
INSERT INTO `documents` VALUES (52, 51, 'Institution Introduction', '81.jpg');

-- ----------------------------
-- Table structure for domains
-- ----------------------------
DROP TABLE IF EXISTS `domains`;
CREATE TABLE `domains`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `domains_domain_unique`(`domain`) USING BTREE,
  INDEX `domains_tenant_id_foreign`(`tenant_id`) USING BTREE,
  CONSTRAINT `domains_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of domains
-- ----------------------------
INSERT INTO `domains` VALUES (9, 'bank1', '50', '2022-06-19 20:18:32', '2022-06-19 20:18:32');
INSERT INTO `domains` VALUES (10, 'bank2', '51', '2022-06-20 00:13:52', '2022-06-20 00:13:52');
INSERT INTO `domains` VALUES (11, 'bank3', '54', '2022-06-20 00:53:19', '2022-06-20 00:53:19');
INSERT INTO `domains` VALUES (12, 'bbbbb', '69', '2022-06-24 22:48:31', '2022-06-24 22:48:31');
INSERT INTO `domains` VALUES (13, 'ccc', '71', '2022-06-25 08:42:47', '2022-06-25 08:42:47');
INSERT INTO `domains` VALUES (14, 'smiss', '74', '2022-07-21 08:12:01', '2022-07-21 08:12:01');
INSERT INTO `domains` VALUES (15, 'aaaa', '75', '2022-07-21 08:20:10', '2022-07-21 08:20:10');

-- ----------------------------
-- Table structure for dps_plans
-- ----------------------------
DROP TABLE IF EXISTS `dps_plans`;
CREATE TABLE `dps_plans`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `per_installment` decimal(10, 0) NULL DEFAULT NULL,
  `installment_interval` int(11) NULL DEFAULT NULL,
  `total_installment` int(11) NOT NULL,
  `interest_rate` decimal(10, 0) NOT NULL,
  `final_amount` decimal(10, 0) NOT NULL,
  `user_profit` decimal(10, 0) NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dps_plans
-- ----------------------------
INSERT INTO `dps_plans` VALUES (3, 'Standard', 9000, 30, 20, 9, 180000, 16200, 1, '2022-01-11 01:46:41', '2022-03-14 22:57:03');
INSERT INTO `dps_plans` VALUES (4, 'Basic', 80, 30, 20, 20, 1600, 320, 1, '2022-01-25 05:27:06', '2022-06-10 14:30:37');

-- ----------------------------
-- Table structure for email_templates
-- ----------------------------
DROP TABLE IF EXISTS `email_templates`;
CREATE TABLE `email_templates`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `email_subject` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
  `email_body` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of email_templates
-- ----------------------------
INSERT INTO `email_templates` VALUES (3, 'Withdraw', 'Your withdraw is completed successfully.', '<p>Hello {customer_name},<br>Your withdraw is completed successfully.</p><p>Thank You<br></p>', 1);
INSERT INTO `email_templates` VALUES (4, 'Deposit', 'You have invested successfully.', '<p>Hello {customer_name},<br>You have deposited successfully.</p><p>Transaction ID:&nbsp;<span style=\"color: rgb(33, 37, 41);\">{order_number}.</span></p><p>Thank You.</p>', 1);
INSERT INTO `email_templates` VALUES (5, 'send money', 'Your send money is completed successfully.', '<p>Hello {customer_name},<br>Your send money is completed successfully.</p><p>Thank You<br></p>', 1);
INSERT INTO `email_templates` VALUES (6, 'request money', 'Your request money is completed successfully.', '<p>Hello {customer_name},<br>Your request money is completed successfully.</p><p>Thank You<br></p>', 1);

-- ----------------------------
-- Table structure for faqs
-- ----------------------------
DROP TABLE IF EXISTS `faqs`;
CREATE TABLE `faqs`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of faqs
-- ----------------------------
INSERT INTO `faqs` VALUES (1, 'Right my front it wound cause fully', 'Aut, expedita optio? Quis ab laudantium, facilis similique est alias, possimus expedita dolorum fugit mollitia, optio quo?\r\n \r\nFacilis similique est alias, possimus expedita dolorum fugit mollitia, optio quo? Dignissimos beatae officia repellat maiores!', 1);
INSERT INTO `faqs` VALUES (3, 'Man particular insensible celebrated', 'Aut, expedita optio? Quis ab laudantium, facilis similique est alias, possimus expedita dolorum fugit mollitia, optio quo?\r\n \r\nFacilis similique est alias, possimus expedita dolorum fugit mollitia, optio quo? Dignissimos beatae officia repellat maiores!', 1);
INSERT INTO `faqs` VALUES (4, 'Will I be charged a fee for receiving the OTP via SMS?', 'No. The OTP service is free of charge. The Bank shall notify customers if any charge is imposed in future for this service. You can find the latest tariff guide for services on the Genius Bank Bangladesh website.', 0);
INSERT INTO `faqs` VALUES (5, 'Can I choose not to use the OTP service for online purchases?', 'No. Bangladesh Bank regulation has mandated the use of OTP for every online purchase.', 0);
INSERT INTO `faqs` VALUES (6, 'Why is there a need for an One-Time-Password (OTP) to complete an online purchase?', 'An OTP helps to protect against online fraud. It is a secure way to authenticate whether the customer who is making an online purchase is the rightful owner of the debit card being used.', 0);

-- ----------------------------
-- Table structure for fdr_plans
-- ----------------------------
DROP TABLE IF EXISTS `fdr_plans`;
CREATE TABLE `fdr_plans`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `min_amount` decimal(10, 0) NULL DEFAULT NULL,
  `max_amount` decimal(10, 0) NULL DEFAULT NULL,
  `interest_interval` int(11) NULL DEFAULT NULL,
  `interval_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `interest_rate` decimal(10, 0) NULL DEFAULT NULL,
  `matured_days` int(11) NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of fdr_plans
-- ----------------------------
INSERT INTO `fdr_plans` VALUES (2, 'Basic', 20, 200, NULL, 'fixed', 3, 30, 1, '2022-01-12 06:05:44', '2022-03-14 22:57:33');
INSERT INTO `fdr_plans` VALUES (3, 'Standard', 50, 100, 30, 'partial', 4, 365, 1, '2022-01-12 06:54:25', '2022-03-14 22:57:47');

-- ----------------------------
-- Table structure for features
-- ----------------------------
DROP TABLE IF EXISTS `features`;
CREATE TABLE `features`  (
  `id` int(191) NOT NULL AUTO_INCREMENT,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of features
-- ----------------------------
INSERT INTO `features` VALUES (8, 'Personal  Loans', 'Genius Bank offers a wide range of loans  to suit your needs.', '1639476553add-bitcoins.png');
INSERT INTO `features` VALUES (9, 'SME Banking', 'For availing SME loan it is required to mortgage collateral/security.', '1639476522buy-sell-bitcoins.png');
INSERT INTO `features` VALUES (10, 'Corporate Banking', 'Get it on PC or Mobile to create, send and receive bitcoins.', '1639476579download-bitcoin.png');
INSERT INTO `features` VALUES (11, 'Personal Banking', 'Customer can enjoy the convenience of banking service.', '1647235824mobile-app.png');

-- ----------------------------
-- Table structure for fonts
-- ----------------------------
DROP TABLE IF EXISTS `fonts`;
CREATE TABLE `fonts`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `font_family` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `font_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_default` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of fonts
-- ----------------------------
INSERT INTO `fonts` VALUES (1, 'Rubik', 'Rubik', 0, '2021-09-08 00:34:28', '2022-03-03 10:24:36');
INSERT INTO `fonts` VALUES (2, 'Roboto', 'Roboto', 0, '2021-09-08 00:35:10', '2022-03-03 10:24:36');
INSERT INTO `fonts` VALUES (3, 'New Tegomin', 'New+Tegomin', 0, '2021-09-08 00:35:23', '2022-03-03 10:24:36');
INSERT INTO `fonts` VALUES (5, 'Open Sans', 'Open+Sans', 0, '2021-09-08 00:44:49', '2022-03-03 10:24:36');
INSERT INTO `fonts` VALUES (11, 'Manrope', 'Manrope', 1, '2022-03-03 10:24:26', '2022-03-03 10:24:36');

-- ----------------------------
-- Table structure for generalsettings
-- ----------------------------
DROP TABLE IF EXISTS `generalsettings`;
CREATE TABLE `generalsettings`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `favicon` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `loader` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_loader` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `banner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_no_prefix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `header_email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `header_phone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `footer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `copyright` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `colors` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_talkto` tinyint(1) NOT NULL DEFAULT 1,
  `talkto` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `is_language` tinyint(1) NOT NULL DEFAULT 1,
  `is_loader` tinyint(1) NOT NULL DEFAULT 1,
  `map_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `is_disqus` tinyint(1) NOT NULL DEFAULT 0,
  `disqus` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `is_contact` tinyint(1) NOT NULL DEFAULT 0,
  `is_faq` tinyint(1) NOT NULL DEFAULT 0,
  `withdraw_status` tinyint(4) NOT NULL DEFAULT 0,
  `smtp_host` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_port` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_encryption` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `smtp_user` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_pass` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_smtp` tinyint(1) NOT NULL DEFAULT 0,
  `coupon_found` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `already_coupon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `order_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `service_subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `service_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `service_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `service_image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `order_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `is_currency` tinyint(1) NOT NULL DEFAULT 0,
  `currency_format` tinyint(1) NOT NULL DEFAULT 0,
  `price_bigtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `price_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribe_success` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `subscribe_error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `error_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `error_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `error_photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `breadcumb_banner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_admin_loader` tinyint(1) NOT NULL DEFAULT 0,
  `currency_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `currency_sign` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_verification_email` tinyint(1) NOT NULL DEFAULT 0,
  `withdraw_fee` double NOT NULL DEFAULT 0,
  `withdraw_charge` double NOT NULL DEFAULT 0,
  `is_affilate` tinyint(1) NOT NULL DEFAULT 1,
  `affilate_charge` double NOT NULL DEFAULT 0,
  `affilate_banner` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `secret_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `gap_limit` int(11) NOT NULL DEFAULT 300,
  `isWallet` tinyint(4) NOT NULL DEFAULT 0,
  `affilate_new_user` int(11) NOT NULL DEFAULT 0,
  `affilate_user` int(11) NOT NULL DEFAULT 0,
  `footer_logo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `pm_account` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_pm` tinyint(4) NULL DEFAULT 0,
  `cc_api_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `balance_transfer` tinyint(4) NOT NULL DEFAULT 0,
  `twilio_account_sid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `twilio_auth_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `twilio_default_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `twilio_status` tinyint(4) NOT NULL DEFAULT 0,
  `nexmo_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nexmo_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nexmo_default_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nexmo_status` tinyint(4) NOT NULL DEFAULT 0,
  `two_factor` tinyint(4) NOT NULL DEFAULT 0,
  `kyc` tinyint(4) NOT NULL DEFAULT 0,
  `menu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `transfer_fixed` double NULL DEFAULT NULL,
  `transfer_percentage` double NULL DEFAULT NULL,
  `transfer_min` double NULL DEFAULT NULL,
  `transfer_max` double NULL DEFAULT NULL,
  `fixed_request_charge` double NULL DEFAULT NULL,
  `percentage_request_charge` double NULL DEFAULT NULL,
  `minimum_request_money` double NULL DEFAULT NULL,
  `maximum_request_money` double NULL DEFAULT NULL,
  `module_section` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `user_module` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `is_verify` tinyint(4) NULL DEFAULT 0,
  `two_fa` tinyint(1) NOT NULL DEFAULT 1,
  `wallet_no_prefix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of generalsettings
-- ----------------------------
INSERT INTO `generalsettings` VALUES (1, 'gdV5JbBZ1652956530.png', '16393007481563335660service-icon-1.png', '5monWltX1641808745.gif', '33CiUFaI1641808748.gif', '1563350277herobg.jpg', 'MT Payment System - All in One Banking System', 'MT', 'Info@example.com', '0123 456789', 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae', '<p>COPYRIGHT © 2022. All Rights Reserved By <a href=\"https://monezium.net/\" target=\"_blank\">monezium.net</a></p>', '#0ba026', 0, '<script type=\"text/javascript\">\r\nvar Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();\r\n(function(){\r\nvar s1=document.createElement(\"script\"),s0=document.getElementsByTagName(\"script\")[0];\r\ns1.async=true;\r\ns1.src=\'https://embed.tawk.to/5bc2019c61d0b77092512d03/default\';\r\ns1.charset=\'UTF-8\';\r\ns1.setAttribute(\'crossorigin\',\'*\');\r\ns0.parentNode.insertBefore(s1,s0);\r\n})();\r\n</script>', 1, 1, 'AIzaSyB1GpE4qeoJ__70UZxvX9CTMUTZRZNHcu8', 1, 'MoneziumNET', 1, 1, 1, 'smtp.gmail.com', '465', 'tls', 'ahmmedafzal4@gmail.com', 'ohzgxzxyaebarzop', 'ahmmedafzal4@gmail.com', 'GeniusOcean', 1, 'Coupon Found', 'Coupon Already Applied', 'THANK YOU FOR YOUR INVEST.', '<h5 class=\"sub-title\">A litter bit More&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</h5>', '<h2 class=\"title extra-padding\">About US&nbsp;</h2>', '<p>Our organization pursues several goals that can be \r\n											identified as our mission. Learn more about them below.\r\n											Auis nostrud exercitation ullamc laboris nisitm aliquip ex \r\nbea sed consequat duis autes ure dolor. dolore magna aliqua nim ad \r\nminim.</p>\r\n									<p>\r\n											Auis nostrud exercitation ullamc laboris nisitm aliquip ex \r\nbea sed consequat duis autes ure dolor. dolore magna aliqua nim ad \r\nminim.&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</p>', '1563350729about.png', 'We\'ll email you an order confirmation with details and tracking info.', 1, 1, 'PRICING', 'Choose Plans & Pricing', 'Choose the best for yourself', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'You are subscribed Successfully.', 'This email has already been taken.', 'OOPS ! ... PAGE NOT FOUND', 'THE PAGE YOU ARE LOOKING FOR MIGHT HAVE BEEN REMOVED, HAD ITS NAME CHANGED, OR IS TEMPORARILY UNAVAILABLE.', '16392899281561878540404.png', 'b4GEKQxa1654847903.jpg', 1, 'USD', '$', 0, 5, 5, 1, 5, '16406712051566471347add.jpg', 'ZzsMLGKe162CfA5EcG6j', 3000, 1, 4, 10, '1EGZG7py1652956537.png', 'U36807958', 1, 'cdb2163c-91cc-4fa6-b3fc-7de11bdcdf1a', 1, 'ACb87cec0c7d04b80d78bf1647edf8f67f', 'ee60fb893d6e7a2db56e5748e5eab8a3', '01976814812', 1, 'ba9111b8', 'cgxbAg4KnE80bcKx', '01976814812', 1, 0, 0, '{\"Home\":{\"title\":\"Home\",\"dropdown\":\"no\",\"href\":\"\\/\",\"target\":\"self\"},\"Pricing\":{\"title\":\"Pricing\",\"dropdown\":\"no\",\"href\":\"\\/#pricing\",\"target\":\"self\"},\"Services\":{\"title\":\"Services\",\"dropdown\":\"no\",\"href\":\"\\/services\",\"target\":\"self\"},\"About\":{\"title\":\"About\",\"dropdown\":\"no\",\"href\":\"\\/about\",\"target\":\"self\"},\"Blog\":{\"title\":\"Blog\",\"dropdown\":\"no\",\"href\":\"\\/blogs\",\"target\":\"self\"}}', 1, 0.8, 10, 1000, 1, 0.3, 1000, 5000, '', '', 1, 1, 'MONE123');

-- ----------------------------
-- Table structure for kyc_forms
-- ----------------------------
DROP TABLE IF EXISTS `kyc_forms`;
CREATE TABLE `kyc_forms`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` tinyint(4) NULL DEFAULT NULL,
  `type` int(11) NULL DEFAULT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `required` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of kyc_forms
-- ----------------------------
INSERT INTO `kyc_forms` VALUES (9, 1, 1, 'Full Name', 'full_name', 1, '2022-03-06 07:08:28', '2022-03-06 07:08:28');
INSERT INTO `kyc_forms` VALUES (10, 1, 2, 'NID', 'nid', 1, '2022-03-06 07:08:38', '2022-03-06 07:08:38');
INSERT INTO `kyc_forms` VALUES (11, 1, 3, 'Present Address', 'present_address', 1, '2022-03-06 07:08:51', '2022-03-06 07:08:51');
INSERT INTO `kyc_forms` VALUES (12, 1, 3, 'Parmanent Address', 'parmanent_address', 1, '2022-03-06 07:09:04', '2022-03-06 07:09:04');

-- ----------------------------
-- Table structure for languages
-- ----------------------------
DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rtl` tinyint(4) NOT NULL DEFAULT 0,
  `is_default` tinyint(4) NOT NULL DEFAULT 0,
  `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of languages
-- ----------------------------
INSERT INTO `languages` VALUES (1, 1, 1, 'EN', '1636017050KyjRNauw', '1636017050KyjRNauw.json', NULL, NULL);

-- ----------------------------
-- Table structure for loan_plans
-- ----------------------------
DROP TABLE IF EXISTS `loan_plans`;
CREATE TABLE `loan_plans`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `min_amount` decimal(10, 0) NULL DEFAULT NULL,
  `max_amount` decimal(10, 0) NULL DEFAULT NULL,
  `per_installment` decimal(10, 0) NULL DEFAULT NULL,
  `installment_interval` int(11) NULL DEFAULT NULL,
  `total_installment` int(11) NULL DEFAULT NULL,
  `instruction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `required_information` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of loan_plans
-- ----------------------------
INSERT INTO `loan_plans` VALUES (4, 'Agriculture Loan', 5000, 50000, 2, 30, 60, NULL, '{\"1\":{\"field_name\":\"NID\",\"type\":\"file\",\"validation\":\"required\"},\"2\":{\"field_name\":\"Father\'s Name\",\"type\":\"text\",\"validation\":\"required\"},\"3\":{\"field_name\":\"Details Information\",\"type\":\"textarea\",\"validation\":\"nullable\"}}', 1, '2022-01-12 22:35:12', '2022-03-14 22:51:54');
INSERT INTO `loan_plans` VALUES (5, 'Education Loan', 5000, 50000, 2, 30, 55, NULL, NULL, 1, '2022-01-23 03:36:10', '2022-03-14 22:52:10');
INSERT INTO `loan_plans` VALUES (6, 'House Loan', 3000, 100000, 10, 30, 15, NULL, NULL, 1, '2022-01-23 03:37:55', '2022-03-14 22:52:27');

-- ----------------------------
-- Table structure for members
-- ----------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members`  (
  `id` int(191) NOT NULL AUTO_INCREMENT,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `photo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `facebook` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `twitter` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `linkedin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of members
-- ----------------------------
INSERT INTO `members` VALUES (2, 'Ervin Kim', 'CEO of Apple', '1561539258comment2.png', 'https://www.facebook.com', 'https://www.twitter.com', 'https://www.linkedin.com');
INSERT INTO `members` VALUES (3, 'Ervin Kim', 'CEO of Apple', '1561539242comment2.png', 'https://www.facebook.com', 'https://www.twitter.com', 'https://www.linkedin.com');
INSERT INTO `members` VALUES (4, 'Ervin Kim', 'CEO of Apple', '1561539231comment2.png', 'https://www.facebook.com', 'https://www.twitter.com', 'https://www.linkedin.com');
INSERT INTO `members` VALUES (5, 'Ervin Kim', 'CEO of Apple', '1561539222comment2.png', NULL, 'https://www.twitter.com', 'https://www.linkedin.com');
INSERT INTO `members` VALUES (6, 'Ervin Kim', 'CEO of Apple', '1561539213comment2.png', NULL, 'https://www.twitter.com', 'https://www.linkedin.com');
INSERT INTO `members` VALUES (7, 'Ervin Kim', 'CEO of Apple', '1561539184comment2.png', 'https://www.facebook.com', NULL, 'https://www.linkedin.com');
INSERT INTO `members` VALUES (8, 'Ervin Kim', 'CEO of Apple', '1561539197comment2.png', 'https://www.facebook.com', 'https://www.twitter.com', 'https://www.linkedin.com');
INSERT INTO `members` VALUES (9, 'Ervin Kim', 'CEO of Apple', '1561539345comment2.png', 'https://www.facebook.com', 'https://www.twitter.com', NULL);

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 153 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '2021_12_21_095225_create_transactions_table', 1);
INSERT INTO `migrations` VALUES (2, '2022_06_02_063601_add_tenant_id_to_admins_table', 2);
INSERT INTO `migrations` VALUES (3, '2022_06_02_063437_add_tenant_id_to_users_table', 3);
INSERT INTO `migrations` VALUES (4, '2022_06_03_141405_create_account_processes_table', 0);
INSERT INTO `migrations` VALUES (5, '2022_06_03_141405_create_admin_languages_table', 0);
INSERT INTO `migrations` VALUES (6, '2022_06_03_141405_create_admin_user_conversations_table', 0);
INSERT INTO `migrations` VALUES (7, '2022_06_03_141405_create_admin_user_messages_table', 0);
INSERT INTO `migrations` VALUES (8, '2022_06_03_141405_create_admins_table', 0);
INSERT INTO `migrations` VALUES (9, '2022_06_03_141405_create_api_creds_table', 0);
INSERT INTO `migrations` VALUES (10, '2022_06_03_141405_create_balance_transfers_table', 0);
INSERT INTO `migrations` VALUES (11, '2022_06_03_141620_create_account_processes_table', 0);
INSERT INTO `migrations` VALUES (12, '2022_06_03_141620_create_admin_languages_table', 0);
INSERT INTO `migrations` VALUES (13, '2022_06_03_141620_create_admin_user_conversations_table', 0);
INSERT INTO `migrations` VALUES (14, '2022_06_03_141620_create_admin_user_messages_table', 0);
INSERT INTO `migrations` VALUES (15, '2022_06_03_141620_create_admins_table', 0);
INSERT INTO `migrations` VALUES (16, '2022_06_03_141620_create_api_creds_table', 0);
INSERT INTO `migrations` VALUES (17, '2022_06_03_141620_create_balance_transfers_table', 0);
INSERT INTO `migrations` VALUES (18, '2022_06_03_142154_create_bank_plans_table', 0);
INSERT INTO `migrations` VALUES (19, '2022_06_03_142205_create_account_processes_table', 0);
INSERT INTO `migrations` VALUES (20, '2022_06_03_142205_create_admin_languages_table', 0);
INSERT INTO `migrations` VALUES (21, '2022_06_03_142205_create_admin_user_conversations_table', 0);
INSERT INTO `migrations` VALUES (22, '2022_06_03_142205_create_admin_user_messages_table', 0);
INSERT INTO `migrations` VALUES (23, '2022_06_03_142205_create_admins_table', 0);
INSERT INTO `migrations` VALUES (24, '2022_06_03_142205_create_api_creds_table', 0);
INSERT INTO `migrations` VALUES (25, '2022_06_03_142205_create_balance_transfers_table', 0);
INSERT INTO `migrations` VALUES (26, '2022_06_03_142205_create_bank_plans_table', 0);
INSERT INTO `migrations` VALUES (27, '2022_06_03_142205_create_beneficiaries_table', 0);
INSERT INTO `migrations` VALUES (28, '2022_06_03_142205_create_blog_categories_table', 0);
INSERT INTO `migrations` VALUES (29, '2022_06_03_142205_create_blogs_table', 0);
INSERT INTO `migrations` VALUES (30, '2022_06_03_142205_create_charges_table', 0);
INSERT INTO `migrations` VALUES (31, '2022_06_03_142205_create_counters_table', 0);
INSERT INTO `migrations` VALUES (32, '2022_06_03_142205_create_countries_table', 0);
INSERT INTO `migrations` VALUES (33, '2022_06_03_142205_create_currencies_table', 0);
INSERT INTO `migrations` VALUES (34, '2022_06_03_142205_create_deposits_table', 0);
INSERT INTO `migrations` VALUES (35, '2022_06_03_142205_create_disputes_table', 0);
INSERT INTO `migrations` VALUES (36, '2022_06_03_142205_create_domains_table', 0);
INSERT INTO `migrations` VALUES (37, '2022_06_03_142205_create_dps_plans_table', 0);
INSERT INTO `migrations` VALUES (38, '2022_06_03_142621_create_account_processes_table', 0);
INSERT INTO `migrations` VALUES (39, '2022_06_03_142621_create_admin_languages_table', 0);
INSERT INTO `migrations` VALUES (40, '2022_06_03_142621_create_admin_user_conversations_table', 0);
INSERT INTO `migrations` VALUES (41, '2022_06_03_142621_create_admin_user_messages_table', 0);
INSERT INTO `migrations` VALUES (42, '2022_06_03_142621_create_admins_table', 0);
INSERT INTO `migrations` VALUES (43, '2022_06_03_142621_create_api_creds_table', 0);
INSERT INTO `migrations` VALUES (44, '2022_06_03_142621_create_balance_transfers_table', 0);
INSERT INTO `migrations` VALUES (45, '2022_06_03_142621_create_bank_plans_table', 0);
INSERT INTO `migrations` VALUES (46, '2022_06_03_142621_create_beneficiaries_table', 0);
INSERT INTO `migrations` VALUES (47, '2022_06_03_142621_create_blog_categories_table', 0);
INSERT INTO `migrations` VALUES (48, '2022_06_03_142621_create_blogs_table', 0);
INSERT INTO `migrations` VALUES (49, '2022_06_03_142621_create_charges_table', 0);
INSERT INTO `migrations` VALUES (50, '2022_06_03_142621_create_counters_table', 0);
INSERT INTO `migrations` VALUES (51, '2022_06_03_142621_create_countries_table', 0);
INSERT INTO `migrations` VALUES (52, '2022_06_03_142621_create_currencies_table', 0);
INSERT INTO `migrations` VALUES (53, '2022_06_03_142621_create_deposits_table', 0);
INSERT INTO `migrations` VALUES (54, '2022_06_03_142621_create_disputes_table', 0);
INSERT INTO `migrations` VALUES (55, '2022_06_03_142621_create_domains_table', 0);
INSERT INTO `migrations` VALUES (56, '2022_06_03_142621_create_dps_plans_table', 0);
INSERT INTO `migrations` VALUES (57, '2022_06_03_142621_create_email_templates_table', 0);
INSERT INTO `migrations` VALUES (58, '2022_06_03_142621_create_escrows_table', 0);
INSERT INTO `migrations` VALUES (59, '2022_06_03_142621_create_exchange_money_table', 0);
INSERT INTO `migrations` VALUES (60, '2022_06_03_142621_create_faqs_table', 0);
INSERT INTO `migrations` VALUES (61, '2022_06_03_142621_create_fdr_plans_table', 0);
INSERT INTO `migrations` VALUES (62, '2022_06_03_142621_create_features_table', 0);
INSERT INTO `migrations` VALUES (63, '2022_06_03_142621_create_fonts_table', 0);
INSERT INTO `migrations` VALUES (64, '2022_06_03_142621_create_generalsettings_table', 0);
INSERT INTO `migrations` VALUES (65, '2022_06_03_142621_create_installment_logs_table', 0);
INSERT INTO `migrations` VALUES (66, '2022_06_03_142621_create_inv_items_table', 0);
INSERT INTO `migrations` VALUES (67, '2022_06_03_142621_create_invoices_table', 0);
INSERT INTO `migrations` VALUES (68, '2022_06_03_142621_create_kyc_forms_table', 0);
INSERT INTO `migrations` VALUES (69, '2022_06_03_142621_create_languages_table', 0);
INSERT INTO `migrations` VALUES (70, '2022_06_03_142621_create_loan_plans_table', 0);
INSERT INTO `migrations` VALUES (71, '2022_06_03_142621_create_login_logs_table', 0);
INSERT INTO `migrations` VALUES (72, '2022_06_03_142621_create_members_table', 0);
INSERT INTO `migrations` VALUES (73, '2022_06_03_142621_create_merchant_payments_table', 0);
INSERT INTO `migrations` VALUES (74, '2022_06_03_142621_create_merchants_table', 0);
INSERT INTO `migrations` VALUES (75, '2022_06_03_142621_create_money_requests_table', 0);
INSERT INTO `migrations` VALUES (76, '2022_06_03_142621_create_notifications_table', 0);
INSERT INTO `migrations` VALUES (77, '2022_06_03_143046_create_account_processes_table', 0);
INSERT INTO `migrations` VALUES (78, '2022_06_03_143046_create_admin_languages_table', 0);
INSERT INTO `migrations` VALUES (79, '2022_06_03_143046_create_admin_user_conversations_table', 0);
INSERT INTO `migrations` VALUES (80, '2022_06_03_143046_create_admin_user_messages_table', 0);
INSERT INTO `migrations` VALUES (81, '2022_06_03_143046_create_admins_table', 0);
INSERT INTO `migrations` VALUES (82, '2022_06_03_143046_create_api_creds_table', 0);
INSERT INTO `migrations` VALUES (83, '2022_06_03_143046_create_balance_transfers_table', 0);
INSERT INTO `migrations` VALUES (84, '2022_06_03_143046_create_bank_plans_table', 0);
INSERT INTO `migrations` VALUES (85, '2022_06_03_143046_create_beneficiaries_table', 0);
INSERT INTO `migrations` VALUES (86, '2022_06_03_143046_create_blog_categories_table', 0);
INSERT INTO `migrations` VALUES (87, '2022_06_03_143046_create_blogs_table', 0);
INSERT INTO `migrations` VALUES (88, '2022_06_03_143046_create_charges_table', 0);
INSERT INTO `migrations` VALUES (89, '2022_06_03_143046_create_counters_table', 0);
INSERT INTO `migrations` VALUES (90, '2022_06_03_143046_create_countries_table', 0);
INSERT INTO `migrations` VALUES (91, '2022_06_03_143046_create_currencies_table', 0);
INSERT INTO `migrations` VALUES (92, '2022_06_03_143046_create_deposits_table', 0);
INSERT INTO `migrations` VALUES (93, '2022_06_03_143046_create_disputes_table', 0);
INSERT INTO `migrations` VALUES (94, '2022_06_03_143046_create_domains_table', 0);
INSERT INTO `migrations` VALUES (95, '2022_06_03_143046_create_dps_plans_table', 0);
INSERT INTO `migrations` VALUES (96, '2022_06_03_143046_create_email_templates_table', 0);
INSERT INTO `migrations` VALUES (97, '2022_06_03_143046_create_escrows_table', 0);
INSERT INTO `migrations` VALUES (98, '2022_06_03_143046_create_exchange_money_table', 0);
INSERT INTO `migrations` VALUES (99, '2022_06_03_143046_create_faqs_table', 0);
INSERT INTO `migrations` VALUES (100, '2022_06_03_143046_create_fdr_plans_table', 0);
INSERT INTO `migrations` VALUES (101, '2022_06_03_143046_create_features_table', 0);
INSERT INTO `migrations` VALUES (102, '2022_06_03_143046_create_fonts_table', 0);
INSERT INTO `migrations` VALUES (103, '2022_06_03_143046_create_generalsettings_table', 0);
INSERT INTO `migrations` VALUES (104, '2022_06_03_143046_create_installment_logs_table', 0);
INSERT INTO `migrations` VALUES (105, '2022_06_03_143046_create_inv_items_table', 0);
INSERT INTO `migrations` VALUES (106, '2022_06_03_143046_create_invoices_table', 0);
INSERT INTO `migrations` VALUES (107, '2022_06_03_143046_create_kyc_forms_table', 0);
INSERT INTO `migrations` VALUES (108, '2022_06_03_143046_create_languages_table', 0);
INSERT INTO `migrations` VALUES (109, '2022_06_03_143046_create_loan_plans_table', 0);
INSERT INTO `migrations` VALUES (110, '2022_06_03_143046_create_login_logs_table', 0);
INSERT INTO `migrations` VALUES (111, '2022_06_03_143046_create_members_table', 0);
INSERT INTO `migrations` VALUES (112, '2022_06_03_143046_create_merchant_payments_table', 0);
INSERT INTO `migrations` VALUES (113, '2022_06_03_143046_create_merchants_table', 0);
INSERT INTO `migrations` VALUES (114, '2022_06_03_143046_create_money_requests_table', 0);
INSERT INTO `migrations` VALUES (115, '2022_06_03_143046_create_notifications_table', 0);
INSERT INTO `migrations` VALUES (116, '2022_06_03_143046_create_other_banks_table', 0);
INSERT INTO `migrations` VALUES (117, '2022_06_03_143046_create_pages_table', 0);
INSERT INTO `migrations` VALUES (118, '2022_06_03_143046_create_pagesettings_table', 0);
INSERT INTO `migrations` VALUES (119, '2022_06_03_143046_create_payment_gateways_table', 0);
INSERT INTO `migrations` VALUES (120, '2022_06_03_143046_create_referral_bonuses_table', 0);
INSERT INTO `migrations` VALUES (121, '2022_06_03_143046_create_referrals_table', 0);
INSERT INTO `migrations` VALUES (122, '2022_06_03_143046_create_request_domains_table', 0);
INSERT INTO `migrations` VALUES (123, '2022_06_03_143046_create_reviews_table', 0);
INSERT INTO `migrations` VALUES (124, '2022_06_03_143046_create_roles_table', 0);
INSERT INTO `migrations` VALUES (125, '2022_06_03_143046_create_save_accounts_table', 0);
INSERT INTO `migrations` VALUES (126, '2022_06_03_143046_create_seotools_table', 0);
INSERT INTO `migrations` VALUES (127, '2022_06_03_143046_create_services_table', 0);
INSERT INTO `migrations` VALUES (128, '2022_06_03_143046_create_sitemaps_table', 0);
INSERT INTO `migrations` VALUES (129, '2022_06_03_143046_create_sliders_table', 0);
INSERT INTO `migrations` VALUES (130, '2022_06_03_143046_create_social_providers_table', 0);
INSERT INTO `migrations` VALUES (131, '2022_06_03_143046_create_socialsettings_table', 0);
INSERT INTO `migrations` VALUES (132, '2022_06_03_143046_create_subscribers_table', 0);
INSERT INTO `migrations` VALUES (133, '2022_06_03_143046_create_support_tickets_table', 0);
INSERT INTO `migrations` VALUES (134, '2022_06_03_143046_create_tenants_table', 0);
INSERT INTO `migrations` VALUES (135, '2022_06_03_143046_create_ticket_messages_table', 0);
INSERT INTO `migrations` VALUES (136, '2022_06_03_143046_create_transactions_table', 0);
INSERT INTO `migrations` VALUES (137, '2022_06_03_143046_create_user_dps_table', 0);
INSERT INTO `migrations` VALUES (138, '2022_06_03_143046_create_user_fdrs_table', 0);
INSERT INTO `migrations` VALUES (139, '2022_06_03_143046_create_user_loans_table', 0);
INSERT INTO `migrations` VALUES (140, '2022_06_03_143046_create_user_notifications_table', 0);
INSERT INTO `migrations` VALUES (141, '2022_06_03_143046_create_user_subscriptions_table', 0);
INSERT INTO `migrations` VALUES (142, '2022_06_03_143046_create_users_table', 0);
INSERT INTO `migrations` VALUES (143, '2022_06_03_143046_create_vouchers_table', 0);
INSERT INTO `migrations` VALUES (144, '2022_06_03_143046_create_wallets_table', 0);
INSERT INTO `migrations` VALUES (145, '2022_06_03_143046_create_wire_transfer_banks_table', 0);
INSERT INTO `migrations` VALUES (146, '2022_06_03_143046_create_wire_transfers_table', 0);
INSERT INTO `migrations` VALUES (147, '2022_06_03_143046_create_withdraw_methods_table', 0);
INSERT INTO `migrations` VALUES (148, '2022_06_03_143046_create_withdrawals_table', 0);
INSERT INTO `migrations` VALUES (149, '2022_06_03_143046_create_withdraws_table', 0);
INSERT INTO `migrations` VALUES (150, '2022_06_03_143047_add_foreign_keys_to_domains_table', 0);
INSERT INTO `migrations` VALUES (151, '2022_06_03_172626_create_domains_table', 0);
INSERT INTO `migrations` VALUES (152, '2022_06_03_172627_add_foreign_keys_to_domains_table', 0);

-- ----------------------------
-- Table structure for pages
-- ----------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages`  (
  `id` int(191) NOT NULL AUTO_INCREMENT,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_tag` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `meta_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `header` tinyint(1) NOT NULL DEFAULT 0,
  `footer` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pages
-- ----------------------------
INSERT INTO `pages` VALUES (1, 'About Us', 'about', '<div helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-size:=\"\" 14px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" font-weight:=\"\" 400;=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\"><h2><font size=\"6\">Title number 1</font><br></h2><p><span style=\"font-weight: 700;\">Lorem Ipsum</span>&nbsp;is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p></div><div helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-size:=\"\" 14px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" font-weight:=\"\" 400;=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\"><h2><font size=\"6\">Title number 2</font><br></h2><p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p></div><br helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-size:=\"\" 14px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" font-weight:=\"\" 400;=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\"><div helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-size:=\"\" 14px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" font-weight:=\"\" 400;=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\"><h2><font size=\"6\">Title number 3</font><br></h2><p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.</p><p>The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from \"de Finibus Bonorum et Malorum\" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.</p></div><h2 helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-weight:=\"\" 700;=\"\" line-height:=\"\" 1.1;=\"\" color:=\"\" rgb(51,=\"\" 51,=\"\" 51);=\"\" margin:=\"\" 0px=\"\" 15px;=\"\" font-size:=\"\" 30px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\" style=\"font-family: \" 51);\"=\"\"><font size=\"6\">Title number 9</font><br></h2><p helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-size:=\"\" 14px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" font-weight:=\"\" 400;=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>', NULL, NULL, 1, 0, 1);
INSERT INTO `pages` VALUES (2, 'Privacy & Policy', 'privacy', '<div helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-size:=\"\" 14px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" font-weight:=\"\" 400;=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\"><h2>Title number 1</h2><p><span style=\"font-weight: 700;\">Lorem Ipsum</span>&nbsp;is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p></div><div helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-size:=\"\" 14px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" font-weight:=\"\" 400;=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\"><h2><font size=\"6\">Title number 2</font><br></h2><p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p></div><br helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-size:=\"\" 14px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" font-weight:=\"\" 400;=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\"><div helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-size:=\"\" 14px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" font-weight:=\"\" 400;=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\"><h2><font size=\"6\">Title number 3</font><br></h2><p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.</p><p>The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from \"de Finibus Bonorum et Malorum\" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.</p></div><h2 helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-weight:=\"\" 700;=\"\" line-height:=\"\" 1.1;=\"\" color:=\"\" rgb(51,=\"\" 51,=\"\" 51);=\"\" margin:=\"\" 0px=\"\" 15px;=\"\" font-size:=\"\" 30px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\" 51);\"=\"\" style=\"font-family: \"><font size=\"6\">Title number 9</font><br></h2><p helvetica=\"\" neue\",=\"\" helvetica,=\"\" arial,=\"\" sans-serif;=\"\" font-size:=\"\" 14px;=\"\" font-style:=\"\" normal;=\"\" font-variant-ligatures:=\"\" font-variant-caps:=\"\" font-weight:=\"\" 400;=\"\" letter-spacing:=\"\" orphans:=\"\" 2;=\"\" text-align:=\"\" start;=\"\" text-indent:=\"\" 0px;=\"\" text-transform:=\"\" none;=\"\" white-space:=\"\" widows:=\"\" word-spacing:=\"\" -webkit-text-stroke-width:=\"\" background-color:=\"\" rgb(255,=\"\" 255,=\"\" 255);=\"\" text-decoration-style:=\"\" initial;=\"\" text-decoration-color:=\"\" initial;\"=\"\">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.</p>', 'test,test1,test2,test3', 'Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc.', 0, 1, 1);

-- ----------------------------
-- Table structure for pagesettings
-- ----------------------------
DROP TABLE IF EXISTS `pagesettings`;
CREATE TABLE `pagesettings`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_success` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `contact_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `side_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `side_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `street` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `phone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `fax` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `site` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `slider` tinyint(1) NOT NULL DEFAULT 1,
  `service` tinyint(1) NOT NULL DEFAULT 1,
  `featured` tinyint(1) NOT NULL DEFAULT 1,
  `small_banner` tinyint(1) NOT NULL DEFAULT 1,
  `best` tinyint(1) NOT NULL DEFAULT 1,
  `top_rated` tinyint(1) NOT NULL DEFAULT 1,
  `large_banner` tinyint(1) NOT NULL DEFAULT 1,
  `big` tinyint(1) NOT NULL DEFAULT 1,
  `hot_sale` tinyint(1) NOT NULL DEFAULT 1,
  `hero_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `hero_subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `hero_btn_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `hero_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `hero_photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `review_blog` tinyint(1) NOT NULL DEFAULT 1,
  `pricing_plan` tinyint(1) NOT NULL DEFAULT 0,
  `service_subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `service_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `service_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `plan_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `plan_subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `review_subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `review_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `review_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `quick_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `quick_subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `quick_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `quick_photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `quick_background` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `blog_subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `blog_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `blog_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `faq_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `faq_subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `about_photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `about_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `about_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `about_attributes` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `about_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `about_details` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `service_photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `service_video` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `strategy_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `strategy_details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `strategy_banner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `footer_top_photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `footer_top_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `footer_top_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `banner_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `banner_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `banner_link1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `banner_link2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `app_banner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `app_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `app_details` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `app_store_photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `app_store_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `app_google_store` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `app_google_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pagesettings
-- ----------------------------
INSERT INTO `pagesettings` VALUES (1, 'Success! Thanks for contacting us, we will get back to you shortly.', 'admin@geniusocean.com', '<h4 class=\"subtitle\" style=\"margin-bottom: 6px; font-weight: 600; line-height: 28px; font-size: 28px; text-transform: uppercase;\">WE\'D LOVE TO</h4><h2 class=\"title\" style=\"margin-bottom: 13px;font-weight: 600;line-height: 50px;font-size: 40px;color: #1f71d4;text-transform: uppercase;\">HEAR FROM YOU</h2>', '<span style=\"color: rgb(119, 119, 119);\">Send us a message and we\' ll respond as soon as possible</span><br>', 'FEEL FREE TO DROP US A MESSAGE', 'Need to speak to us? Do you have any queries or suggestions? Please contact us about all enquiries including membership and volunteer work using the form below.', '3584 Hickory Heights Drive ,Hanover MD 21076, USA', '+12 3456 7890 1234', '00 000 000 000', 'admin@geniusocean.com', 'https://geniusocean.com/', 1, 1, 0, 0, 0, 1, 1, 1, 1, 'Simple And Safe Digital Banking System', 'MT System works around your schedule, offering innovative products that is better, faster and affordable', 'http://localhost/geniusbank/', 'https://www.youtube.com/watch?v=lG-J1QC8cKY&ab_channel=EsoGolpoKoriPrime', 'qN2bqitJ1645077354.jpg', 1, 1, 'The client perspective depends on Business first growth.', 'The Better Way to Save & Invest', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'Simple Transparent Pricing', 'The best price we only can ask for you.', 'Testimonial', 'What customers say about us', 'The client perspective depends on Business first growth. How big business can be. We provide best service all area.', 'Need a Personalized Solution?', 'Explicabo repellat minus eaque velit unde nulla nobis veritatis labore dolore, necessitatibus harum laborum at, aut reprehenderit!', 'http://localhost/geniusbank/', '4TsFX1TA1645088780.png', 'uhzm3tT31645088780.jpg', 'Latest Blog', 'Latest News & Tips', 'Banking commodi explicabo aperiam unde maxime debitis.', 'Frequently Asked Questions', 'Though we have provided lots of information about us and how we serve what is our working process our terms and conditions our policies etc.', 'N7TNLIK31645005637.jpg', 'WE ARE Genius Bank', 'A place for everyone who wants to simply banking system. Deposit funds using payment gateway or bank transfer. A Bank at fair price is guaranteed. Nothing extra. Join over 700,000 users from all over the world satisfied with our services. A place for everyone who wants to simply loans and Dps. Even with a history of hefty technological investments and an even larger donations, consumer and investor confidence has never waned.', '[\"Get up to $15,000 Cash Fast\",\"15 Minute Online Application\",\"Coverage around the world\",\"Business without borders\",\"Affiliates and partnerships\",\"Bad Credit Considered2\"]', 'https://www.google.com/', '<h3>We are Secure and Stable</h3>\r\nGenius Bank has become one of the largest donors and the largest bank donor in Bangladesh. The bank has won numerous international awards because of its unique approach as a socially conscious bank\r\nAs a result it now provides unrivaled banking technology offerings to all its customers. Because of this mindset, most local banks have joined Genius Bank banking infrastructure instead of pursuing their own.\r\n<br><br><br>\r\n<h3>We are Secure and Stable</h3>\r\nGenius Bank has become one of the largest donors and the largest bank donor in Bangladesh. The bank has won numerous international awards because of its unique approach as a socially conscious bank\r\nAs a result it now provides unrivaled banking technology offerings to all its customers. Because of this mindset, most local banks have joined Genius Bank banking infrastructure instead of pursuing their own.\r\n<br><br><br>\r\n\r\n<h3>We are Secure and Stable</h3>\r\nGenius Bank has become one of the largest donors and the largest bank donor in Bangladesh. The bank has won numerous international awards because of its unique approach as a socially conscious bank\r\nAs a result it now provides unrivaled banking technology offerings to all its customers. Because of this mindset, most local banks have joined Genius Bank banking infrastructure instead of pursuing their own.\r\n<br><br><br>\r\n\r\n<h3>We are Secure and Stable</h3>\r\nGenius Bank has become one of the largest donors and the largest bank donor in Bangladesh. The bank has won numerous international awards because of its unique approach as a socially conscious bank\r\nAs a result it now provides unrivaled banking technology offerings to all its customers. Because of this mindset, most local banks have joined Genius Bank banking infrastructure instead of pursuing their own.\r\n<br><br><br>', '1639568953bg-banner.jpg', 'https://www.youtube.com/watch?v=0gv7OC9L2s8', 'How it Works', 'The strategy where user can use the banking system. The strategy is simple easier to use. This is the fewer step to follow to create a bank account.', 'cjER6eH01645442056.png', '1639561929call-to-action-bg.jpg', 'GET STARTED TODAY WITH BITCOIN', 'Open account for free and start trading Bitcoins!', '<h4 class=\"subtitle\" style=\"font-weight: 600; line-height: 1.2381; font-size: 24px; color: rgb(31, 113, 212);\">More convenient than others</h4>', '<h2 class=\"title\" style=\"font-weight: 600; line-height: 60px; font-size: 50px; color: rgb(23, 34, 44);\">Find Value &amp; Build confidence</h2>', 'https://www.google.com/', 'https://www.google.com/', 'gFNRbRDL1645425298.png', 'Your banking experience anytime, anywhere', 'Deserunt hic consequatur ex placeat! atque repellendus inventore quisquam, perferendis, eum reiciendis quia nesciunt fuga. Natus illum doloremque sed perferendis blanditiis maiores, voluptas ad quas beatae facilis totam officiis ratione, ab cumque libero. Ducimus molestias iusto facilis!\r\n\r\nNatus illum doloremque sed perferendis blanditiis maiores, voluptas ad quas beatae facilis totam officiis ratione, ab cumque libero. Ducimus molestias iusto facilis!', '9HX3cjLu1645425298.png', 'https://www.google.com/', 'zbT8VZef1645425298.png', NULL);

-- ----------------------------
-- Table structure for payment_gateways
-- ----------------------------
DROP TABLE IF EXISTS `payment_gateways`;
CREATE TABLE `payment_gateways`  (
  `id` int(191) NOT NULL AUTO_INCREMENT,
  `subtitle` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `type` enum('manual','automatic') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'manual',
  `information` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `keyword` varchar(191) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `currency_id` varchar(191) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0',
  `status` int(191) NOT NULL DEFAULT 1,
  `subins_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 28 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of payment_gateways
-- ----------------------------
INSERT INTO `payment_gateways` VALUES (6, NULL, NULL, NULL, 'Flutter Wave', 'automatic', '{\"public_key\":\"FLWPUBK_TEST-299dc2c8bf4c7f14f7d7f48c32433393-X\",\"secret_key\":\"FLWSECK_TEST-afb1f2a4789002d7c0f2185b830450b7-X\",\"text\":\"Pay via your Flutter Wave account.\"}', 'flutterwave', '[\"1\"]', 0, NULL);
INSERT INTO `payment_gateways` VALUES (8, NULL, NULL, NULL, 'Authorize.Net', 'automatic', '{\"login_id\":\"76zu9VgUSxrJ\",\"txn_key\":\"2Vj62a6skSrP5U3X\",\"sandbox_check\":1,\"text\":\"Pay Via Authorize.Net\"}', 'authorize.net', '[\"1\"]', 0, NULL);
INSERT INTO `payment_gateways` VALUES (9, NULL, NULL, NULL, 'Razorpay', 'automatic', '{\"key\":\"rzp_test_xDH74d48cwl8DF\",\"secret\":\"cr0H1BiQ20hVzhpHfHuNbGri\",\"text\":\"Pay via your Razorpay account.\"}', 'razorpay', '[\"8\"]', 0, NULL);
INSERT INTO `payment_gateways` VALUES (10, NULL, NULL, NULL, 'Mollie Payment', 'automatic', '{\"key\":\"test_jePgBjaRV5rUdzWc44rb2fUxgM2dM9\",\"text\":\"Pay with Mollie Payment.\"}', 'mollie', '[\"1\",\"6\"]', 1, NULL);
INSERT INTO `payment_gateways` VALUES (11, NULL, NULL, NULL, 'Paytm', 'automatic', '{\"merchant\":\"tkogux49985047638244\",\"secret\":\"LhNGUUKE9xCQ9xY8\",\"website\":\"WEBSTAGING\",\"industry\":\"Retail\",\"sandbox_check\":1,\"text\":\"Pay via your Paytm account.\"}', 'paytm', '[\"8\"]', 1, NULL);
INSERT INTO `payment_gateways` VALUES (12, NULL, NULL, NULL, 'Paystack', 'automatic', '{\"key\":\"pk_test_162a56d42131cbb01932ed0d2c48f9cb99d8e8e2\",\"email\":\"junnuns@gmail.com\",\"text\":\"Pay via your Paystack account.\"}', 'paystack', '[\"9\"]', 1, NULL);
INSERT INTO `payment_gateways` VALUES (13, NULL, NULL, NULL, 'Instamojo', 'automatic', '{\"key\":\"test_172371aa837ae5cad6047dc3052\",\"token\":\"test_4ac5a785e25fc596b67dbc5c267\",\"sandbox_check\":1,\"text\":\"Pay via your Instamojo account.\"}', 'instamojo', '[\"8\"]', 1, NULL);
INSERT INTO `payment_gateways` VALUES (14, NULL, NULL, NULL, 'Stripe', 'automatic', '{\"key\":\"pk_test_UnU1Coi1p5qFGwtpjZMRMgJM\",\"secret\":\"sk_test_QQcg3vGsKRPlW6T3dXcNJsor\",\"text\":\"Pay via your Credit Card.\"}', 'stripe', '[\"1\"]', 1, NULL);
INSERT INTO `payment_gateways` VALUES (15, NULL, NULL, NULL, 'Paypal', 'automatic', '{\"client_id\":\"AcWYnysKa_elsQIAnlfsJXokR64Z31CeCbpis9G3msDC-BvgcbAwbacfDfEGSP-9Dp9fZaGgD05pX5Qi\",\"client_secret\":\"EGZXTq6d6vBPq8kysVx8WQA5NpavMpDzOLVOb9u75UfsJ-cFzn6aeBXIMyJW2lN1UZtJg5iDPNL9ocYE\",\"sandbox_check\":1,\"text\":\"Pay via your PayPal account.\"}', 'paypal', '[\"1\"]', 1, NULL);

-- ----------------------------
-- Table structure for plans
-- ----------------------------
DROP TABLE IF EXISTS `plans`;
CREATE TABLE `plans`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double(8, 2) NOT NULL DEFAULT 0,
  `duration` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `durationtype` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_users` int(11) NOT NULL DEFAULT 0,
  `tenant_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `plans_name_unique`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of plans
-- ----------------------------
INSERT INTO `plans` VALUES (1, 'Free', 0.00, '1', 'month', 0, NULL, '2022-05-05 07:20:38', '2022-05-05 07:20:38');
INSERT INTO `plans` VALUES (2, 'STARTER', 50.00, '6', 'Month', 0, NULL, '2022-05-05 08:12:26', '2022-05-05 08:12:26');
INSERT INTO `plans` VALUES (3, 'PRO', 100.00, '1', 'Year', 0, NULL, '2022-05-05 08:12:47', '2022-05-05 08:12:47');

-- ----------------------------
-- Table structure for request_domains
-- ----------------------------
DROP TABLE IF EXISTS `request_domains`;
CREATE TABLE `request_domains`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 68 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of request_domains
-- ----------------------------
INSERT INTO `request_domains` VALUES (38, 'bank1', 'bank1@gmail.com', 'bank1', '$2y$10$RS2zxaXOEcwAvnbcnWJm/uOVQwpPYYwSNxC7gvwghvKMD0WZ1bYyW', NULL, 'Admin', 1, '2022-06-19 20:17:35', '2022-06-19 20:18:32', NULL);
INSERT INTO `request_domains` VALUES (39, 'bank2', 'bank2@gmail.com', 'bank2', '$2y$10$vobQJGxed0gvgjjUvc4dsOR0KBnjb81H0SFKGRS9rjTMHCbicxkA2', NULL, 'Admin', 1, '2022-06-20 00:12:55', '2022-06-20 00:13:52', NULL);
INSERT INTO `request_domains` VALUES (40, 'bank3', 'bank3@gmail.com', 'bank3', '$2y$10$WSMPra5PMXsEN5lg9XOeXubJ8iI2aTMqc2FjaN36HF7uW2hyqijfq', NULL, 'Admin', 1, '2022-06-20 00:31:40', '2022-06-20 00:53:19', NULL);
INSERT INTO `request_domains` VALUES (67, 'aaaa', 'aaaa@gmail.com', 'aaaa', '$2y$10$7QWIpdix.ymy3uUUq5zwYuLyjq.o0vawppYdkI.fYiumUTXP.ofaG', NULL, 'Admin', 1, '2022-07-21 08:17:59', '2022-07-21 08:20:10', NULL);

-- ----------------------------
-- Table structure for reviews
-- ----------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of reviews
-- ----------------------------
INSERT INTO `reviews` VALUES (5, 'PME52yRz1645070778.png', 'Jhon Smith', 'CEO & Founder', 'The is just awesome,  best quality service ever I had. You can trust them and deposit your funds. Their Loan plans are really helpful. Easy to use their online banking system.');
INSERT INTO `reviews` VALUES (6, 'AjOD94Yk1645070744.png', 'Jazmin Sultana', 'CEO & Founder', 'The is just awesome,  best quality service ever I had. You can trust them and deposit your funds. Their Loan plans are really helpful. Easy to use their online banking system.');

-- ----------------------------
-- Table structure for seotools
-- ----------------------------
DROP TABLE IF EXISTS `seotools`;
CREATE TABLE `seotools`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `google_analytics` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `meta_keys` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of seotools
-- ----------------------------
INSERT INTO `seotools` VALUES (1, '<script async src=\"https://www.googletagmanager.com/gtag/js?id=UA-137437974-1\"></script>  <script>    window.dataLayer = window.dataLayer || [];    function gtag(){dataLayer.push(arguments);}    gtag(\'js\', new Date());    gtag(\'config\', \'UA-137437974-1\');  </script>', 'Genius,Ocean,Sea,Etc,Genius,Ocean,SeaGenius,Ocean,Sea,Etc,Genius,Ocean,SeaGenius,Ocean,Sea,Etc,Genius,Ocean,Sea');

-- ----------------------------
-- Table structure for services
-- ----------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services`  (
  `id` int(191) NOT NULL AUTO_INCREMENT,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of services
-- ----------------------------
INSERT INTO `services` VALUES (15, 'HIGH LIQUIDITY', 'Fast access to high liquidity orderbook</br>\r\nfor top currency pairs', '1639476836high-liquidity.png');
INSERT INTO `services` VALUES (16, 'COST EFFICIENCY', 'Reasonable trading fees for takers</br>\r\nand all market makers', '1639476885cost-efficiency.png');
INSERT INTO `services` VALUES (17, 'MOBILE APP', 'Trading via our Mobile App, Available</br>\r\nin Play Store & App Store', '1639476911mobile-app.png');
INSERT INTO `services` VALUES (18, 'PAYMENT OPTIONS', 'Popular methods: Visa, MasterCard,</br>\r\nbank transfer, cryptocurrency', '1639476937payment-options.png');
INSERT INTO `services` VALUES (19, 'WORLD COVERAGE', 'Providing services in 99% countries</br>\r\naround all the globe', '1639476969world-coverage.png');
INSERT INTO `services` VALUES (20, 'STRONG SECURITY', 'Protection against DDoS attacks,</br>\r\nfull data encryption', '1639476998strong-security.png');

-- ----------------------------
-- Table structure for sitemaps
-- ----------------------------
DROP TABLE IF EXISTS `sitemaps`;
CREATE TABLE `sitemaps`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sitemap_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sliders
-- ----------------------------
DROP TABLE IF EXISTS `sliders`;
CREATE TABLE `sliders`  (
  `id` int(191) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subtitle_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `subtitle_size` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `subtitle_color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `subtitle_anime` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `title_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `title_size` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `title_color` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `title_anime` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `details_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `details_size` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `details_color` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `details_anime` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `position` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `link` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sliders
-- ----------------------------
INSERT INTO `sliders` VALUES (9, 'YOU CAN TRUST', '24', '#ffffff', 'slideInUp', 'BITCOIN EXCHANGE', '60', '#ffffff', 'slideInDown', 'Highlight your personality  and look with these fabulous and exquisite fashion.', '16', '#ffffff', 'slideInDown', '1639479478bg1.jpg', 'slide-one', 'https://www.google.com/');
INSERT INTO `sliders` VALUES (10, 'TO BITCOIN', '24', '#c32d2d', 'slideInUp', 'SECURE AND EASY WAY', '60', '#bc2727', 'slideInDown', NULL, NULL, '#c51d1d', 'slideInLeft', '1639479394bg2.jpg', 'slide-one', 'https://www.google.com/');

-- ----------------------------
-- Table structure for social_providers
-- ----------------------------
DROP TABLE IF EXISTS `social_providers`;
CREATE TABLE `social_providers`  (
  `id` int(191) NOT NULL AUTO_INCREMENT,
  `user_id` int(191) NOT NULL,
  `provider_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of social_providers
-- ----------------------------
INSERT INTO `social_providers` VALUES (3, 17, '102485372716947456487', 'google', '2019-06-19 19:06:00', '2019-06-19 19:06:00');
INSERT INTO `social_providers` VALUES (4, 18, '109955884428371086401', 'google', '2019-06-19 19:17:04', '2019-06-19 19:17:04');

-- ----------------------------
-- Table structure for socialsettings
-- ----------------------------
DROP TABLE IF EXISTS `socialsettings`;
CREATE TABLE `socialsettings`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `facebook` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `gplus` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `twitter` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `linkedin` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `dribble` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `f_status` tinyint(4) NOT NULL DEFAULT 1,
  `g_status` tinyint(4) NOT NULL DEFAULT 1,
  `t_status` tinyint(4) NOT NULL DEFAULT 1,
  `l_status` tinyint(4) NOT NULL DEFAULT 1,
  `d_status` tinyint(4) NOT NULL DEFAULT 1,
  `f_check` tinyint(10) NULL DEFAULT NULL,
  `g_check` tinyint(10) NULL DEFAULT NULL,
  `fclient_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `fclient_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `fredirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `gclient_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `gclient_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `gredirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of socialsettings
-- ----------------------------
INSERT INTO `socialsettings` VALUES (1, 'https://www.facebook.com/', 'https://plus.google.com/', 'https://twitter.com/', 'https://www.linkedin.com/', 'https://dribbble.com/', 1, 0, 1, 1, 0, 1, 1, '503140663460329', 'f66cd670ec43d14209a2728af26dcc43', 'https://localhost/crypto/auth/facebook/callback', '904681031719-sh1aolu42k7l93ik0bkiddcboghbpcfi.apps.googleusercontent.com', 'yGBWmUpPtn5yWhDAsXnswEX3', 'http://localhost/marketplace/auth/google/callback');

-- ----------------------------
-- Table structure for subscribers
-- ----------------------------
DROP TABLE IF EXISTS `subscribers`;
CREATE TABLE `subscribers`  (
  `id` int(191) NOT NULL AUTO_INCREMENT,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of subscribers
-- ----------------------------
INSERT INTO `subscribers` VALUES (1, 'ahmmedafzal4@gmail.com');
INSERT INTO `subscribers` VALUES (2, 'imtiaze93@yahoo.com');

-- ----------------------------
-- Table structure for tenants
-- ----------------------------
DROP TABLE IF EXISTS `tenants`;
CREATE TABLE `tenants`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  `data` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tenants
-- ----------------------------
INSERT INTO `tenants` VALUES ('50', '2022-06-19 20:17:56', '2022-06-19 20:17:56', '{\"tenancy_db_name\":\"bank1\",\"tenancy_db_username\":\"root\",\"tenancy_db_password\":null}');
INSERT INTO `tenants` VALUES ('51', '2022-06-20 00:13:16', '2022-06-20 00:13:16', '{\"tenancy_db_name\":\"bank2\",\"tenancy_db_username\":\"root\",\"tenancy_db_password\":null}');
INSERT INTO `tenants` VALUES ('54', '2022-06-20 00:52:37', '2022-06-20 00:52:37', '{\"tenancy_db_name\":\"bank3\",\"tenancy_db_username\":\"root\",\"tenancy_db_password\":null}');
INSERT INTO `tenants` VALUES ('69', '2022-06-24 22:47:50', '2022-06-24 22:47:50', '{\"updated_at\":\"2022-06-24 22:47:50\",\"created_at\":\"2022-06-24 22:47:50\",\"tenancy_db_name\":\"bbb\",\"tenancy_db_username\":\"root\",\"tenancy_db_password\":null}');
INSERT INTO `tenants` VALUES ('71', '2022-06-25 08:41:56', '2022-06-25 08:41:56', '{\"updated_at\":\"2022-06-25 08:41:56\",\"created_at\":\"2022-06-25 08:41:56\",\"tenancy_db_name\":\"ccc\",\"tenancy_db_username\":\"root\",\"tenancy_db_password\":null}');
INSERT INTO `tenants` VALUES ('72', '2022-07-21 07:52:21', '2022-07-21 07:52:21', '{\"updated_at\":\"2022-07-21 07:52:21\",\"created_at\":\"2022-07-21 07:52:21\",\"tenancy_db_name\":\"boris\",\"tenancy_db_username\":\"root\",\"tenancy_db_password\":null}');
INSERT INTO `tenants` VALUES ('73', '2022-07-21 07:57:10', '2022-07-21 07:57:10', '{\"updated_at\":\"2022-07-21 07:57:10\",\"created_at\":\"2022-07-21 07:57:10\",\"tenancy_db_name\":\"itever\",\"tenancy_db_username\":\"root\",\"tenancy_db_password\":null}');
INSERT INTO `tenants` VALUES ('74', '2022-07-21 08:10:01', '2022-07-21 08:10:01', '{\"updated_at\":\"2022-07-21 08:10:01\",\"created_at\":\"2022-07-21 08:10:01\",\"tenancy_db_name\":\"smiss\",\"tenancy_db_username\":\"root\",\"tenancy_db_password\":null}');
INSERT INTO `tenants` VALUES ('75', '2022-07-21 08:18:12', '2022-07-21 08:18:12', '{\"updated_at\":\"2022-07-21 08:18:12\",\"created_at\":\"2022-07-21 08:18:12\",\"tenancy_db_name\":\"aaaa\",\"tenancy_db_username\":\"root\",\"tenancy_db_password\":null}');

-- ----------------------------
-- Table structure for user_subscriptions
-- ----------------------------
DROP TABLE IF EXISTS `user_subscriptions`;
CREATE TABLE `user_subscriptions`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `txnid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `user_id` int(11) NULL DEFAULT NULL,
  `bank_plan_id` int(11) NULL DEFAULT NULL,
  `currency_id` int(11) NULL DEFAULT NULL,
  `price` double NULL DEFAULT NULL,
  `method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `days` int(11) NULL DEFAULT NULL,
  `status` enum('pending','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 49 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_subscriptions
-- ----------------------------
INSERT INTO `user_subscriptions` VALUES (18, 'jIVw1644993862', '3137234', 50, 6, 1, 100, 'flutterwave', 100, 'completed', '2022-02-16 01:44:22', '2022-02-16 01:44:53');
INSERT INTO `user_subscriptions` VALUES (19, 'vreF1646111014', NULL, 62, 6, 1, 100, 'paypal', 100, 'pending', '2022-03-01 06:03:34', '2022-03-01 06:03:34');
INSERT INTO `user_subscriptions` VALUES (20, 'yeTi1646111315', '2adbacc97c6246998aa69d90aab5375e', 62, 6, 9, 1.3513513513514, 'instamojo', 100, 'completed', '2022-03-01 06:08:35', '2022-03-01 06:08:35');
INSERT INTO `user_subscriptions` VALUES (21, 'ZII41646111557', 'txn_3KYO0qJlIV5dN9n71WhB4b5r', 62, 6, 9, 1.3513513513514, 'stripe', 100, 'pending', '2022-03-01 06:12:39', '2022-03-01 06:12:39');
INSERT INTO `user_subscriptions` VALUES (22, 'pG6M1646111589', NULL, 62, 6, 9, 1.3513513513514, 'paypal', 100, 'pending', '2022-03-01 06:13:09', '2022-03-01 06:13:09');
INSERT INTO `user_subscriptions` VALUES (23, 'vMpW1646111619', NULL, 62, 6, 9, 1.3513513513514, 'paypal', 100, 'pending', '2022-03-01 06:13:39', '2022-03-01 06:13:39');
INSERT INTO `user_subscriptions` VALUES (24, 'bKo41646111907', '56004648YH063573D', 62, 6, 9, 1.3513513513514, 'paypal', 100, 'completed', '2022-03-01 06:18:27', '2022-03-01 06:19:38');
INSERT INTO `user_subscriptions` VALUES (25, 'JKPP1646112310', '97P17095MP529492Y', 62, 6, 1, 100, 'paypal', 100, 'completed', '2022-03-01 06:25:10', '2022-03-01 06:25:24');
INSERT INTO `user_subscriptions` VALUES (26, 'O65E1646112794', NULL, 62, 6, 9, 1.3513513513514, 'authorize.net', 100, 'pending', '2022-03-01 06:33:16', '2022-03-01 06:33:16');
INSERT INTO `user_subscriptions` VALUES (27, 'AanR1646825572', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pending', '2022-03-09 12:32:53', '2022-03-09 12:32:53');
INSERT INTO `user_subscriptions` VALUES (28, 'SIoo1646825881', NULL, 50, 6, 1, 100, 'mollie', 100, 'completed', '2022-03-09 12:38:02', '2022-03-09 12:38:02');
INSERT INTO `user_subscriptions` VALUES (29, 'Rnkb1646826036', '26W28613JS034702K', 50, 6, 9, 1.3513513513514, 'paypal', 100, 'completed', '2022-03-09 12:40:36', '2022-03-09 12:41:06');
INSERT INTO `user_subscriptions` VALUES (30, 'hGPy1646884602', NULL, 50, 6, 1, 100, 'mollie', 100, 'completed', '2022-03-10 04:56:43', '2022-03-10 04:56:43');
INSERT INTO `user_subscriptions` VALUES (31, 'd9U61646885817', NULL, 50, 6, 9, 100, 'paypal', 100, 'pending', '2022-03-10 05:16:57', '2022-03-10 05:16:57');
INSERT INTO `user_subscriptions` VALUES (32, '6qyx1646885923', NULL, 50, 6, 9, 100, 'paypal', 100, 'pending', '2022-03-10 05:18:43', '2022-03-10 05:18:43');
INSERT INTO `user_subscriptions` VALUES (33, 'WMv71646885953', NULL, 50, 6, 1, 100, 'paypal', 100, 'pending', '2022-03-10 05:19:13', '2022-03-10 05:19:13');
INSERT INTO `user_subscriptions` VALUES (34, 'FAU01646885997', NULL, 50, 6, 6, 100, 'paypal', 100, 'pending', '2022-03-10 05:19:57', '2022-03-10 05:19:57');
INSERT INTO `user_subscriptions` VALUES (35, 'e5Jc1646886604', '20220310111212800110168503203512219', 50, 6, 9, 100, 'paytm', 100, 'completed', '2022-03-10 05:30:04', '2022-03-10 05:30:19');
INSERT INTO `user_subscriptions` VALUES (36, 'GJhw1647143160', '5X30428265724942N', 72, 6, 1, 100, 'paypal', 100, 'completed', '2022-03-13 17:46:00', '2022-03-13 17:46:32');
INSERT INTO `user_subscriptions` VALUES (37, 'Ixnj1647143227', '7GR99816HC461762T', 72, 6, 1, 100, 'paypal', 100, 'completed', '2022-03-13 17:47:07', '2022-03-13 17:47:17');
INSERT INTO `user_subscriptions` VALUES (38, 'dIn31647143349', '1K984646SG758972E', 50, 6, 1, 100, 'paypal', 100, 'completed', '2022-03-13 17:49:09', '2022-03-13 17:49:59');
INSERT INTO `user_subscriptions` VALUES (39, 'k5AM1647143691', '0V0048634V776132H', 72, 6, 1, 100, 'paypal', 100, 'completed', '2022-03-13 17:54:51', '2022-03-13 17:55:01');
INSERT INTO `user_subscriptions` VALUES (40, 'vSMm1647143730', '9L599537RL016344E', 72, 6, 1, 100, 'paypal', 100, 'completed', '2022-03-13 17:55:30', '2022-03-13 17:55:40');
INSERT INTO `user_subscriptions` VALUES (41, 'uL2z1647143769', 'txn_3KciXPJlIV5dN9n70Y6wP7q6', 72, 6, 1, 100, 'stripe', 100, 'pending', '2022-03-13 17:56:12', '2022-03-13 17:56:12');
INSERT INTO `user_subscriptions` VALUES (42, 'Qn6n1647144166', 'txn_3KcidoJlIV5dN9n71sW7KiXs', 50, 6, 1, 100, 'stripe', 100, 'pending', '2022-03-13 18:02:48', '2022-03-13 18:02:48');
INSERT INTO `user_subscriptions` VALUES (43, 'WeAK1647144188', '9CY544766H703262B', 72, 6, 1, 100, 'paypal', 100, 'completed', '2022-03-13 18:03:08', '2022-03-13 18:03:19');
INSERT INTO `user_subscriptions` VALUES (44, 'IpcI1647144483', NULL, 72, 6, 1, 100, 'mollie', 100, 'completed', '2022-03-13 18:08:04', '2022-03-13 18:08:04');
INSERT INTO `user_subscriptions` VALUES (45, 'k2W81647144688', NULL, 72, 6, 9, 100, 'paytm', 100, 'pending', '2022-03-13 18:11:28', '2022-03-13 18:11:28');
INSERT INTO `user_subscriptions` VALUES (46, 'ZuzG1647144720', '20220313111212800110168581603506962', 72, 6, 9, 100, 'paytm', 100, 'completed', '2022-03-13 18:12:00', '2022-03-13 18:12:33');
INSERT INTO `user_subscriptions` VALUES (47, 'a0Ie1647145474', 'order_J6RN0gLagRgC7j', 72, 6, 9, 100, 'razorpay', 100, 'completed', '2022-03-13 18:25:37', '2022-03-13 18:25:37');
INSERT INTO `user_subscriptions` VALUES (48, 'hDrK1653381058', NULL, 124, 6, 1, 100, 'paypal', 100, 'pending', '2022-05-24 14:30:58', '2022-05-24 14:30:58');

SET FOREIGN_KEY_CHECKS = 1;
