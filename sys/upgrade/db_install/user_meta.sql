CREATE TABLE IF NOT EXISTS `user_meta` (
  `meta_id` bigint(20),
  `user_id` bigint(20) DEFAULT '0',
  `meta_key` varchar(512),
  `meta_value` text,
  `meta_type` varchar(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `user_meta`
  ADD PRIMARY KEY (`meta_id`);

ALTER TABLE `user_meta`
  MODIFY `meta_id` bigint(20) AUTO_INCREMENT;