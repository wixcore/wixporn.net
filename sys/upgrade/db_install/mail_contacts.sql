
CREATE TABLE IF NOT EXISTS `mail_contacts` (
  `user_id` bigint(20),
  `contact_id` bigint(20),
  `status` varchar(128) DEFAULT 'new',
  `time_update` int(11) DEFAULT '0',
  `title` varchar(128)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;