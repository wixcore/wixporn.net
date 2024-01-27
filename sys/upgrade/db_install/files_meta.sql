
CREATE TABLE IF NOT EXISTS `files_meta` (
  `meta_id` bigint(20) DEFAULT '0',
  `object_id` bigint(20) DEFAULT '0',
  `meta_key` varchar(512) DEFAULT '',
  `meta_value` text DEFAULT '',
  `meta_type` varchar(128) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `files_meta`
  ADD PRIMARY KEY (`meta_id`);

ALTER TABLE `files_meta`
  MODIFY `meta_id` bigint(20) AUTO_INCREMENT;