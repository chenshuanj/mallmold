INSERT INTO `mallmold`.`mm_payment` VALUES (NULL,'Alipay Direct','支付宝及时到帐','alipay_direct',6,0,1);
INSERT INTO `mallmold`.`mm_payment` VALUES (NULL,'Alipay','支付宝','alipay',7,0,1);
INSERT INTO `mallmold`.`mm_payment` VALUES (NULL,'Alipay Escow','支付宝担保交易','alipay_escow',8,0,1);
INSERT INTO `mallmold`.`mm_payment` VALUES (NULL,'Alipay Bankpay','支付宝网银支付','alipay_bankpay',9,0,1);
INSERT INTO `mallmold`.`mm_payment` VALUES (NULL,'Tenpay','财付通','tenpay',9,0,1);

INSERT INTO `mm_payment` (`id`, `name`, `description`, `model`, `sort_order`, `bind`, `status`) VALUES (NULL, '支付宝', '支付宝即时到帐', 'alipay', 6, 0, 1),
INSERT INTO `mm_payment` (`id`, `name`, `description`, `model`, `sort_order`, `bind`, `status`) VALUES(NULL, '财付通', '财付通支付', 'tenpay', 7, 0, 1),
INSERT INTO `mm_payment` (`id`, `name`, `description`, `model`, `sort_order`, `bind`, `status`) VALUES(NULL, '银联在线支付', '银联在线快速支付', 'unionpay', 8, 0, 1);

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
