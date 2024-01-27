
CREATE TABLE IF NOT EXISTS `comments_meta` (
  `meta_id` bigint(20),
  `meta_key` varchar(512),
  `meta_value` text,
  `comment_id` bigint(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `comments_meta`
  ADD PRIMARY KEY (`meta_id`);

ALTER TABLE `comments_meta`
  MODIFY `meta_id` bigint(20) AUTO_INCREMENT;