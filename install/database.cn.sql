
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `mm_admin` (
  `id` tinyint(2) NOT NULL auto_increment,
  `group_id` int(2) NOT NULL,
  `name` varchar(16) NOT NULL,
  `pswd` varchar(32) NOT NULL,
  `salt` char(2) default NULL,
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

CREATE TABLE IF NOT EXISTS `mm_article` (
  `article_id` int(10) NOT NULL auto_increment,
  `cate_id` int(4) NOT NULL,
  `title_key_` varchar(10) NOT NULL,
  `urlkey` varchar(64) NOT NULL,
  `image` varchar(64) default NULL,
  `content_txtkey_` varchar(10) NOT NULL,
  `sort_order` int(10) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`article_id`),
  KEY `cate_id` (`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

INSERT INTO `mm_article` (`article_id`, `cate_id`, `title_key_`, `urlkey`, `image`, `content_txtkey_`, `sort_order`, `status`) VALUES
(1, 2, 'k_40', 'about-us', '', 'k_41', 1, 1),
(2, 1, 'k_42', 'how-to-pay', '', 'k_43', 2, 1),
(3, 2, 'k_44', 'contact-us', '', 'k_45', 2, 1);

CREATE TABLE IF NOT EXISTS `mm_article_cate` (
  `cate_id` int(4) NOT NULL auto_increment,
  `name_key_` varchar(10) NOT NULL,
  `urlkey` varchar(32) NOT NULL,
  `image` varchar(32) default NULL,
  `sort_order` int(4) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `mm_article_cate` (`cate_id`, `name_key_`, `urlkey`, `image`, `sort_order`, `status`) VALUES
(1, 'k_38', 'help', '', 1, 1),
(2, 'k_39', 'about', '', 2, 1);

CREATE TABLE IF NOT EXISTS `mm_attribute` (
  `attr_id` int(4) NOT NULL auto_increment,
  `code` varchar(32) NOT NULL,
  `name_key_` varchar(10) NOT NULL,
  `can_filter` tinyint(1) NOT NULL default '1',
  `sort_order` int(4) NOT NULL default '0',
  `click` int(10) NOT NULL default '0',
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY  (`attr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_attribute_value` (
  `av_id` int(8) NOT NULL auto_increment,
  `attr_id` int(4) NOT NULL,
  `title_key_` varchar(10) NOT NULL,
  `sort_order` int(8) NOT NULL default '0',
  PRIMARY KEY  (`av_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_block` (
  `id` int(4) NOT NULL auto_increment,
  `code` varchar(32) NOT NULL,
  `content_txtkey_` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

INSERT INTO `mm_block` (`id`, `code`, `content_txtkey_`) VALUES
(1, 'top_tel', 'k_35'),
(2, 'foot_article', 'k_36'),
(3, 'copyright', 'k_37');

CREATE TABLE IF NOT EXISTS `mm_cart` (
  `id` int(10) NOT NULL auto_increment,
  `user_id` int(10) NOT NULL default '0',
  `session_id` char(32) default NULL,
  `goods_id` int(10) NOT NULL,
  `options` varchar(64) default NULL,
  `quantity` int(4) NOT NULL,
  `currency` char(4) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL default '0.00',
  `addtime` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_checkout` (
  `id` int(10) NOT NULL auto_increment,
  `address_id` int(10) NOT NULL default '0',
  `session_id` varchar(32) NOT NULL,
  `email` varchar(32) default NULL,
  `fullname` varchar(16) default NULL,
  `country_id` int(4) NOT NULL default '0',
  `region_id` int(8) NOT NULL default '0',
  `city` varchar(64) default NULL,
  `address` varchar(128) default NULL,
  `phone` varchar(32) default NULL,
  `postcode` char(8) default NULL,
  `shipping_id` int(4) NOT NULL default '0',
  `payment_id` int(2) NOT NULL default '0',
  `coupon_id` int(10) NOT NULL default '0',
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_comments` (
  `id` int(10) NOT NULL auto_increment,
  `goods_id` int(10) NOT NULL,
  `username` varchar(32) NOT NULL,
  `language` varchar(8) NOT NULL,
  `content` text NOT NULL,
  `time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_comments_summary` (
  `comments_id` int(10) NOT NULL,
  `summary_id` int(2) NOT NULL,
  `score` int(11) NOT NULL,
  KEY `comments_id` (`comments_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `mm_country` (
  `id` int(4) NOT NULL auto_increment,
  `code` char(2) NOT NULL,
  `name` varchar(32) NOT NULL,
  `phone_code` varchar(4) default NULL,
  `time_zone` float(2,1) default NULL,
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `mm_country` (`id`, `code`, `name`, `phone_code`, `time_zone`, `status`) VALUES
(1, 'CN', '中国', '86', 8.0, 1);

CREATE TABLE IF NOT EXISTS `mm_coupon` (
  `id` int(10) NOT NULL auto_increment,
  `code` char(12) NOT NULL,
  `money` decimal(6,2) NOT NULL,
  `send` tinyint(1) NOT NULL default '0',
  `sendto` varchar(32) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `create_order` int(10) NOT NULL default '0',
  `used_order` int(10) NOT NULL default '0',
  `createtime` int(11) NOT NULL,
  `expiretime` int(11) NOT NULL default '0',
  `usetime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_currency` (
  `id` int(2) NOT NULL auto_increment,
  `name_key_` varchar(10) NOT NULL,
  `code` char(4) NOT NULL,
  `rate` decimal(8,4) NOT NULL,
  `symbol` char(2) NOT NULL,
  `is_main` tinyint(1) NOT NULL default '0',
  `sort_order` int(2) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `mm_currency` (`id`, `name_key_`, `code`, `rate`, `symbol`, `is_main`, `sort_order`, `status`) VALUES
(1, 'k_2', 'RMB', 1.0000, '¥', 0, 1, 1),
(2, 'k_180', 'USD', 0.1634, '$', 0, 2, 0);

CREATE TABLE IF NOT EXISTS `mm_dict` (
  `id` int(10) NOT NULL auto_increment,
  `dict_key` char(10) NOT NULL,
  `dict_val_zh_cn` varchar(255) default NULL,
  `dict_val_zh_tw` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `dict_key` (`dict_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='dictionary' AUTO_INCREMENT=135 ;

INSERT INTO `mm_dict` (`id`, `dict_key`, `dict_val_zh_cn`, `dict_val_zh_tw`) VALUES
(133, 'k_180', '美元', NULL),
(2, 'k_2', '人民币', '人民幣'),
(3, 'k_3', '摩登电子商务系统在线演示', '摩登電子商務系统在线演示'),
(4, 'k_4', '商品主图', '商品主圖'),
(5, 'k_5', '商品幻灯图', '商品幻燈圖'),
(6, 'k_6', '商品描述', '商品描述'),
(7, 'k_7', '商品类别', '商品類別'),
(8, 'k_8', '文章主图', '文章主圖'),
(9, 'k_9', '文章描述', '文章描述'),
(10, 'k_10', '默认用户组', '默認用戶組'),
(11, 'k_11', '摩登电子商务', '摩登電子商務'),
(12, 'k_13', '您的订单已发货', '您的訂單已發貨'),
(13, 'k_15', '您的订单已更新', '您的訂單已更新'),
(14, 'k_17', '恭喜，您获得了一张优惠券', '恭喜，您獲得了一張優惠券'),
(15, 'k_19', '你的订单信息，感谢惠顾', '你的訂單信息，感謝惠顧'),
(16, 'k_21', '注册成功', '注冊成功'),
(17, 'k_23', '重置您的密码', '重置您的密碼'),
(18, 'k_25', '恭喜，您获得了一张优惠券', '恭喜，您獲得了一張優惠券'),
(19, 'k_27', '错误报告', '錯誤報告'),
(20, 'k_29', '新订单通知', '新訂單通知'),
(21, 'k_31', '您的订单支付成功，感谢惠顾', '您的訂單支付成功，感謝惠顧'),
(22, 'k_33', '幻灯片', '幻燈片'),
(24, 'k_38', '帮助', '幫助'),
(25, 'k_39', '关于', '關于'),
(26, 'k_40', '关于我们', '關于我們'),
(27, 'k_42', '如何付款', '如何付款'),
(28, 'k_44', '联系我们', '聯系我們'),
(134, 'k_181', '您的订单已退款', NULL),
(108, 'k_156', '关于我们', '关于我们'),
(109, 'k_158', '联系我们', '联系我们'),
(110, 'k_160', '联系我们', '联系我们'),
(111, 'k_161', '关于我们', '关于我们');

CREATE TABLE IF NOT EXISTS `mm_dict_keys` (
  `id` int(10) NOT NULL auto_increment,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=200 ;

INSERT INTO `mm_dict_keys` (`id`) VALUES
(1),
(2),
(3),
(4),
(5),
(6),
(7),
(8),
(9),
(10),
(11),
(12),
(13),
(14),
(15),
(16),
(17),
(18),
(19),
(20),
(21),
(22),
(23),
(24),
(25),
(26),
(27),
(28),
(29),
(30),
(31),
(32),
(33),
(34),
(35),
(36),
(37),
(38),
(39),
(40),
(41),
(42),
(43),
(44),
(45),
(46),
(47),
(48),
(49),
(50),
(51),
(52),
(53),
(54),
(55),
(56),
(57),
(58),
(59),
(60),
(61),
(62),
(63),
(64),
(65),
(66),
(67),
(68),
(69),
(70),
(71),
(72),
(73),
(74),
(75),
(76),
(77),
(78),
(79),
(80),
(81),
(82),
(83),
(84),
(85),
(86),
(87),
(88),
(89),
(90),
(91),
(92),
(93),
(94),
(95),
(96),
(97),
(98),
(99),
(100),
(101),
(102),
(103),
(104),
(105),
(106),
(107),
(108),
(109),
(110),
(111),
(112),
(113),
(114),
(115),
(116),
(117),
(118),
(119),
(120),
(121),
(122),
(123),
(124),
(125),
(126),
(127),
(128),
(129),
(130),
(131),
(132),
(133),
(134),
(135),
(136),
(137),
(138),
(139),
(140),
(141),
(142),
(143),
(144),
(145),
(146),
(147),
(148),
(149),
(150),
(151),
(152),
(153),
(154),
(155),
(156),
(157),
(158),
(159),
(160),
(161),
(162),
(163),
(164),
(165),
(166),
(167),
(168),
(169),
(170),
(171),
(172),
(173),
(174),
(175),
(176),
(177),
(178),
(179),
(180),
(181),
(182);

CREATE TABLE IF NOT EXISTS `mm_dict_text` (
  `id` int(10) NOT NULL auto_increment,
  `text_key` char(10) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `test_key` (`text_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='text dictionary' AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_dict_text_zh_cn` (
  `id` int(10) NOT NULL auto_increment,
  `text_key` char(10) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `test_key` (`text_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='text dictionary' AUTO_INCREMENT=44 ;

INSERT INTO `mm_dict_text_zh_cn` (`id`, `text_key`, `content`) VALUES
(1, 'k_12', '摩登电子商务'),
(2, 'k_14', 'You order has been shiped'),
(3, 'k_16', 'You order has been updated'),
(4, 'k_18', '<span id="result_box" class="short_text" lang="en"><span class="hps">Congratulations</span><span class="">,</span> <span class="hps">you get</span> <span class="hps">a</span> <span class="hps">coupon</span></span>'),
(5, 'k_20', '<span id="result_box" class="short_text" lang="en"><span class="hps">Order information,</span> <span class="hps">thank</span> <span class="hps">patrons</span></span>'),
(6, 'k_22', '<span id="result_box" class="short_text" lang="en"><span class="hps">Successful registration</span></span>'),
(7, 'k_24', '<span id="result_box" class="short_text" lang="en"><span class="hps">亲爱的 {$user[''firstname'']},<br />\r\n&nbsp;&nbsp;&nbsp; 您的临时密码是: {$user[''password'']}<br />\r\n&nbsp;&nbsp; &nbsp;<br />\r\n<span id="result_box" class="short_text" lang="zh-CN"><span class="">诚挚的问候</span></span>,<br />\r\n摩登电子商务小组</span><span class="hps"></span></span>'),
(8, 'k_26', 'Congratulations, you get a coupon'),
(9, 'k_28', '<span id="result_box" class="short_text" lang="en"><span class="hps">Error Reporting</span></span>'),
(10, 'k_30', '<span id="result_box" class="short_text" lang="en"><span class="hps">New</span> <span class="hps">order notification</span></span>'),
(11, 'k_32', '<span id="result_box" class="short_text" lang="en"><span class="hps">Successful payment</span><span>,</span> <span class="hps">thank</span> <span class="hps">patrons</span></span>'),
(12, 'k_35', '800-888888'),
(13, 'k_36', 'some links'),
(14, 'k_37', 'Copyright ©2013 Mallmold Ecommerce(HK) Limited. All Rights Reserved'),
(15, 'k_41', 'Some About us'),
(16, 'k_43', '<p>\r\n	how-to-pay\r\n</p>\r\n<p>\r\n	how-to-pay\r\n</p>'),
(17, 'k_45', '<span id="result_box" class="short_text" lang="en"><span class="hps">Contact Us</span></span>'),
(43, 'k_182', '您的订单已退款'),
(41, 'k_157', 'About Us'),
(42, 'k_159', '<span id="result_box" class="short_text" lang="en"><span class="hps">Contact Us</span></span>');

CREATE TABLE IF NOT EXISTS `mm_dict_text_zh_tw` (
  `id` int(10) NOT NULL auto_increment,
  `text_key` char(10) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `test_key` (`text_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='text dictionary' AUTO_INCREMENT=15 ;

INSERT INTO `mm_dict_text_zh_tw` (`id`, `text_key`, `content`) VALUES
(1, 'k_12', '摩登電子商務'),
(2, 'k_14', 'You order has been shiped'),
(3, 'k_16', 'You order has been updated'),
(4, 'k_18', '恭喜，您獲得了一張優惠券'),
(5, 'k_20', '你的訂單信息，感謝惠顧'),
(6, 'k_22', '注冊成功'),
(7, 'k_24', '重置您的密碼'),
(8, 'k_26', '恭喜，您獲得了一張優惠券'),
(9, 'k_28', '錯誤報告'),
(10, 'k_30', '新訂單通知'),
(11, 'k_32', '您的訂單支付成功，感謝惠顧'),
(12, 'k_35', '800-888888'),
(13, 'k_36', 'some links'),
(14, 'k_37', 'Copyright &copy;2013 Mallmold Ecommerce(HK) Limited. All Rights Reserved');

CREATE TABLE IF NOT EXISTS `mm_discount` (
  `id` int(4) NOT NULL auto_increment,
  `title_key_` varchar(10) NOT NULL,
  `type` tinyint(1) NOT NULL default '1',
  `val` float(6,2) NOT NULL,
  `can_coupon` tinyint(1) NOT NULL default '0',
  `priority` int(4) NOT NULL,
  `starttime` int(11) NOT NULL default '0',
  `endtime` int(11) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_discount_set` (
  `id` int(8) NOT NULL auto_increment,
  `discount_id` int(4) NOT NULL,
  `item` varchar(16) NOT NULL,
  `logic` varchar(4) NOT NULL,
  `item_val` varchar(64) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_email_log` (
  `id` int(10) NOT NULL auto_increment,
  `email` varchar(32) NOT NULL,
  `title` varchar(128) NOT NULL,
  `content` text NOT NULL,
  `time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  `error` varchar(128) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_email_template` (
  `name` varchar(32) NOT NULL,
  `type` varchar(8) NOT NULL default 'backend',
  `path` varchar(64) NOT NULL,
  `title_key_` varchar(10) NOT NULL,
  `content_txtkey_` varchar(10) NOT NULL,
  KEY `name` (`name`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `mm_email_template` (`name`, `type`, `path`, `title_key_`, `content_txtkey_`) VALUES
('order_ship', 'backend', 'order_ship.html', 'k_13', 'k_14'),
('order_update', 'backend', 'order_update.html', 'k_15', 'k_16'),
('send_coupon', 'backend', 'coupon.html', 'k_17', 'k_18'),
('new_order', 'frontend', 'neworder.html', 'k_19', 'k_20'),
('new_customer', 'frontend', 'newcustomer.html', 'k_21', 'k_22'),
('resetpassword', 'frontend', 'resetpassword.html', 'k_23', 'k_24'),
('autosend_coupon', 'frontend', 'coupon.html', 'k_25', 'k_26'),
('error_report', 'frontend', 'error_report.html', 'k_27', 'k_28'),
('new_order_admin', 'frontend', 'new_order_admin.html', 'k_29', 'k_30'),
('order_pay', 'frontend', 'order_pay.html', 'k_31', 'k_32'),
('order_refund', 'backend', 'order_refund.html', 'k_181', 'k_182');

CREATE TABLE IF NOT EXISTS `mm_error_report` (
  `id` int(10) NOT NULL auto_increment,
  `type` varchar(16) NOT NULL,
  `message` varchar(255) NOT NULL,
  `uri` varchar(1024) default NULL,
  `time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_extend` (
  `extend_id` int(4) NOT NULL auto_increment,
  `code` varchar(32) NOT NULL,
  `name_key_` varchar(10) NOT NULL,
  `type` tinyint(1) NOT NULL default '1',
  `sort_order` int(4) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`extend_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_extend_val` (
  `id` int(6) NOT NULL auto_increment,
  `extend_id` int(4) NOT NULL,
  `val_key_` varchar(10) NOT NULL,
  `sort_order` int(6) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `extend_id` (`extend_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_goods` (
  `goods_id` int(8) NOT NULL auto_increment,
  `title_key_` varchar(10) NOT NULL,
  `urlkey` varchar(255) default NULL,
  `sku` varchar(32) default NULL,
  `price_origin` decimal(8,2) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `weight` int(10) NOT NULL default '0',
  `brief_txtkey_` varchar(10) default NULL,
  `description_txtkey_` varchar(10) default NULL,
  `meta_title_key_` varchar(10) default NULL,
  `meta_keywords_txtkey_` varchar(10) default NULL,
  `meta_description_txtkey_` varchar(10) default NULL,
  `image` varchar(64) default NULL,
  `stock` int(8) NOT NULL default '0',
  `sold_num` int(8) NOT NULL default '0',
  `score` tinyint(1) NOT NULL default '0',
  `is_sale` tinyint(1) NOT NULL default '1',
  `sort_order` int(8) NOT NULL default '0',
  `addtime` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_goods_attr` (
  `goods_id` int(8) NOT NULL,
  `attr_id` int(4) NOT NULL,
  `av_id` int(8) NOT NULL,
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `mm_goods_cate` (
  `id` int(4) NOT NULL auto_increment,
  `pid` int(4) NOT NULL default '0',
  `name_key_` varchar(10) NOT NULL,
  `description_txtkey_` varchar(10) default NULL,
  `meta_title_key_` varchar(10) default NULL,
  `meta_keywords_txtkey_` varchar(10) default NULL,
  `meta_description_txtkey_` varchar(10) default NULL,
  `urlkey` varchar(32) default NULL,
  `image` varchar(64) default NULL,
  `sort_order` int(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_goods_cate_val` (
  `goods_id` int(8) NOT NULL,
  `cate_id` int(4) NOT NULL,
  KEY `goods_id` (`goods_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `mm_goods_extend` (
  `goods_id` int(8) NOT NULL,
  `extend_id` int(4) NOT NULL,
  `val` varchar(128) NOT NULL,
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `mm_goods_image` (
  `id` int(10) NOT NULL auto_increment,
  `goods_id` int(8) NOT NULL,
  `image` varchar(64) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_goods_option` (
  `id` int(8) NOT NULL auto_increment,
  `goods_id` int(8) NOT NULL,
  `op_id` int(8) NOT NULL,
  `name_key_` varchar(10) NOT NULL,
  `image` varchar(64) default NULL,
  `price` decimal(8,2) NOT NULL default '0.00',
  `stock` int(10) NOT NULL default '0',
  `sort_order` int(8) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_goods_statistic` (
  `goods_id` int(8) NOT NULL,
  `click` int(10) NOT NULL default '0',
  `cart` int(10) NOT NULL default '0',
  `buy` int(10) NOT NULL default '0',
  `delivery` int(10) NOT NULL default '0',
  `refund` int(10) NOT NULL default '0',
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `mm_goods_trend` (
  `id` int(10) NOT NULL auto_increment,
  `month` int(6) NOT NULL,
  `goods_id` int(8) NOT NULL,
  `options` varchar(32) default NULL,
  `quantity` int(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商品动态' AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_host` (
  `id` int(4) NOT NULL auto_increment,
  `host` varchar(32) NOT NULL,
  `template` varchar(16) default NULL,
  `bind_country` int(4) NOT NULL default '0',
  `bind_language` varchar(8) default NULL,
  `bind_currency` char(4) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_images` (
  `id` int(10) NOT NULL auto_increment,
  `type` varchar(16) NOT NULL,
  `dir` varchar(64) NOT NULL,
  `addtime` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_image_setting` (
  `id` int(4) NOT NULL auto_increment,
  `name_key_` varchar(10) NOT NULL,
  `sign` varchar(16) NOT NULL,
  `type` varchar(16) NOT NULL,
  `thumbnails` tinyint(1) NOT NULL default '1',
  `width` int(4) NOT NULL,
  `height` int(4) NOT NULL,
  `watermark` tinyint(1) NOT NULL default '0',
  `watermark_img` varchar(64) default NULL,
  `watermark_pos` tinyint(1) NOT NULL default '5',
  `watermark_alpha` int(3) NOT NULL default '50',
  `if_sys` tinyint(1) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

INSERT INTO `mm_image_setting` (`id`, `name_key_`, `sign`, `type`, `thumbnails`, `width`, `height`, `watermark`, `watermark_img`, `watermark_pos`, `watermark_alpha`, `if_sys`, `status`) VALUES
(1, 'k_4', 'goods_img', 'goods_main_img', 1, 300, 320, 0, '', 1, 0, 1, 1),
(2, 'k_5', 'goods_img_slider', 'goods_imgs', 1, 300, 320, 0, '', 1, 0, 1, 1),
(3, 'k_6', 'goods_desc', 'goods_desc', 0, 0, 0, 0, '', 1, 0, 1, 1),
(4, 'k_7', 'goods_cate', 'goods_cate', 0, 0, 0, 0, '', 1, 0, 1, 1),
(5, 'k_8', 'article_img', 'article_img', 0, 0, 0, 0, '', 1, 0, 1, 1),
(6, 'k_9', 'article_desc', 'article_desc', 0, 0, 0, 0, '', 1, 0, 1, 1),
(7, 'k_33', 'slider_index', 'slider', 1, 180, 62, 0, '', 1, 0, 0, 1);

CREATE TABLE IF NOT EXISTS `mm_keywords` (
  `id` int(10) NOT NULL auto_increment,
  `keyword` varchar(32) NOT NULL,
  `search_num` int(10) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_language` (
  `id` int(2) NOT NULL auto_increment,
  `code` varchar(8) NOT NULL,
  `name` varchar(32) NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `mm_language` (`id`, `code`, `name`, `status`) VALUES
(1, 'zh_cn', '中文(简体)', 1),
(2, 'zh_tw', '中文(繁体)', 0);

CREATE TABLE IF NOT EXISTS `mm_language_code` (
  `id` int(4) NOT NULL auto_increment,
  `code` varchar(8) NOT NULL,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=82 ;

INSERT INTO `mm_language_code` (`id`, `code`, `name`) VALUES
(1, 'af', 'Afrikaans'),
(2, 'ar', 'العربية'),
(3, 'az', 'Azəri'),
(4, 'be', 'бельгійскі'),
(5, 'bg', 'български'),
(6, 'bs', 'bosanski'),
(7, 'ca', 'català'),
(8, 'cs', 'čeština'),
(9, 'cy', 'Cymraeg'),
(10, 'da', 'Dansk'),
(11, 'de', 'Deutsch'),
(12, 'dv', 'Erste Sprache Janvier'),
(13, 'el', 'ελληνικά'),
(14, 'en', 'English'),
(15, 'eo', 'Esperanto'),
(16, 'es', 'lengua española'),
(17, 'et', 'eesti'),
(18, 'eu', 'Euskal'),
(19, 'fa', 'Fass زبان'),
(20, 'fi', 'Suomen kieli'),
(21, 'fo', 'Färsaarten'),
(22, 'fr', 'langue française'),
(23, 'gl', 'Galego'),
(24, 'gu', 'ગુજરાતી'),
(25, 'he', 'હીબ્રુ ભાષા'),
(26, 'hi', 'हिंदी'),
(27, 'hr', 'hrvatski'),
(28, 'hu', 'magyar nyelv'),
(29, 'hy', 'հայերեն'),
(30, 'id', 'bahasa Indonesia'),
(31, 'is', 'Icelandic'),
(32, 'it', 'lingua italiana'),
(33, 'ja', '日本語'),
(34, 'ka', 'საქართველოს'),
(36, 'kn', 'ಕೆನರಾ ಭಾಷೆ'),
(37, 'ko', '한국어'),
(40, 'lt', 'Lietuvos'),
(41, 'lv', 'Latvijas'),
(43, 'mk', 'македонски'),
(45, 'mr', 'मराठी'),
(46, 'ms', 'Melayu'),
(47, 'mt', 'Malti'),
(48, 'nl', 'Nederlands'),
(49, 'no', 'Norsk språk'),
(52, 'pl', 'polski'),
(53, 'pt', 'português'),
(55, 'ro', 'român'),
(56, 'ru', 'русский'),
(59, 'sk', 'Slovenský jazyk'),
(60, 'sl', 'slovenščina'),
(61, 'sq', 'shqiptar'),
(62, 'sr', 'Српски језик'),
(63, 'sv', 'svensk'),
(64, 'sw', 'lugha ya Kiswahili'),
(66, 'ta', 'தமிழ் மொழி'),
(67, 'te', 'తెలుగు'),
(68, 'th', 'ภาษาไทย'),
(69, 'tl', 'Pilipino'),
(71, 'tr', 'Türk dili'),
(74, 'uk', 'Український'),
(75, 'ur', 'اردو زبان'),
(77, 'vi', 'tiếng Việt'),
(79, 'zh_cn', '中文(简体)'),
(80, 'zh_tw', '中文(繁体)');

CREATE TABLE IF NOT EXISTS `mm_nav` (
  `id` int(4) NOT NULL auto_increment,
  `type` tinyint(1) NOT NULL default '2',
  `title_key_` varchar(10) NOT NULL,
  `url` varchar(255) NOT NULL,
  `sort_order` int(4) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `mm_nav` (`id`, `type`, `title_key_`, `url`, `sort_order`, `status`) VALUES
(1, 2, 'k_160', '/index.php/page/contact-us', 1, 1),
(2, 3, 'k_161', '/index.php/page/about-us', 1, 1);

CREATE TABLE IF NOT EXISTS `mm_option` (
  `op_id` int(8) NOT NULL auto_increment,
  `name_key_` varchar(10) default NULL,
  `sort_order` int(8) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`op_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_order` (
  `order_id` int(10) NOT NULL auto_increment,
  `order_sn` varchar(16) NOT NULL,
  `user_id` int(10) NOT NULL,
  `email` varchar(32) NOT NULL,
  `shipping_id` int(4) NOT NULL,
  `payment_id` int(2) NOT NULL,
  `coupon_id` int(10) NOT NULL default '0',
  `currency` char(4) NOT NULL,
  `language` varchar(8) NOT NULL,
  `goods_amount` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL default '0.00',
  `tax_fee` decimal(10,2) NOT NULL default '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `gift` decimal(8,2) NOT NULL default '0.00',
  `addtime` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `shipping_status` tinyint(1) NOT NULL default '0',
  `refund` decimal(10,2) NOT NULL default '0.00',
  PRIMARY KEY  (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_order_goods` (
  `id` int(10) NOT NULL auto_increment,
  `order_id` int(10) NOT NULL,
  `goods_id` int(10) NOT NULL,
  `goods_name` varchar(255) NOT NULL,
  `options` varchar(256) default NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(4) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping` int(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_order_ship` (
  `id` int(10) NOT NULL auto_increment,
  `type` tinyint(1) NOT NULL default '1',
  `order_id` int(10) NOT NULL,
  `order_sn` varchar(16) NOT NULL,
  `ship_sn` varchar(32) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_order_shipping_address` (
  `order_id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `fullname` varchar(16) NOT NULL,
  `country_id` int(4) NOT NULL,
  `region_id` int(8) NOT NULL,
  `city` varchar(32) NOT NULL,
  `address` varchar(128) NOT NULL,
  `postcode` varchar(8) NOT NULL,
  `phone` varchar(24) default NULL,
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `mm_order_ship_goods` (
  `ship_id` int(10) NOT NULL,
  `goods_id` int(10) NOT NULL,
  `sku` varchar(32) default NULL,
  `title` varchar(256) NOT NULL,
  `options` varchar(128) NOT NULL,
  `quantity` int(8) NOT NULL,
  KEY `ship_id` (`ship_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `mm_order_sn` (
  `sn` int(8) NOT NULL default '1000000',
  KEY `sn` (`sn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `mm_order_sn` (`sn`) VALUES
(1000000);

CREATE TABLE IF NOT EXISTS `mm_order_status` (
  `id` int(10) NOT NULL auto_increment,
  `order_id` int(10) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `remark` varchar(256) NOT NULL,
  `notice` tinyint(1) NOT NULL default '0',
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_pages` (
  `id` int(4) NOT NULL auto_increment,
  `urlkey` varchar(64) NOT NULL,
  `title_key_` varchar(10) NOT NULL,
  `content_txtkey_` varchar(10) NOT NULL,
  `image` varchar(64) NOT NULL,
  `sort_order` int(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `mm_pages` (`id`, `urlkey`, `title_key_`, `content_txtkey_`, `image`, `sort_order`) VALUES
(1, 'about-us', 'k_156', 'k_157', '', 2),
(2, 'contact-us', 'k_158', 'k_159', '', 1);

CREATE TABLE IF NOT EXISTS `mm_payment` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `description` varchar(64) default NULL,
  `model` varchar(16) NOT NULL,
  `sort_order` int(2) NOT NULL default '0',
  `bind` tinyint(1) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

INSERT INTO `mm_payment` (`id`, `name`, `description`, `model`, `sort_order`, `bind`, `status`) VALUES
(1, '支付宝', '支付宝即时到帐', 'alipay', 1, 0, 1),
(2, '财付通', '财付通支付', 'tenpay', 2, 0, 1),
(3, '银联在线支付', '银联在线快速支付', 'unionpay', 3, 0, 1),
(4, 'Paypal', 'Paypal(Website Payments Standard)', 'paypal', 4, 0, 1),
(5, 'Credit card', 'Authorize.net(Advanced Integration Method)', 'authorize', 5, 0, 0);

CREATE TABLE IF NOT EXISTS `mm_payment_alipay` (
  `id` tinyint(1) NOT NULL auto_increment,
  `test_mode` tinyint(1) NOT NULL default '0',
  `seller_email` varchar(32) NOT NULL,
  `partner` varchar(16) default NULL,
  `key` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `mm_payment_alipay` (`id`, `test_mode`, `seller_email`, `partner`, `key`) VALUES
(1, 0, 'test@mallmold.com', '12345678', 'ffmhdgrdggg');

CREATE TABLE IF NOT EXISTS `mm_payment_authorize` (
  `id` tinyint(1) NOT NULL auto_increment,
  `test_mode` tinyint(1) NOT NULL default '0',
  `api_id` varchar(32) NOT NULL,
  `api_key` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `mm_payment_authorize` (`id`, `test_mode`, `api_id`, `api_key`) VALUES
(1, 1, '6nrM7QzAM6z', '2Z3kT2wmLW62dB6t');

CREATE TABLE IF NOT EXISTS `mm_payment_bind` (
  `payment_id` int(2) NOT NULL,
  `country_id` int(4) NOT NULL,
  KEY `country_id` (`country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `mm_payment_error` (
  `id` int(10) NOT NULL auto_increment,
  `order_id` int(10) NOT NULL default '0',
  `method` varchar(16) NOT NULL,
  `error_msg` text NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_payment_log` (
  `id` int(10) NOT NULL auto_increment,
  `type` tinyint(1) NOT NULL default '1',
  `order_sn` varchar(16) NOT NULL,
  `model` varchar(16) NOT NULL,
  `track_id` varchar(64) NOT NULL,
  `currency` char(4) NOT NULL,
  `money` decimal(10,2) NOT NULL,
  `remark` text,
  `time` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_payment_paypal` (
  `id` tinyint(1) NOT NULL auto_increment,
  `test_mode` tinyint(1) NOT NULL default '0',
  `email` varchar(32) NOT NULL,
  `user` varchar(32) default NULL,
  `password` varchar(32) default NULL,
  `signature` varchar(32) default NULL,
  `type` tinyint(1) NOT NULL default '1',
  `paypal_cert_id` varchar(32) default NULL,
  `paypal_cert_file` varchar(32) default NULL,
  `my_public_cert_file` varchar(32) default NULL,
  `my_private_key_file` varchar(32) default NULL,
  `my_private_key_pswd` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `mm_payment_paypal` (`id`, `test_mode`, `email`, `user`, `password`, `signature`, `type`, `paypal_cert_id`, `paypal_cert_file`, `my_public_cert_file`, `my_private_key_file`, `my_private_key_pswd`) VALUES
(1, 1, 'mallmold-facilitator@gmail.com', '', '', '', 1, '', '', '', '', '');

CREATE TABLE IF NOT EXISTS `mm_payment_tenpay` (
  `id` tinyint(1) NOT NULL auto_increment,
  `test_mode` tinyint(1) NOT NULL default '0',
  `appid` varchar(16) default NULL,
  `key` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `mm_payment_tenpay` (`id`, `test_mode`, `appid`, `key`) VALUES
(1, 0, '1234', '3564353777');

CREATE TABLE IF NOT EXISTS `mm_payment_unionpay` (
  `id` tinyint(1) NOT NULL auto_increment,
  `test_mode` tinyint(1) NOT NULL default '0',
  `merid` varchar(32) default NULL,
  `mercode` varchar(16) default NULL,
  `merabbr` varchar(256) NOT NULL,
  `security_key` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `mm_payment_unionpay` (`id`, `test_mode`, `merid`, `mercode`, `merabbr`, `security_key`) VALUES
(1, 1, '105550149170027', '', '香港摩登电子商务有限公司', '88888888');

CREATE TABLE IF NOT EXISTS `mm_region` (
  `region_id` int(8) NOT NULL auto_increment,
  `country_id` int(4) NOT NULL default '1',
  `code` char(2) default NULL,
  `name` varchar(64) NOT NULL,
  `sort_order` int(8) NOT NULL default '0',
  PRIMARY KEY  (`region_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=36 ;

INSERT INTO `mm_region` (`region_id`, `country_id`, `code`, `name`, `sort_order`) VALUES
(1, 1, NULL, '北京', 0),
(2, 1, NULL, '安徽', 0),
(3, 1, NULL, '福建', 0),
(4, 1, NULL, '甘肃', 0),
(5, 1, NULL, '广东', 0),
(6, 1, NULL, '广西', 0),
(7, 1, NULL, '贵州', 0),
(8, 1, NULL, '海南', 0),
(9, 1, NULL, '河北', 0),
(10, 1, NULL, '河南', 0),
(11, 1, NULL, '黑龙江', 0),
(12, 1, NULL, '湖北', 0),
(13, 1, NULL, '湖南', 0),
(14, 1, NULL, '吉林', 0),
(15, 1, NULL, '江苏', 0),
(16, 1, NULL, '江西', 0),
(17, 1, NULL, '辽宁', 0),
(18, 1, NULL, '内蒙古', 0),
(19, 1, NULL, '宁夏', 0),
(20, 1, NULL, '青海', 0),
(21, 1, NULL, '山东', 0),
(22, 1, NULL, '山西', 0),
(23, 1, NULL, '陕西', 0),
(24, 1, NULL, '上海', 0),
(25, 1, NULL, '四川', 0),
(26, 1, NULL, '天津', 0),
(27, 1, NULL, '西藏', 0),
(28, 1, NULL, '新疆', 0),
(29, 1, NULL, '云南', 0),
(30, 1, NULL, '浙江', 0),
(31, 1, NULL, '重庆', 0),
(32, 1, NULL, '香港', 0),
(33, 1, NULL, '澳门', 0),
(34, 1, NULL, '台湾', 0),
(35, 1, '', '北京', 0);

CREATE TABLE IF NOT EXISTS `mm_region_city` (
  `city_id` int(10) NOT NULL auto_increment,
  `region_id` int(8) NOT NULL,
  `name` varchar(32) NOT NULL,
  `postcode` char(8) NOT NULL,
  `sort_order` int(10) NOT NULL default '0',
  PRIMARY KEY  (`city_id`),
  KEY `region_id` (`region_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_region_city_cn` (
  `city_id` int(10) NOT NULL auto_increment,
  `region_id` int(8) NOT NULL,
  `name` varchar(32) NOT NULL,
  `postcode` char(8) default NULL,
  `sort_order` int(10) NOT NULL default '0',
  PRIMARY KEY  (`city_id`),
  KEY `region_id` (`region_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=472 ;

INSERT INTO `mm_region_city_cn` (`city_id`, `region_id`, `name`, `postcode`, `sort_order`) VALUES
(1, 1, '东城区', NULL, 0),
(2, 1, '西城区', NULL, 0),
(3, 1, '海淀区', NULL, 0),
(4, 1, '朝阳区', NULL, 0),
(5, 1, '崇文区', NULL, 0),
(6, 1, '宣武区', NULL, 0),
(7, 1, '丰台区', NULL, 0),
(8, 1, '石景山区', NULL, 0),
(9, 1, '房山区', NULL, 0),
(10, 1, '门头沟区', NULL, 0),
(11, 1, '通州区', NULL, 0),
(12, 1, '顺义区', NULL, 0),
(13, 1, '昌平区', NULL, 0),
(14, 1, '怀柔区', NULL, 0),
(15, 1, '平谷区', NULL, 0),
(16, 1, '大兴区', NULL, 0),
(17, 1, '密云县', NULL, 0),
(18, 1, '延庆县', NULL, 0),
(19, 2, '安庆', NULL, 0),
(20, 2, '蚌埠', NULL, 0),
(21, 2, '巢湖', NULL, 0),
(22, 2, '池州', NULL, 0),
(23, 2, '滁州', NULL, 0),
(24, 2, '阜阳', NULL, 0),
(25, 2, '淮北', NULL, 0),
(26, 2, '淮南', NULL, 0),
(27, 2, '黄山', NULL, 0),
(28, 2, '六安', NULL, 0),
(29, 2, '马鞍山', NULL, 0),
(30, 2, '宿州', NULL, 0),
(31, 2, '铜陵', NULL, 0),
(32, 2, '芜湖', NULL, 0),
(33, 2, '宣城', NULL, 0),
(34, 2, '亳州', NULL, 0),
(35, 2, '合肥', NULL, 0),
(36, 3, '福州', NULL, 0),
(37, 3, '龙岩', NULL, 0),
(38, 3, '南平', NULL, 0),
(39, 3, '宁德', NULL, 0),
(40, 3, '莆田', NULL, 0),
(41, 3, '泉州', NULL, 0),
(42, 3, '三明', NULL, 0),
(43, 3, '厦门', NULL, 0),
(44, 3, '漳州', NULL, 0),
(45, 4, '兰州', NULL, 0),
(46, 4, '白银', NULL, 0),
(47, 4, '定西', NULL, 0),
(48, 4, '甘南', NULL, 0),
(49, 4, '嘉峪关', NULL, 0),
(50, 4, '金昌', NULL, 0),
(51, 4, '酒泉', NULL, 0),
(52, 4, '临夏', NULL, 0),
(53, 4, '陇南', NULL, 0),
(54, 4, '平凉', NULL, 0),
(55, 4, '庆阳', NULL, 0),
(56, 4, '天水', NULL, 0),
(57, 4, '武威', NULL, 0),
(58, 4, '张掖', NULL, 0),
(59, 5, '广州', NULL, 0),
(60, 5, '深圳', NULL, 0),
(61, 5, '潮州', NULL, 0),
(62, 5, '东莞', NULL, 0),
(63, 5, '佛山', NULL, 0),
(64, 5, '河源', NULL, 0),
(65, 5, '惠州', NULL, 0),
(66, 5, '江门', NULL, 0),
(67, 5, '揭阳', NULL, 0),
(68, 5, '茂名', NULL, 0),
(69, 5, '梅州', NULL, 0),
(70, 5, '清远', NULL, 0),
(71, 5, '汕头', NULL, 0),
(72, 5, '汕尾', NULL, 0),
(73, 5, '韶关', NULL, 0),
(74, 5, '阳江', NULL, 0),
(75, 5, '云浮', NULL, 0),
(76, 5, '湛江', NULL, 0),
(77, 5, '肇庆', NULL, 0),
(78, 5, '中山', NULL, 0),
(79, 5, '珠海', NULL, 0),
(80, 6, '南宁', NULL, 0),
(81, 6, '桂林', NULL, 0),
(82, 6, '百色', NULL, 0),
(83, 6, '北海', NULL, 0),
(84, 6, '崇左', NULL, 0),
(85, 6, '防城港', NULL, 0),
(86, 6, '贵港', NULL, 0),
(87, 6, '河池', NULL, 0),
(88, 6, '贺州', NULL, 0),
(89, 6, '来宾', NULL, 0),
(90, 6, '柳州', NULL, 0),
(91, 6, '钦州', NULL, 0),
(92, 6, '梧州', NULL, 0),
(93, 6, '玉林', NULL, 0),
(94, 7, '贵阳', NULL, 0),
(95, 7, '安顺', NULL, 0),
(96, 7, '毕节', NULL, 0),
(97, 7, '六盘水', NULL, 0),
(98, 7, '黔东南', NULL, 0),
(99, 7, '黔南', NULL, 0),
(100, 7, '黔西南', NULL, 0),
(101, 7, '铜仁', NULL, 0),
(102, 7, '遵义', NULL, 0),
(103, 8, '海口', NULL, 0),
(104, 8, '三亚', NULL, 0),
(105, 8, '白沙', NULL, 0),
(106, 8, '保亭', NULL, 0),
(107, 8, '昌江', NULL, 0),
(108, 8, '澄迈县', NULL, 0),
(109, 8, '定安县', NULL, 0),
(110, 8, '东方', NULL, 0),
(111, 8, '乐东', NULL, 0),
(112, 8, '临高县', NULL, 0),
(113, 8, '陵水', NULL, 0),
(114, 8, '琼海', NULL, 0),
(115, 8, '琼中', NULL, 0),
(116, 8, '屯昌县', NULL, 0),
(117, 8, '万宁', NULL, 0),
(118, 8, '文昌', NULL, 0),
(119, 8, '五指山', NULL, 0),
(120, 8, '儋州', NULL, 0),
(121, 9, '石家庄', NULL, 0),
(122, 9, '保定', NULL, 0),
(123, 9, '沧州', NULL, 0),
(124, 9, '承德', NULL, 0),
(125, 9, '邯郸', NULL, 0),
(126, 9, '衡水', NULL, 0),
(127, 9, '廊坊', NULL, 0),
(128, 9, '秦皇岛', NULL, 0),
(129, 9, '唐山', NULL, 0),
(130, 9, '邢台', NULL, 0),
(131, 9, '张家口', NULL, 0),
(132, 10, '郑州', NULL, 0),
(133, 10, '洛阳', NULL, 0),
(134, 10, '开封', NULL, 0),
(135, 10, '安阳', NULL, 0),
(136, 10, '鹤壁', NULL, 0),
(137, 10, '济源', NULL, 0),
(138, 10, '焦作', NULL, 0),
(139, 10, '南阳', NULL, 0),
(140, 10, '平顶山', NULL, 0),
(141, 10, '三门峡', NULL, 0),
(142, 10, '商丘', NULL, 0),
(143, 10, '新乡', NULL, 0),
(144, 10, '信阳', NULL, 0),
(145, 10, '许昌', NULL, 0),
(146, 10, '周口', NULL, 0),
(147, 10, '驻马店', NULL, 0),
(148, 10, '漯河', NULL, 0),
(149, 10, '濮阳', NULL, 0),
(150, 11, '哈尔滨', NULL, 0),
(151, 11, '大庆', NULL, 0),
(152, 11, '大兴安岭', NULL, 0),
(153, 11, '鹤岗', NULL, 0),
(154, 11, '黑河', NULL, 0),
(155, 11, '鸡西', NULL, 0),
(156, 11, '佳木斯', NULL, 0),
(157, 11, '牡丹江', NULL, 0),
(158, 11, '七台河', NULL, 0),
(159, 11, '齐齐哈尔', NULL, 0),
(160, 11, '双鸭山', NULL, 0),
(161, 11, '绥化', NULL, 0),
(162, 11, '伊春', NULL, 0),
(163, 12, '武汉', NULL, 0),
(164, 12, '仙桃', NULL, 0),
(165, 12, '鄂州', NULL, 0),
(166, 12, '黄冈', NULL, 0),
(167, 12, '黄石', NULL, 0),
(168, 12, '荆门', NULL, 0),
(169, 12, '荆州', NULL, 0),
(170, 12, '潜江', NULL, 0),
(171, 12, '神农架林区', NULL, 0),
(172, 12, '十堰', NULL, 0),
(173, 12, '随州', NULL, 0),
(174, 12, '天门', NULL, 0),
(175, 12, '咸宁', NULL, 0),
(176, 12, '襄樊', NULL, 0),
(177, 12, '孝感', NULL, 0),
(178, 12, '宜昌', NULL, 0),
(179, 12, '恩施', NULL, 0),
(180, 13, '长沙', NULL, 0),
(181, 13, '张家界', NULL, 0),
(182, 13, '常德', NULL, 0),
(183, 13, '郴州', NULL, 0),
(184, 13, '衡阳', NULL, 0),
(185, 13, '怀化', NULL, 0),
(186, 13, '娄底', NULL, 0),
(187, 13, '邵阳', NULL, 0),
(188, 13, '湘潭', NULL, 0),
(189, 13, '湘西', NULL, 0),
(190, 13, '益阳', NULL, 0),
(191, 13, '永州', NULL, 0),
(192, 13, '岳阳', NULL, 0),
(193, 13, '株洲', NULL, 0),
(194, 14, '长春', NULL, 0),
(195, 14, '吉林', NULL, 0),
(196, 14, '白城', NULL, 0),
(197, 14, '白山', NULL, 0),
(198, 14, '辽源', NULL, 0),
(199, 14, '四平', NULL, 0),
(200, 14, '松原', NULL, 0),
(201, 14, '通化', NULL, 0),
(202, 14, '延边', NULL, 0),
(203, 15, '南京', NULL, 0),
(204, 15, '苏州', NULL, 0),
(205, 15, '无锡', NULL, 0),
(206, 15, '常州', NULL, 0),
(207, 15, '淮安', NULL, 0),
(208, 15, '连云港', NULL, 0),
(209, 15, '南通', NULL, 0),
(210, 15, '宿迁', NULL, 0),
(211, 15, '泰州', NULL, 0),
(212, 15, '徐州', NULL, 0),
(213, 15, '盐城', NULL, 0),
(214, 15, '扬州', NULL, 0),
(215, 15, '镇江', NULL, 0),
(216, 16, '南昌', NULL, 0),
(217, 16, '抚州', NULL, 0),
(218, 16, '赣州', NULL, 0),
(219, 16, '吉安', NULL, 0),
(220, 16, '景德镇', NULL, 0),
(221, 16, '九江', NULL, 0),
(222, 16, '萍乡', NULL, 0),
(223, 16, '上饶', NULL, 0),
(224, 16, '新余', NULL, 0),
(225, 16, '宜春', NULL, 0),
(226, 16, '鹰潭', NULL, 0),
(227, 17, '沈阳', NULL, 0),
(228, 17, '大连', NULL, 0),
(229, 17, '鞍山', NULL, 0),
(230, 17, '本溪', NULL, 0),
(231, 17, '朝阳', NULL, 0),
(232, 17, '丹东', NULL, 0),
(233, 17, '抚顺', NULL, 0),
(234, 17, '阜新', NULL, 0),
(235, 17, '葫芦岛', NULL, 0),
(236, 17, '锦州', NULL, 0),
(237, 17, '辽阳', NULL, 0),
(238, 17, '盘锦', NULL, 0),
(239, 17, '铁岭', NULL, 0),
(240, 17, '营口', NULL, 0),
(241, 18, '呼和浩特', NULL, 0),
(242, 18, '阿拉善盟', NULL, 0),
(243, 18, '巴彦淖尔盟', NULL, 0),
(244, 18, '包头', NULL, 0),
(245, 18, '赤峰', NULL, 0),
(246, 18, '鄂尔多斯', NULL, 0),
(247, 18, '呼伦贝尔', NULL, 0),
(248, 18, '通辽', NULL, 0),
(249, 18, '乌海', NULL, 0),
(250, 18, '乌兰察布市', NULL, 0),
(251, 18, '锡林郭勒盟', NULL, 0),
(252, 18, '兴安盟', NULL, 0),
(253, 19, '银川', NULL, 0),
(254, 19, '固原', NULL, 0),
(255, 19, '石嘴山', NULL, 0),
(256, 19, '吴忠', NULL, 0),
(257, 19, '中卫', NULL, 0),
(258, 20, '西宁', NULL, 0),
(259, 20, '果洛', NULL, 0),
(260, 20, '海北', NULL, 0),
(261, 20, '海东', NULL, 0),
(262, 20, '海南', NULL, 0),
(263, 20, '海西', NULL, 0),
(264, 20, '黄南', NULL, 0),
(265, 20, '玉树', NULL, 0),
(266, 21, '济南', NULL, 0),
(267, 21, '青岛', NULL, 0),
(268, 21, '滨州', NULL, 0),
(269, 21, '德州', NULL, 0),
(270, 21, '东营', NULL, 0),
(271, 21, '菏泽', NULL, 0),
(272, 21, '济宁', NULL, 0),
(273, 21, '莱芜', NULL, 0),
(274, 21, '聊城', NULL, 0),
(275, 21, '临沂', NULL, 0),
(276, 21, '日照', NULL, 0),
(277, 21, '泰安', NULL, 0),
(278, 21, '威海', NULL, 0),
(279, 21, '潍坊', NULL, 0),
(280, 21, '烟台', NULL, 0),
(281, 21, '枣庄', NULL, 0),
(282, 21, '淄博', NULL, 0),
(283, 22, '太原', NULL, 0),
(284, 22, '长治', NULL, 0),
(285, 22, '大同', NULL, 0),
(286, 22, '晋城', NULL, 0),
(287, 22, '晋中', NULL, 0),
(288, 22, '临汾', NULL, 0),
(289, 22, '吕梁', NULL, 0),
(290, 22, '朔州', NULL, 0),
(291, 22, '忻州', NULL, 0),
(292, 22, '阳泉', NULL, 0),
(293, 22, '运城', NULL, 0),
(294, 23, '西安', NULL, 0),
(295, 23, '安康', NULL, 0),
(296, 23, '宝鸡', NULL, 0),
(297, 23, '汉中', NULL, 0),
(298, 23, '商洛', NULL, 0),
(299, 23, '铜川', NULL, 0),
(300, 23, '渭南', NULL, 0),
(301, 23, '咸阳', NULL, 0),
(302, 23, '延安', NULL, 0),
(303, 23, '榆林', NULL, 0),
(304, 24, '长宁区', NULL, 0),
(305, 24, '闸北区', NULL, 0),
(306, 24, '闵行区', NULL, 0),
(307, 24, '徐汇区', NULL, 0),
(308, 24, '浦东新区', NULL, 0),
(309, 24, '杨浦区', NULL, 0),
(310, 24, '普陀区', NULL, 0),
(311, 24, '静安区', NULL, 0),
(312, 24, '卢湾区', NULL, 0),
(313, 24, '虹口区', NULL, 0),
(314, 24, '黄浦区', NULL, 0),
(315, 24, '南汇区', NULL, 0),
(316, 24, '松江区', NULL, 0),
(317, 24, '嘉定区', NULL, 0),
(318, 24, '宝山区', NULL, 0),
(319, 24, '青浦区', NULL, 0),
(320, 24, '金山区', NULL, 0),
(321, 24, '奉贤区', NULL, 0),
(322, 24, '崇明县', NULL, 0),
(323, 25, '成都', NULL, 0),
(324, 25, '绵阳', NULL, 0),
(325, 25, '阿坝', NULL, 0),
(326, 25, '巴中', NULL, 0),
(327, 25, '达州', NULL, 0),
(328, 25, '德阳', NULL, 0),
(329, 25, '甘孜', NULL, 0),
(330, 25, '广安', NULL, 0),
(331, 25, '广元', NULL, 0),
(332, 25, '乐山', NULL, 0),
(333, 25, '凉山', NULL, 0),
(334, 25, '眉山', NULL, 0),
(335, 25, '南充', NULL, 0),
(336, 25, '内江', NULL, 0),
(337, 25, '攀枝花', NULL, 0),
(338, 25, '遂宁', NULL, 0),
(339, 25, '雅安', NULL, 0),
(340, 25, '宜宾', NULL, 0),
(341, 25, '资阳', NULL, 0),
(342, 25, '自贡', NULL, 0),
(343, 25, '泸州', NULL, 0),
(344, 26, '天津', NULL, 0),
(345, 27, '拉萨', NULL, 0),
(346, 27, '阿里', NULL, 0),
(347, 27, '昌都', NULL, 0),
(348, 27, '林芝', NULL, 0),
(349, 27, '那曲', NULL, 0),
(350, 27, '日喀则', NULL, 0),
(351, 27, '山南', NULL, 0),
(352, 28, '乌鲁木齐', NULL, 0),
(353, 28, '阿克苏', NULL, 0),
(354, 28, '阿拉尔', NULL, 0),
(355, 28, '巴音郭楞', NULL, 0),
(356, 28, '博尔塔拉', NULL, 0),
(357, 28, '昌吉', NULL, 0),
(358, 28, '哈密', NULL, 0),
(359, 28, '和田', NULL, 0),
(360, 28, '喀什', NULL, 0),
(361, 28, '克拉玛依', NULL, 0),
(362, 28, '克孜勒苏', NULL, 0),
(363, 28, '石河子', NULL, 0),
(364, 28, '图木舒克', NULL, 0),
(365, 28, '吐鲁番', NULL, 0),
(366, 28, '五家渠', NULL, 0),
(367, 28, '伊犁', NULL, 0),
(368, 29, '昆明', NULL, 0),
(369, 29, '怒江', NULL, 0),
(370, 29, '普洱', NULL, 0),
(371, 29, '丽江', NULL, 0),
(372, 29, '保山', NULL, 0),
(373, 29, '楚雄', NULL, 0),
(374, 29, '大理', NULL, 0),
(375, 29, '德宏', NULL, 0),
(376, 29, '迪庆', NULL, 0),
(377, 29, '红河', NULL, 0),
(378, 29, '临沧', NULL, 0),
(379, 29, '曲靖', NULL, 0),
(380, 29, '文山', NULL, 0),
(381, 29, '西双版纳', NULL, 0),
(382, 29, '玉溪', NULL, 0),
(383, 29, '昭通', NULL, 0),
(384, 30, '杭州', NULL, 0),
(385, 30, '湖州', NULL, 0),
(386, 30, '嘉兴', NULL, 0),
(387, 30, '金华', NULL, 0),
(388, 30, '丽水', NULL, 0),
(389, 30, '宁波', NULL, 0),
(390, 30, '绍兴', NULL, 0),
(391, 30, '台州', NULL, 0),
(392, 30, '温州', NULL, 0),
(393, 30, '舟山', NULL, 0),
(394, 30, '衢州', NULL, 0),
(395, 30, '义乌', NULL, 0),
(396, 31, '合川区', NULL, 0),
(397, 31, '江津区', NULL, 0),
(398, 31, '南川区', NULL, 0),
(399, 31, '永川区', NULL, 0),
(400, 31, '南岸区', NULL, 0),
(401, 31, '渝北区', NULL, 0),
(402, 31, '万盛区', NULL, 0),
(403, 31, '大渡口区', NULL, 0),
(404, 31, '万州区', NULL, 0),
(405, 31, '北碚区', NULL, 0),
(406, 31, '沙坪坝区', NULL, 0),
(407, 31, '巴南区', NULL, 0),
(408, 31, '涪陵区', NULL, 0),
(409, 31, '江北区', NULL, 0),
(410, 31, '九龙坡区', NULL, 0),
(411, 31, '渝中区', NULL, 0),
(412, 31, '黔江开发区', NULL, 0),
(413, 31, '长寿区', NULL, 0),
(414, 31, '双桥区', NULL, 0),
(415, 31, '綦江县', NULL, 0),
(416, 31, '潼南县', NULL, 0),
(417, 31, '铜梁县', NULL, 0),
(418, 31, '大足县', NULL, 0),
(419, 31, '荣昌县', NULL, 0),
(420, 31, '璧山县', NULL, 0),
(421, 31, '垫江县', NULL, 0),
(422, 31, '武隆县', NULL, 0),
(423, 31, '丰都县', NULL, 0),
(424, 31, '城口县', NULL, 0),
(425, 31, '梁平县', NULL, 0),
(426, 31, '开县', NULL, 0),
(427, 31, '巫溪县', NULL, 0),
(428, 31, '巫山县', NULL, 0),
(429, 31, '奉节县', NULL, 0),
(430, 31, '云阳县', NULL, 0),
(431, 31, '忠县', NULL, 0),
(432, 31, '石柱', NULL, 0),
(433, 31, '彭水', NULL, 0),
(434, 31, '酉阳', NULL, 0),
(435, 31, '秀山', NULL, 0),
(436, 32, '沙田区', NULL, 0),
(437, 32, '东区', NULL, 0),
(438, 32, '观塘区', NULL, 0),
(439, 32, '黄大仙区', NULL, 0),
(440, 32, '九龙城区', NULL, 0),
(441, 32, '屯门区', NULL, 0),
(442, 32, '葵青区', NULL, 0),
(443, 32, '元朗区', NULL, 0),
(444, 32, '深水埗区', NULL, 0),
(445, 32, '西贡区', NULL, 0),
(446, 32, '大埔区', NULL, 0),
(447, 32, '湾仔区', NULL, 0),
(448, 32, '油尖旺区', NULL, 0),
(449, 32, '北区', NULL, 0),
(450, 32, '南区', NULL, 0),
(451, 32, '荃湾区', NULL, 0),
(452, 32, '中西区', NULL, 0),
(453, 32, '离岛区', NULL, 0),
(454, 33, '澳门', NULL, 0),
(455, 34, '台北', NULL, 0),
(456, 34, '高雄', NULL, 0),
(457, 34, '基隆', NULL, 0),
(458, 34, '台中', NULL, 0),
(459, 34, '台南', NULL, 0),
(460, 34, '新竹', NULL, 0),
(461, 34, '嘉义', NULL, 0),
(462, 34, '宜兰县', NULL, 0),
(463, 34, '桃园县', NULL, 0),
(464, 34, '苗栗县', NULL, 0),
(465, 34, '彰化县', NULL, 0),
(466, 34, '南投县', NULL, 0),
(467, 34, '云林县', NULL, 0),
(468, 34, '屏东县', NULL, 0),
(469, 34, '台东县', NULL, 0),
(470, 34, '花莲县', NULL, 0),
(471, 34, '澎湖县', NULL, 0);

CREATE TABLE IF NOT EXISTS `mm_setting` (
  `name` varchar(32) NOT NULL,
  `val` varchar(128) default NULL,
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `mm_setting` (`name`, `val`) VALUES
('web_name_key_', 'k_3'),
('meta_keywords_key_', 'k_11'),
('meta_description_txtkey_', 'k_12'),
('default_host', '127.0.01'),
('frontend', 'app'),
('default_tpl', 'default'),
('default_country', '1'),
('default_timezone', '8'),
('default_lang', 'zh_cn'),
('default_cur', 'RMB'),
('weight_unit', 'k'),
('goods_order', 'sort_order'),
('show_unsale', '1'),
('pre_sale', '1'),
('comment_accept', '1'),
('goods_index_sid', '1'),
('goods_list_sid', '1'),
('goods_view_sid', '1'),
('goods_imgs_big_sid', '2'),
('goods_imgs_small_sid', '2'),
('order_prefix', '10000'),
('date_format', 'Y/m/d'),
('coupon_expire_day', '0'),
('visitor_order', '1'),
('user_order_notice', '0'),
('user_pay_notice', '0'),
('user_ship_notice', '0'),
('user_refund_notice', '0'),
('use_agent_lang', '1'),
('user_register_notice', '1'),
('user_default_group', '1'),
('register_verify', '0'),
('login_verify', '0'),
('spider_code', 'Googlebot|msnbot|bing'),
('smtp_host', 'smtp.sina.com'),
('smtp_port', '25'),
('smtp_user', 'mallmold'),
('smtp_pswd', 'mallmold'),
('email_log', '0'),
('admin_order_notice', '0'),
('admin_order_notice_email', ''),
('admin_error_notice', '0'),
('admin_error_notice_email', ''),
('admin_login_verify', '0'),
('data_cache', '1'),
('tpl_cache', '1'),
('dict_cache', ''),
('redis_host', ''),
('redis_port', ''),
('redis_pswd', ''),
('memcache_host', ''),
('memcache_port', ''),
('web_logo', '/upload/image/201307/17194015_85989.png'),
('btm_logo', '/upload/image/201307/17194015_85989.png'),
('smtp_email', 'mallmold@sina.com');

CREATE TABLE IF NOT EXISTS `mm_shipping` (
  `shipping_id` int(4) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `country_id` int(4) NOT NULL,
  `base_weight` int(10) NOT NULL default '0',
  `base_fee` float(8,2) NOT NULL,
  `step_weight` int(10) NOT NULL default '0',
  `step_fee` float(8,2) NOT NULL default '0.00',
  `sort_order` int(4) NOT NULL default '0',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`shipping_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `mm_shipping` (`shipping_id`, `name`, `country_id`, `base_weight`, `base_fee`, `step_weight`, `step_fee`, `sort_order`, `status`) VALUES
(1, 'Free shipping', 1, 0, 0.00, 0, 0.00, 1, 1);

CREATE TABLE IF NOT EXISTS `mm_shipping_set` (
  `id` int(8) NOT NULL auto_increment,
  `shipping_id` int(4) NOT NULL,
  `region_id` int(8) NOT NULL,
  `base_fee` float(8,2) NOT NULL,
  `step_fee` float(8,2) NOT NULL default '0.00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `mm_shipping_set` (`id`, `shipping_id`, `region_id`, `base_fee`, `step_fee`) VALUES
(1, 1, 32, 2.00, 0.00);

CREATE TABLE IF NOT EXISTS `mm_slider` (
  `slider_id` int(4) NOT NULL auto_increment,
  `name_key_` varchar(10) NOT NULL,
  `sign` varchar(32) NOT NULL,
  `setting_id` varchar(32) NOT NULL default '0',
  PRIMARY KEY  (`slider_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_slider_image` (
  `id` int(4) NOT NULL auto_increment,
  `slider_id` int(4) NOT NULL,
  `src` varchar(64) NOT NULL,
  `title_key_` varchar(10) NOT NULL,
  `sort_order` int(4) NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `siler_id` (`slider_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_summary` (
  `id` int(2) NOT NULL auto_increment,
  `name_key_` varchar(10) NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_tax` (
  `id` int(4) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL,
  `country_id` int(4) NOT NULL,
  `defaut_tax` decimal(6,4) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_tax_set` (
  `id` int(8) NOT NULL auto_increment,
  `tax_id` int(4) NOT NULL,
  `region_id` int(8) NOT NULL,
  `tax` decimal(6,4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_urlkey` (
  `id` int(10) NOT NULL auto_increment,
  `model` varchar(16) NOT NULL,
  `item_id` int(10) NOT NULL,
  `urlkey` varchar(1024) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

INSERT INTO `mm_urlkey` (`id`, `model`, `item_id`, `urlkey`) VALUES
(1, 'list', 1, 'help'),
(2, 'list', 2, 'about'),
(3, 'article', 1, 'about-us'),
(4, 'article', 2, 'how-to-pay'),
(5, 'article', 3, 'contact-us'),
(27, 'page', 1, 'about-us'),
(28, 'page', 2, 'contact-us');

CREATE TABLE IF NOT EXISTS `mm_user` (
  `user_id` int(10) NOT NULL auto_increment,
  `group_id` int(4) NOT NULL,
  `username` varchar(32) NOT NULL,
  `email` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `salt` char(2) default NULL,
  `score` int(8) NOT NULL default '0',
  `language` varchar(8) default NULL,
  `reg_time` int(11) NOT NULL,
  `login_time` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`user_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_user_address` (
  `id` int(10) NOT NULL auto_increment,
  `user_id` int(10) NOT NULL,
  `fullname` varchar(16) NOT NULL,
  `country_id` int(4) NOT NULL,
  `region_id` int(8) NOT NULL,
  `city` varchar(16) NOT NULL,
  `address` varchar(128) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `postcode` char(8) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `mm_user_group` (
  `group_id` int(4) NOT NULL auto_increment,
  `name_key_` varchar(10) NOT NULL,
  `spending` float(10,2) NOT NULL default '0.00',
  `status` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `mm_user_group` (`group_id`, `name_key_`, `spending`, `status`) VALUES
(1, 'k_10', 0.00, 1);
