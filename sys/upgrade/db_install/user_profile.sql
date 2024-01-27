
CREATE TABLE IF NOT EXISTS `user_profile` (
  `id` bigint(20),
  `user_id` bigint(20),
  `profile_key` varchar(512),
  `profile_value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `user_profile`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `user_profile`
  MODIFY `id` bigint(20) AUTO_INCREMENT;