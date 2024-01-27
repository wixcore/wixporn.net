
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11),
  `nick` varchar(32) DEFAULT '',
  `pass` varchar(32) DEFAULT '',
  `email` varchar(256) DEFAULT '',
  `sess` varchar(32) DEFAULT NULL,
  `activation` varchar(32) DEFAULT NULL,
  `ip` bigint(20) DEFAULT '0',
  `ip_cl` bigint(20) DEFAULT '0',
  `ip_xff` bigint(20) DEFAULT '0',
  `ua` varchar(32) DEFAULT NULL,
  `date_reg` int(11) DEFAULT '0',
  `time` int(11) DEFAULT '0',
  `date_aut` int(11) DEFAULT '0',
  `date_last` int(11) DEFAULT '0',
  `birthdate` date DEFAULT '0000-00-00',
  `balls` int(11) DEFAULT '0',
  `rating` int(11) DEFAULT '0',
  `level` int(1) DEFAULT '0',
  `group_access` int(10) unsigned DEFAULT '1',
  `pol` varchar(10) DEFAULT '1',
  `language` varchar(10) DEFAULT 'ru_RU',
  `url` varchar(64) DEFAULT '/',
  `first_name` varchar(128) DEFAULT NULL,
  `last_name` varchar(128) DEFAULT NULL,
  `set_p_str` int(11) DEFAULT '20',
  `set_timesdvig` int(11) DEFAULT '0',
  `set_them` varchar(32) DEFAULT 'default',
  `browser` varchar(10) DEFAULT 'wap',
  `rating_tmp` int(11) DEFAULT '0',
  `ban_where` varchar(10) DEFAULT NULL,
  `money` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nick` (`nick`),
  ADD KEY `url` (`url`);

ALTER TABLE `user`
  MODIFY `id` int(11) AUTO_INCREMENT;