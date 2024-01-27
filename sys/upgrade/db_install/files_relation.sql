
CREATE TABLE IF NOT EXISTS `files_relation` (
  `id` bigint(20),
  `term_id` bigint(20) DEFAULT '0',
  `file_id` bigint(20) DEFAULT '0',
  `user_id` bigint(20) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `files_relation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `term_id` (`term_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `files_relation`
  MODIFY `id` bigint(20) AUTO_INCREMENT;