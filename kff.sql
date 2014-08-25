-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.17-1~dotdeb.1 - (Debian)
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             8.3.0.4694
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table kff.tblMovieWatchSN
DROP TABLE IF EXISTS `tblMovieWatchSN`;
CREATE TABLE IF NOT EXISTS `tblMovieWatchSN` (
  `sn_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_account` varchar(128) NOT NULL COMMENT 'email format',
  `sn_watch_code` varchar(10) NOT NULL,
  `sn_type` int(10) unsigned NOT NULL COMMENT '1: all films ticket, 2: all films user pay ticket, 3: three films ticket',
  `sn_status` int(10) unsigned NOT NULL COMMENT '1: not activated, 2: activated, 3: disabled',
  `sn_watch_count` int(10) NOT NULL COMMENT '-1: unlimited',
  `sn_activate_time` datetime DEFAULT NULL,
  `sn_create_time` datetime NOT NULL,
  `sn_update_time` datetime NOT NULL,
  PRIMARY KEY (`sn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='2014 kff movie watch serial number';

-- Data exporting was unselected.


-- Dumping structure for table kff.tblOrder
DROP TABLE IF EXISTS `tblOrder`;
CREATE TABLE IF NOT EXISTS `tblOrder` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(20) NOT NULL,
  `member_account` varchar(128) NOT NULL,
  `order_status` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '1: not paid, 2: paid',
  `order_product_sn_id` int(10) unsigned DEFAULT NULL,
  `order_create_time` datetime NOT NULL,
  `order_update_time` datetime NOT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table kff.tblTrade
DROP TABLE IF EXISTS `tblTrade`;
CREATE TABLE IF NOT EXISTS `tblTrade` (
  `trade_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `trade_provider` int(10) unsigned NOT NULL COMMENT '1: allpay, 2: android, 3: ios',
  `trade_no` varchar(20) NOT NULL,
  `trade_status` int(10) unsigned NOT NULL COMMENT '1: succeed, others: fail',
  `trade_msg` varchar(200) NOT NULL,
  `trade_amount` int(10) unsigned NOT NULL,
  `payment_type` varchar(20) NOT NULL,
  `payment_charge_fee` int(10) unsigned NOT NULL,
  `payment_time` datetime NOT NULL,
  `simulate_paid` int(10) unsigned NOT NULL DEFAULT '0',
  `trade_create_time` datetime NOT NULL,
  PRIMARY KEY (`trade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
