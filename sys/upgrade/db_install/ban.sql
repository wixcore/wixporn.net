CREATE TABLE `ban` (
  `id` int(11) NOT NULL,
  `time_until` int(11) DEFAULT NULL,
  `user_id` bigint(20) NOT NULL,
  `banned_id` bigint(20) NOT NULL,
  `comment` varchar(1024) DEFAULT NULL,
  `view` set('1','0') DEFAULT '0',
  `time_create` int(11) NOT NULL,
  `hide` int(1) DEFAULT '0',
  `reason` varchar(28) DEFAULT 'spam',
  `forever` int(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `ban`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`user_id`,`banned_id`),
  ADD KEY `time` (`time_until`);

ALTER TABLE `ban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;