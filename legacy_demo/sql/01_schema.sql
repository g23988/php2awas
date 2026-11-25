-- MySQL dump 10.11
--
-- Host: db01.shop.local    Database: shopdb
-- ------------------------------------------------------
-- Server version	5.0.77-log

/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

CREATE DATABASE IF NOT EXISTS `shopdb` DEFAULT CHARACTER SET utf8;
USE `shopdb`;

--
-- Table structure for table `admin_user`
--

DROP TABLE IF EXISTS `admin_user`;
CREATE TABLE `admin_user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(30) NOT NULL default '',
  `password` char(32) NOT NULL default '',
  `name` varchar(30) NOT NULL default '',
  `status` tinyint(1) NOT NULL default '1',
  `last_login` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `member`
--

DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `email` varchar(100) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `phone` varchar(20) NOT NULL default '',
  `level` tinyint(1) NOT NULL default '1',
  `points` int(11) NOT NULL default '0',
  `zip` varchar(5) NOT NULL default '',
  `address` varchar(200) NOT NULL default '',
  `status` tinyint(1) NOT NULL default '1',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `password` char(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sku` varchar(20) NOT NULL default '',
  `name` varchar(100) NOT NULL default '',
  `price` int(11) NOT NULL default '0',
  `stock` int(11) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `order_no` varchar(20) NOT NULL default '',
  `member_id` int(10) unsigned NOT NULL default '0',
  `receiver` varchar(50) NOT NULL default '',
  `phone` varchar(20) NOT NULL default '',
  `zip` varchar(5) NOT NULL default '',
  `address` varchar(200) NOT NULL default '',
  `note` text,
  `subtotal` int(11) NOT NULL default '0',
  `discount` int(11) NOT NULL default '0',
  `promo_discount` int(11) NOT NULL default '0',
  `shipping` int(11) NOT NULL default '0',
  `total` int(11) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `paid_at` datetime default NULL,
  `shipped_at` datetime default NULL,
  `canceled_at` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `order_no` (`order_no`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `order_item`
--

DROP TABLE IF EXISTS `order_item`;
CREATE TABLE `order_item` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `order_id` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `product_name` varchar(100) NOT NULL default '',
  `price` int(11) NOT NULL default '0',
  `qty` int(11) NOT NULL default '0',
  `amount` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `payment_log`
--

DROP TABLE IF EXISTS `payment_log`;
CREATE TABLE `payment_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `order_no` varchar(20) NOT NULL default '',
  `amount` varchar(20) NOT NULL default '',
  `chk` varchar(32) NOT NULL default '',
  `raw` text,
  `result` varchar(20) NOT NULL default '',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;

-- Dump completed on 2011-06-30  3:00:01
