CREATE TABLE `ban_ip` (
  `min` bigint(20),
  `max` bigint(20),
  KEY `min` (`min`,`max`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;