

CREATE TABLE IF NOT EXISTS `files_terms` (
  `term_id` bigint(20) DEFAULT '0',
  `title` varchar(128) DEFAULT '',
  `description` text DEFAULT '',
  `slug` varchar(128) DEFAULT '',
  `parent` bigint(20) DEFAULT '0',
  `path` varchar(512) DEFAULT '0',
  `privacy` varchar(128) DEFAULT 'public',
  `user_id` bigint(20) DEFAULT '0',
  `files` bigint(20) DEFAULT '0',
  `term_type` varchar(128) DEFAULT 'files',
  `size` bigint(20) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `files_terms`
  ADD PRIMARY KEY (`term_id`);

ALTER TABLE `files_terms`
  MODIFY `term_id` bigint(20) AUTO_INCREMENT;
