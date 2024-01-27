CREATE TABLE `user_collision` (
  `id_user` int(11),
  `id_user2` int(11),
  `type` set('sess','ip_ua_time') default 'sess',
  KEY `id_user` (`id_user`,`id_user2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;