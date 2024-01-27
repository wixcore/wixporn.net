
CREATE TABLE IF NOT EXISTS `mail` (
  `id` int(11),
  `user_id` bigint(20),
  `contact_id` bigint(20),
  `time` int(11),
  `msg` text,
  `read` set('0','1') DEFAULT '0',
  `unlink` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `mail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`user_id`,`contact_id`),
  ADD KEY `read` (`read`);

ALTER TABLE `mail`
  MODIFY `id` int(11) AUTO_INCREMENT;