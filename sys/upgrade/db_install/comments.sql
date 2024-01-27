
CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint(20),
  `user_id` bigint(20),
  `msg` text,
  `time` int(11),
  `object` varchar(128),
  `object_id` bigint(20) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `comments`
  MODIFY `id` bigint(20) AUTO_INCREMENT;