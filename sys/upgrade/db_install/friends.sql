
CREATE TABLE IF NOT EXISTS `friends` (
  `id` bigint(20),
  `user_id` bigint(20),
  `friend_id` bigint(20),
  `status` int(11) DEFAULT '0',
  `time` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `friends`
  MODIFY `id` bigint(20) AUTO_INCREMENT;