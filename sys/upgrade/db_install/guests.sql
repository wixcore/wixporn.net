CREATE TABLE `guests` (
  `ip` bigint(20),
  `ua` varchar(32),
  `date_aut` int(11),
  `date_last` int(11),
  `url` varchar(64),
  `pereh` int(11) default '0',
  KEY `ip_2` (`ip`,`ua`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;