SET NAMES latin1;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `email`
-- ----------------------------
DROP TABLE IF EXISTS `email`;
CREATE TABLE `email` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` blob NOT NULL,
  `leak` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `email` (`email`) USING BTREE
) ENGINE=Aria DEFAULT CHARSET=utf8 PAGE_CHECKSUM=1;

-- ----------------------------
--  Table structure for `emailload`
-- ----------------------------
DROP TABLE IF EXISTS `emailload`;
CREATE TABLE `emailload` (
  `email` varchar(255) NOT NULL,
  `password` binary(255) NOT NULL,
  `leak` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`email`,`password`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `hash`
-- ----------------------------
DROP TABLE IF EXISTS `hash`;
CREATE TABLE `hash` (
  `hash` varchar(255) NOT NULL,
  `leak` varchar(255) NOT NULL,
  PRIMARY KEY (`hash`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `settings`
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `name` varchar(255) NOT NULL,
  `value` blob NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=Aria DEFAULT CHARSET=utf8 PAGE_CHECKSUM=1;

-- ----------------------------
--  Table structure for `sha1`
-- ----------------------------
DROP TABLE IF EXISTS `sha1`;
CREATE TABLE `sha1` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(40) NOT NULL,
  `leak` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`) USING BTREE
) ENGINE=Aria DEFAULT CHARSET=utf8 PAGE_CHECKSUM=1;

SET FOREIGN_KEY_CHECKS = 1;
