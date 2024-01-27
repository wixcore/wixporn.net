
CREATE TABLE IF NOT EXISTS `feeds` (
  `id` bigint(20),
  `user_id` bigint(20),
  `slug` varchar(128),
  `object_id` bigint(20),
  `content` text,
  `time_create` bigint(20),
  `likes` bigint(20) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `feeds`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `feeds`
  MODIFY `id` bigint(20) AUTO_INCREMENT;