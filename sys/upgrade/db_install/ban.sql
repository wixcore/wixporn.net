
CREATE TABLE IF NOT EXISTS `ban` (
  `id` int(11) AUTO_INCREMENT,
  `time` int(11),
  `id_user` int(11),
  `id_ban` int(11),
  `prich` varchar(1024),
  `view` set('1','0') DEFAULT '0',
  `razdel` varchar(10) DEFAULT 'all',
  `post` int(1) DEFAULT '0',
  `pochemu` int(11) DEFAULT '0',
  `navsegda` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`,`id_ban`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
