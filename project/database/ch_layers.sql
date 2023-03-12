/*
 Navicat Premium Data Transfer

 Source Server         : Bank
 Source Server Type    : MySQL
 Source Server Version : 100424
 Source Host           : localhost:3306
 Source Schema         : whcode

 Target Server Type    : MySQL
 Target Server Version : 100424
 File Encoding         : 65001

 Date: 12/03/2023 22:08:18
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ch_layers
-- ----------------------------
DROP TABLE IF EXISTS `ch_layers`;
CREATE TABLE `ch_layers`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `layer_id` int(11) NOT NULL,
  `pincode` int(11) NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
