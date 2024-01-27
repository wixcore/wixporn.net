
CREATE TABLE IF NOT EXISTS `user_options` (
  `id` bigint(20),
  `setting_id` varchar(128),
  `options` text,
  `user_id` bigint(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `user_options`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `user_options`
  MODIFY `id` bigint(20) AUTO_INCREMENT;