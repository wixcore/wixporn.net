CREATE TABLE IF NOT EXISTS `admin_log` (
  `id` int(10) unsigned,
  `id_user` int(10) unsigned,
  `time` int(10) unsigned,
  `mod` int(11),
  `act` int(11),
  `opis` text
) ENGINE=MyISAM AUTO_INCREMENT=146 DEFAULT CHARSET=utf8;

ALTER TABLE `admin_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mod` (`mod`),
  ADD KEY `act` (`act`);

ALTER TABLE `admin_log` MODIFY `id` int(10) unsigned AUTO_INCREMENT,AUTO_INCREMENT=1;