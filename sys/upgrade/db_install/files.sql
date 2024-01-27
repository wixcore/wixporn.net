CREATE TABLE IF NOT EXISTS `files` (
  `id` bigint(20),
  `user_id` bigint(20) DEFAULT '0',
  `title` varchar(512) DEFAULT '',
  `description` text DEFAULT '',
  `name` varchar(512),
  `path` text,
  `mimetype` varchar(128),
  `size` bigint(20),
  `date_upload` datetime,
  `time_upload` bigint(20),
  `comment` varchar(128) DEFAULT 'public',
  `file_type` varchar(128) DEFAULT 'files',
  `hash` varchar(32)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

ALTER TABLE `files`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;