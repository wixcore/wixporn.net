
CREATE TABLE IF NOT EXISTS `likes` (
  `id` bigint(20),
  `user_id` bigint(20),
  `object_id` bigint(20),
  `type` varchar(32),
  `time` bigint(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `likes`
  MODIFY `id` bigint(20) AUTO_INCREMENT;