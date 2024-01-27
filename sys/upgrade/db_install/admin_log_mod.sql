CREATE TABLE `admin_log_mod` (
  `id` int(11) auto_increment,
  `name` varchar(64),
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;