-- --------------------------------------------------------
-- Сервер:                       localhost
-- Версія сервера:               10.4.26-MariaDB - mariadb.org binary distribution
-- ОС сервера:                   Win64
-- HeidiSQL Версія:              12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for таблиця web_wixpornn.admin_log
CREATE TABLE IF NOT EXISTS `admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned DEFAULT NULL,
  `time` int(10) unsigned DEFAULT NULL,
  `mod` int(11) DEFAULT NULL,
  `act` int(11) DEFAULT NULL,
  `opis` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mod` (`mod`),
  KEY `act` (`act`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.admin_log: 0 rows
/*!40000 ALTER TABLE `admin_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_log` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.admin_log_act
CREATE TABLE IF NOT EXISTS `admin_log_act` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mod` int(11) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `act` (`name`),
  KEY `id_mod` (`id_mod`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.admin_log_act: 0 rows
/*!40000 ALTER TABLE `admin_log_act` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_log_act` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.admin_log_mod
CREATE TABLE IF NOT EXISTS `admin_log_mod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.admin_log_mod: 0 rows
/*!40000 ALTER TABLE `admin_log_mod` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_log_mod` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.ban
CREATE TABLE IF NOT EXISTS `ban` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_until` int(11) DEFAULT NULL,
  `user_id` bigint(20) NOT NULL,
  `banned_id` bigint(20) NOT NULL,
  `comment` varchar(1024) DEFAULT NULL,
  `view` set('1','0') DEFAULT '0',
  `time_create` int(11) NOT NULL,
  `hide` int(1) DEFAULT 0,
  `reason` varchar(28) DEFAULT 'spam',
  `forever` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time_until`),
  KEY `id_user` (`user_id`,`banned_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.ban: 0 rows
/*!40000 ALTER TABLE `ban` DISABLE KEYS */;
/*!40000 ALTER TABLE `ban` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.ban_ip
CREATE TABLE IF NOT EXISTS `ban_ip` (
  `min` bigint(20) DEFAULT NULL,
  `max` bigint(20) DEFAULT NULL,
  KEY `min` (`min`,`max`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.ban_ip: 0 rows
/*!40000 ALTER TABLE `ban_ip` DISABLE KEYS */;
/*!40000 ALTER TABLE `ban_ip` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.comments
CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `msg` text DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `object` varchar(128) DEFAULT NULL,
  `object_id` bigint(20) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.comments: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.comments_meta
CREATE TABLE IF NOT EXISTS `comments_meta` (
  `meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `meta_key` varchar(512) DEFAULT NULL,
  `meta_value` text DEFAULT NULL,
  `comment_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`meta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.comments_meta: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.cron
CREATE TABLE IF NOT EXISTS `cron` (
  `id` varchar(32) NOT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.cron: 0 rows
/*!40000 ALTER TABLE `cron` DISABLE KEYS */;
/*!40000 ALTER TABLE `cron` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.feeds
CREATE TABLE IF NOT EXISTS `feeds` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `slug` varchar(128) DEFAULT NULL,
  `object_id` bigint(20) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `time_create` bigint(20) DEFAULT NULL,
  `likes` bigint(20) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.feeds: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.feeds_likes
CREATE TABLE IF NOT EXISTS `feeds_likes` (
  `user_id` bigint(20) DEFAULT NULL,
  `object_id` bigint(20) NOT NULL,
  PRIMARY KEY (`object_id`),
  KEY `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.feeds_likes: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.files
CREATE TABLE IF NOT EXISTS `files` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT 0,
  `title` varchar(512) DEFAULT '',
  `description` text DEFAULT '',
  `name` varchar(512) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `mimetype` varchar(128) DEFAULT NULL,
  `size` bigint(20) DEFAULT NULL,
  `date_upload` datetime DEFAULT NULL,
  `time_upload` bigint(20) DEFAULT NULL,
  `comment` varchar(128) DEFAULT 'public',
  `file_type` varchar(128) DEFAULT 'files',
  `hash` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.files: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.files_attachments
CREATE TABLE IF NOT EXISTS `files_attachments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `file_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `object` varchar(128) NOT NULL,
  `object_id` bigint(20) NOT NULL,
  `time` int(11) NOT NULL,
  `param1` varchar(128) NOT NULL,
  `param1_id` bigint(20) NOT NULL DEFAULT -1,
  `param2` varchar(128) NOT NULL,
  `param2_id` bigint(20) NOT NULL DEFAULT -1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.files_attachments: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.files_meta
CREATE TABLE IF NOT EXISTS `files_meta` (
  `meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `object_id` bigint(20) DEFAULT 0,
  `meta_key` varchar(512) DEFAULT '',
  `meta_value` text DEFAULT '',
  `meta_type` varchar(128) DEFAULT '',
  PRIMARY KEY (`meta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.files_meta: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.files_relation
CREATE TABLE IF NOT EXISTS `files_relation` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) DEFAULT 0,
  `file_id` bigint(20) DEFAULT 0,
  `user_id` bigint(20) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`),
  KEY `term_id` (`term_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.files_relation: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.files_terms
CREATE TABLE IF NOT EXISTS `files_terms` (
  `term_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) DEFAULT '',
  `description` text DEFAULT '',
  `slug` varchar(128) DEFAULT '',
  `parent` bigint(20) DEFAULT 0,
  `path` varchar(512) DEFAULT '0',
  `privacy` varchar(128) DEFAULT 'public',
  `user_id` bigint(20) DEFAULT 0,
  `files` bigint(20) DEFAULT 0,
  `term_type` varchar(128) DEFAULT 'files',
  `size` bigint(20) DEFAULT 0,
  PRIMARY KEY (`term_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.files_terms: ~3 rows (приблизно)
INSERT INTO `files_terms` (`term_id`, `title`, `description`, `slug`, `parent`, `path`, `privacy`, `user_id`, `files`, `term_type`, `size`) VALUES
	(1, 'Фотографии', '', '', 0, '0', 'public', 1, 0, 'photos', 0),
	(2, 'Файлы', '', '', 0, '0', 'public', 1, 0, 'files', 0),
	(3, 'Музыка', '', '', 0, '0', 'public', 1, 0, 'music', 0);

-- Dumping structure for таблиця web_wixpornn.friends
CREATE TABLE IF NOT EXISTS `friends` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `friend_id` bigint(20) DEFAULT NULL,
  `status` int(11) DEFAULT 0,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.friends: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.guests
CREATE TABLE IF NOT EXISTS `guests` (
  `ip` bigint(20) DEFAULT NULL,
  `ua` varchar(32) DEFAULT NULL,
  `date_aut` int(11) DEFAULT NULL,
  `date_last` int(11) DEFAULT NULL,
  `url` varchar(64) DEFAULT NULL,
  `pereh` int(11) DEFAULT 0,
  KEY `ip_2` (`ip`,`ua`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.guests: 0 rows
/*!40000 ALTER TABLE `guests` DISABLE KEYS */;
/*!40000 ALTER TABLE `guests` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.likes
CREATE TABLE IF NOT EXISTS `likes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `object_id` bigint(20) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `time` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.likes: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.mail
CREATE TABLE IF NOT EXISTS `mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `contact_id` bigint(20) DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  `msg` text DEFAULT NULL,
  `read` set('0','1') DEFAULT '0',
  `unlink` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_user` (`user_id`,`contact_id`),
  KEY `read` (`read`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.mail: 0 rows
/*!40000 ALTER TABLE `mail` DISABLE KEYS */;
/*!40000 ALTER TABLE `mail` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.mail_contacts
CREATE TABLE IF NOT EXISTS `mail_contacts` (
  `user_id` bigint(20) DEFAULT NULL,
  `contact_id` bigint(20) DEFAULT NULL,
  `status` varchar(128) DEFAULT 'new',
  `time_update` int(11) DEFAULT 0,
  `title` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.mail_contacts: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.notification
CREATE TABLE IF NOT EXISTS `notification` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `from_id` bigint(20) NOT NULL,
  `object` varchar(128) NOT NULL,
  `object_id` bigint(20) NOT NULL,
  `type` varchar(128) NOT NULL,
  `group_id` varchar(128) NOT NULL DEFAULT 'other',
  `data` text NOT NULL,
  `read` int(1) NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.notification: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.opsos
CREATE TABLE IF NOT EXISTS `opsos` (
  `min` bigint(11) DEFAULT NULL,
  `max` bigint(11) DEFAULT NULL,
  `opsos` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  KEY `min` (`min`,`max`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.opsos: 332 rows
/*!40000 ALTER TABLE `opsos` DISABLE KEYS */;
INSERT INTO `opsos` (`min`, `max`, `opsos`) VALUES
	(3274702592, 3274702847, 'Kcel'),
	(1333559296, 1333575679, 'life:)'),
	(-734354944, -734354433, 'life:)'),
	(3582431104, 3582434943, 'TambovGSM'),
	(1358905344, 1358905471, 'UMC'),
	(1490444288, 1490452479, 'UMC'),
	(1490436096, 1490444287, 'UMC'),
	(3588472832, 3588489215, 'WellCOM GSM'),
	(3253698560, 3253699071, 'WellCOM GSM'),
	(3557661440, 3557661695, 'КаР-Тел'),
	(3240705024, 3240706047, 'Киевстар'),
	(1360467968, 1360470015, 'Киевстар'),
	(1360465920, 1360467967, 'Киевстар'),
	(1402278912, 1402279935, 'МегаФон'),
	(1402286080, 1402287103, 'МегаФон'),
	(1402273792, 1402275839, 'MegaFon'),
	(3650368512, 3650368767, 'МОТИВ'),
	(1410459648, 1410460159, 'МТС'),
	(3641237504, 3641241599, 'МТС'),
	(3276428288, 3276428543, 'МТС'),
	(3258356736, 3258357759, 'МТС'),
	(3562834880, 3562834943, 'МТС'),
	(3579259392, 3579265535, 'МТС'),
	(1347674112, 1347678207, 'МТС'),
	(3579267072, 3579267935, 'МТС'),
	(1358906880, 1358907135, 'МТС'),
	(1476194816, 1476195071, 'МТС'),
	(1372794624, 1372794879, 'МТС'),
	(3642047744, 3642047999, 'МТС'),
	(3579269120, 3579273215, 'МТС'),
	(3277188608, 3277188863, 'МТС'),
	(3645018112, 3645019135, 'МТС'),
	(1347126528, 1347127167, 'МТС'),
	(3281465344, 3281465599, 'МТС'),
	(1360933376, 1360933887, 'МТС'),
	(3647698432, 3647698687, 'МТС'),
	(3267023360, 3267023488, 'МТС'),
	(1535627776, 1535628031, 'МТС'),
	(1410457600, 1410459647, 'МТС'),
	(1433788416, 1433788671, 'МТС'),
	(1389383040, 1389383167, 'НСС'),
	(1358122240, 1358123007, 'НТК'),
	(1358118912, 1358119423, 'НТК'),
	(1438682304, 1438682335, 'Оренбург-GSM'),
	(1509752832, 1509756927, 'Скай Линк'),
	(3565250560, 3565254655, 'Скай Линк'),
	(1536507904, 1536516095, 'Скай Линк'),
	(1536516096, 1536518143, 'Скай Линк'),
	(3578853376, 3578854079, 'Скай Линк'),
	(1406738432, 1406739967, 'Скай Линк'),
	(3266342656, 3266342911, 'Скай Мобайл'),
	(1346736128, 1346737151, 'СМАРТС'),
	(3260286336, 3260286463, 'СМАРТС'),
	(1509527552, 1509528575, 'СТеК Джи Эс Эм'),
	(1506762752, 1506764799, 'ТАТИНКОМ-Т'),
	(1404203008, 1404211199, 'Теле2'),
	(3580231936, 3580232191, 'Теле2'),
	(1404215296, 1404219391, 'Теле2'),
	(1404874752, 1404875775, 'Теле2'),
	(1404189184, 1404189695, 'Теле2'),
	(1404829696, 1404837887, 'Теле2'),
	(3580236800, 3580237567, 'Теле2'),
	(3580235264, 3580235775, 'Теле2'),
	(1404837888, 1404846079, 'Теле2'),
	(3580214272, 3580214783, 'Теле2'),
	(1404227584, 1404232191, 'Теле2'),
	(3580232448, 3580233215, 'Теле2'),
	(3580239360, 3580239871, 'Теле2'),
	(3580231680, 3580231935, 'Теле2'),
	(1441366016, 1441371903, 'Utel'),
	(1401450496, 1401450751, 'Utel'),
	(1506570240, 1506574335, 'Utel'),
	(3571187712, 3571191807, 'Utel'),
	(3564676832, 3564676863, 'Utel'),
	(1494507776, 1494508031, 'Utel'),
	(3641816064, 3641816319, 'Utel'),
	(3651755008, 3651756031, 'Цифровая экспансия'),
	(2130706433, 2130706433, 'localhost'),
	(3283979853, 3283979853, 'Opera Mini'),
	(1412413440, 1412413951, 'Наука-Связь'),
	(1518993408, 1519058943, 'Теле2'),
	(1410269184, 1410334719, 'Bite GSM'),
	(1407907840, 1407908351, 'Ульяновск-GSM'),
	(2197028864, 2197094399, 'Теле2'),
	(3274702592, 3274702847, 'Kcel'),
	(-734351360, -734347265, 'life:)'),
	(-734353408, -734351361, 'life:)'),
	(3582431104, 3582434943, 'TambovGSM'),
	(1358905344, 1358905471, 'UMC'),
	(1490444288, 1490452479, 'UMC'),
	(1490436096, 1490444287, 'UMC'),
	(3588472832, 3588489215, 'WellCOM GSM'),
	(3253698560, 3253699071, 'WellCOM GSM'),
	(3557661440, 3557661695, 'КаР-Тел'),
	(3240705024, 3240706047, 'Киевстар'),
	(1360467968, 1360470015, 'Киевстар'),
	(1360465920, 1360467967, 'Киевстар'),
	(1402275840, 1402276863, 'МегаФон'),
	(1402278912, 1402279935, 'МегаФон'),
	(1402286080, 1402287103, 'МегаФон'),
	(1402273792, 1402275839, 'МегаФон'),
	(1346621440, 1346622463, 'МегаФон'),
	(3251233792, 3251234815, 'МегаФон'),
	(1402284032, 1402285055, 'МегаФон'),
	(1402281984, 1402283007, 'МегаФон'),
	(1402287104, 1402288127, 'МегаФон'),
	(1402279936, 1402280959, 'МегаФон'),
	(1402277888, 1402278911, 'МегаФон'),
	(3650368512, 3650368767, 'МОТИВ'),
	(1410459648, 1410460159, 'МТС'),
	(3641237504, 3641241599, 'МТС'),
	(3276428288, 3276428543, 'МТС'),
	(3258356736, 3258357759, 'МТС'),
	(3562834880, 3562834943, 'МТС'),
	(3579259392, 3579265535, 'МТС'),
	(1347674112, 1347678207, 'МТС'),
	(3579267072, 3579267935, 'МТС'),
	(1358906880, 1358907135, 'МТС'),
	(1476194816, 1476195071, 'МТС'),
	(1372794624, 1372794879, 'МТС'),
	(3642047744, 3642047999, 'МТС'),
	(3579269120, 3579273215, 'МТС'),
	(3277188608, 3277188863, 'МТС'),
	(3645018112, 3645019135, 'МТС'),
	(1347126528, 1347127167, 'МТС'),
	(3281465344, 3281465599, 'МТС'),
	(1360933376, 1360933887, 'МТС'),
	(3647698432, 3647698687, 'МТС'),
	(3267023360, 3267023488, 'МТС'),
	(3645566976, 3645569023, 'МТС'),
	(1535627776, 1535628031, 'МТС'),
	(1410457600, 1410459647, 'МТС'),
	(1433788416, 1433788671, 'МТС'),
	(1389383040, 1389383167, 'НСС'),
	(1358122240, 1358123007, 'НТК'),
	(1358118912, 1358119423, 'НТК'),
	(1438682304, 1438682335, 'Оренбург-GSM'),
	(1509752832, 1509756927, 'Скай Линк'),
	(3565250560, 3565254655, 'Скай Линк'),
	(1536507904, 1536516095, 'Скай Линк'),
	(1536516096, 1536518143, 'Скай Линк'),
	(3578853376, 3578854079, 'Скай Линк'),
	(1406738432, 1406739967, 'Скай Линк'),
	(3266342656, 3266342911, 'Скай Мобайл'),
	(1346736128, 1346737151, 'СМАРТС'),
	(3260286336, 3260286463, 'СМАРТС'),
	(1509527552, 1509528575, 'СТеК Джи Эс Эм'),
	(1506762752, 1506764799, 'ТАТИНКОМ-Т'),
	(1404203008, 1404211199, 'Теле2'),
	(3580231936, 3580232191, 'Теле2'),
	(1404215296, 1404219391, 'Теле2'),
	(1404874752, 1404875775, 'Теле2'),
	(1404189184, 1404189695, 'Теле2'),
	(1404829696, 1404837887, 'Теле2'),
	(3580236800, 3580237567, 'Теле2'),
	(3580235264, 3580235775, 'Теле2'),
	(1404837888, 1404846079, 'Теле2'),
	(3580214272, 3580214783, 'Теле2'),
	(1404227584, 1404232191, 'Теле2'),
	(3580232448, 3580233215, 'Теле2'),
	(3580239360, 3580239871, 'Теле2'),
	(3580231680, 3580231935, 'Теле2'),
	(1441366016, 1441371903, 'Utel'),
	(1401450496, 1401450751, 'Utel'),
	(1506570240, 1506574335, 'Utel'),
	(3571187712, 3571191807, 'Utel'),
	(3564676832, 3564676863, 'Utel'),
	(1494507776, 1494508031, 'Utel'),
	(3641816064, 3641816319, 'Utel'),
	(3651755008, 3651756031, 'Цифровая экспансия'),
	(2130706433, 2130706433, 'localhost'),
	(3283979853, 3283979853, 'Opera Mini'),
	(1412413440, 1412413951, 'Наука-Связь'),
	(3588390912, 3588391935, 'Bite GSM'),
	(1518993408, 1519058943, 'Теле2'),
	(1410269184, 1410334719, 'Bite GSM'),
	(1407907840, 1407908351, 'Ульяновск-GSM'),
	(2197028864, 2197094399, 'Теле2'),
	(-646557696, -646556673, 'Beeline'),
	(3274702592, 3274702847, 'K cel'),
	(-712536192, -712532353, 'TambovGSM'),
	(1333575680, 1333592063, 'life:)'),
	(-734355456, -734355201, 'life:)'),
	(1536278528, 1536294911, 'Life:)'),
	(3582431104, 3582434943, 'TambovGSM'),
	(1358905344, 1358905471, 'UMC'),
	(1490444288, 1490452479, 'UMC'),
	(1490436096, 1490444287, 'UMC'),
	(3588472832, 3588489215, 'WellCOM GSM'),
	(3253698560, 3253699071, 'WellCOM GSM'),
	(1519796224, 1519800319, 'Уралсвязьинформ'),
	(1388849152, 1388850175, 'ИНДИГО'),
	(1427948288, 1427948543, 'Дальсвязь'),
	(1385632000, 1385632255, 'БашСЕЛ'),
	(3273026560, 3273027583, 'БашСЕЛ'),
	(1347599104, 1347599359, 'Байкалвестком'),
	(1360162816, 1360166911, 'Байкалвестком'),
	(1360163840, 1360164351, 'Байкалвестком'),
	(3588519936, 3588520447, 'Байкалвестком'),
	(1407888896, 1407889151, 'Байкалвестком'),
	(3564188672, 3564188927, 'Utel'),
	(3564189696, 3564191743, 'Utel'),
	(1441366016, 1441371903, 'Utel'),
	(1401450496, 1401450751, 'Utel'),
	(1506570240, 1506574335, 'Utel'),
	(3571187712, 3571191807, 'Utel'),
	(3564676832, 3564676863, 'Utel'),
	(1494507776, 1494508031, 'Utel'),
	(3641816064, 3641816319, 'Utel'),
	(3564189440, 3564189695, 'Utel'),
	(2130706433, 2130706433, 'localhost'),
	(3283979853, 3283979853, 'Opera Mini'),
	(3588390912, 3588391935, 'Bite GSM'),
	(3585764352, 3585764607, 'Utel'),
	(1410269184, 1410334719, 'Bite GSM'),
	(3564189184, 3564189439, 'Utel'),
	(3582031776, 3582031807, 'Мегафон'),
	(1427812352, 1427813375, 'Мегафон'),
	(1433657344, 1433657599, 'Билайн'),
	(3648405504, 3648406527, 'Билайн'),
	(3648406528, 3648407551, 'Билайн'),
	(3648408576, 3648409599, 'Билайн'),
	(3648409600, 3648410623, 'Билайн'),
	(3648410624, 3648411647, 'Билайн'),
	(3648411648, 3648412671, 'Билайн'),
	(3648412672, 3648413695, 'Билайн'),
	(1047070464, 1047072255, 'Utel'),
	(1401451520, 1401451775, 'Utel'),
	(1425981440, 1425997823, 'Utel'),
	(3272364544, 3272364799, 'Utel'),
	(3564675072, 3564675583, 'Utel'),
	(3641816576, 3641816831, 'Utel'),
	(1519779840, 1519910911, 'Utel'),
	(3274599168, 3274599423, 'БайкалВестКом'),
	(1042394624, 1042394879, 'МТС'),
	(1346950400, 1346950655, 'МТС'),
	(3281453056, 3281518591, 'МТС'),
	(3287259392, 3287259647, 'МТС'),
	(3559689216, 3559689471, 'МТС'),
	(3562834688, 3562834879, 'МТС'),
	(3578831872, 3578832127, 'МТС'),
	(3648478720, 3648479231, 'МТС'),
	(3579268096, 3579268607, 'МТС'),
	(1404862464, 1404870655, 'Tele2'),
	(1404846080, 1404854271, 'Tele2'),
	(1404854272, 1404862463, 'Tele2'),
	(1518927872, 1518944255, 'Tele2'),
	(3650367488, 3650368511, 'Мотив'),
	(3278955480, 3278955515, 'ЕТК'),
	(3278956288, 3278956543, 'ЕТК'),
	(3278960128, 3278960383, 'ЕТК'),
	(1432330240, 1432334335, 'НСС'),
	(3282161664, 3282163711, 'НСС'),
	(3282171904, 3282173951, 'НСС'),
	(3585171456, 3585174271, 'НСС'),
	(3267008512, 3267008767, 'НСС'),
	(3277992448, 3277992959, 'НСС'),
	(1441609984, 1441610239, 'НСС'),
	(3645731072, 3645731079, 'Смартс'),
	(1310210048, 1310211071, 'Смартс'),
	(1536099328, 1536099839, 'Смартс'),
	(3587219456, 3587219711, 'Смартс'),
	(3648294912, 3648295935, 'Stek GSM'),
	(1481787392, 1481787647, 'Татинком-Т'),
	(3652001536, 3652001599, 'Татинком-Т'),
	(1052193280, 1052193535, 'MTT'),
	(1347125248, 1347125759, 'MTT'),
	(1358119424, 1358120703, 'НТК'),
	(1406740480, 1406746623, 'Sky Link'),
	(3564593152, 3564593919, 'Sky Link'),
	(3564595968, 3564596735, 'Sky Link'),
	(3564599808, 3564601343, 'Sky Link'),
	(3565248512, 3565250047, 'Sky Link'),
	(3565250048, 3565250559, 'Sky Link'),
	(3648212992, 3648217087, 'Sky Link'),
	(1386348544, 1386414079, 'Акос'),
	(1566410752, 1566411775, 'Ульяновск GSM'),
	(3651751936, 3651752191, 'Цифровая экспансия'),
	(3651752192, 3651752447, 'Цифровая экспансия'),
	(3287244288, 3287244543, 'Индиго'),
	(3560612864, 3560613887, 'Киевстар'),
	(1486487552, 1486553087, 'Киевстар'),
	(1295253504, 1295319039, 'UMC'),
	(1358907392, 1358907647, 'UMC'),
	(1358907648, 1358907903, 'UMC'),
	(1358907904, 1358908031, 'UMC'),
	(1358908160, 1358908415, 'UMC'),
	(1358908416, 1358908671, 'UMC'),
	(1358908672, 1358908927, 'UMC'),
	(1358908928, 1358909183, 'UMC'),
	(1358909184, 1358909439, 'UMC'),
	(2516451328, 2516516863, 'T-Mobile'),
	(3562889472, 3562889727, 'LMT'),
	(3651710976, 3651715071, 'AzerCellTelecom'),
	(1365222400, 1365223423, 'Ge-Magticom'),
	(3645744128, 3645744639, 'Vodafon'),
	(3584180736, 3584180991, 'T-Mobile'),
	(1358192896, 1358193151, 'Vodafon'),
	(1507835904, 1507852287, 'Vodafon'),
	(1507819520, 1507827711, 'Vodafon'),
	(1507827712, 1507835903, 'Vodafon'),
	(1536655360, 1536659455, 'Geocell'),
	(3253712384, 3253712895, 'Voxtel'),
	(3560144384, 3560144639, 'Mobtel'),
	(3644964864, 3644965479, 'T-Mobile'),
	(1054471936, 1054472191, 'Orange'),
	(3275173632, 3275173695, 'TME'),
	(1404190720, 1404198911, 'Tele2'),
	(3583496320, 3583496447, 'Orange'),
	(3266322432, 3266322687, 'Omnitel'),
	(1407033600, 1407033855, 'Unitel'),
	(3568795648, 3568799999, 'Vodafon'),
	(1407188992, 1407210495, 'Vodafon'),
	(3645317120, 3645318015, 'EMT'),
	(3240329216, 3240333311, 'Orange'),
	(3254619392, 3254619647, 'Orange'),
	(1358598144, 1358600191, 'Cellcom'),
	(1296285696, 1296302079, 'Tcell'),
	(3576365056, 3576397823, 'Tcell'),
	(1449959424, 1449967615, 'Tcell'),
	(1336598528, 1336599521, 'Mordcell'),
	(3566895360, 3566895615, 'МТС'),
	(3279123456, 3279124479, 'SRR'),
	(3652165632, 3652167167, 'OutRemer'),
	(3283979776, 3283980287, 'Opera Mini'),
	(3242554368, 3242554495, 'Opera Mini'),
	(1357411584, 1357411839, 'Opera Mini'),
	(1540055040, 1540056063, 'Opera Mini'),
	(1593212416, 1593212927, 'Opera Mini'),
	(-1010987520, -1010987009, 'Opera Mini'),
	(-646555136, -646554369, 'Вымпелком'),
	(-646554368, -646553857, 'Вымпелком WiFi');
/*!40000 ALTER TABLE `opsos` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.options
CREATE TABLE IF NOT EXISTS `options` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT '',
  `value` longtext DEFAULT NULL,
  `type` varchar(20) DEFAULT 'yes',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.options: 6 rows
/*!40000 ALTER TABLE `options` DISABLE KEYS */;
INSERT INTO `options` (`id`, `name`, `value`, `type`) VALUES
	(1, 'ds_themes', '{"justweb":{"name":"Just Web\\r","description":"Адаптивный шаблон WEB \\/ Tablet \\/ Mobile, с поддержкой Ajax технологий WIXCMS v1.0.0 Alpha, создано специально для релизов системы.\\r","author":"ua.lifesheets\\r","authoruri":"https:\\/\\/andryushkin.ru\\r","version":"1.0.4\\r","status":"beta\\r","slug":"justweb","active":"0"}}', 'themes'),
	(2, '_widgets-home', '[]', 'widgets'),
	(3, '_widget_home-1706330036', '{"widget_name":"JW_Widget_Online","widget_area":"home","instance":{"widget_area":"home"}}', 'widget'),
	(4, '_widget_home-1706330029', '{"widget_name":"Widget_Html","widget_area":"home","instance":{"html":"INDEX","widget_area":"home"}}', 'widget'),
	(5, '_widget_home-1706330026', '{"widget_name":"Widget_Feed","widget_area":"home","instance":{"p_str":"5","ajax_load_more":"scroll","widget_area":"home"}}', 'widget'),
	(6, '_widget_home-1706330032', '{"widget_name":"Widget_Text","widget_area":"home","instance":{"title":"Текст","text":"Текст","widget_area":"home"}}', 'widget');
/*!40000 ALTER TABLE `options` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.subscriptions
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `object_id` bigint(20) DEFAULT NULL,
  `type` varchar(128) DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.subscriptions: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nick` varchar(32) DEFAULT '',
  `pass` varchar(32) DEFAULT '',
  `email` varchar(256) DEFAULT '',
  `sess` varchar(32) DEFAULT NULL,
  `activation` varchar(32) DEFAULT NULL,
  `ip` bigint(20) DEFAULT 0,
  `ip_cl` bigint(20) DEFAULT 0,
  `ip_xff` bigint(20) DEFAULT 0,
  `ua` varchar(32) DEFAULT NULL,
  `date_reg` int(11) DEFAULT 0,
  `time` int(11) DEFAULT 0,
  `date_aut` int(11) DEFAULT 0,
  `date_last` int(11) DEFAULT 0,
  `birthdate` date DEFAULT '0000-00-00',
  `balls` int(11) DEFAULT 0,
  `rating` int(11) DEFAULT 0,
  `level` int(1) DEFAULT 0,
  `group_access` int(10) unsigned DEFAULT 1,
  `pol` varchar(10) DEFAULT '1',
  `language` varchar(10) DEFAULT 'ru_RU',
  `url` varchar(64) DEFAULT '/',
  `first_name` varchar(128) DEFAULT NULL,
  `last_name` varchar(128) DEFAULT NULL,
  `set_p_str` int(11) DEFAULT 20,
  `set_timesdvig` int(11) DEFAULT 0,
  `set_them` varchar(32) DEFAULT 'default',
  `browser` varchar(10) DEFAULT 'wap',
  `rating_tmp` int(11) DEFAULT 0,
  `ban_where` varchar(10) DEFAULT NULL,
  `money` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nick` (`nick`),
  KEY `url` (`url`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.user: 1 rows
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id`, `nick`, `pass`, `email`, `sess`, `activation`, `ip`, `ip_cl`, `ip_xff`, `ua`, `date_reg`, `time`, `date_aut`, `date_last`, `birthdate`, `balls`, `rating`, `level`, `group_access`, `pol`, `language`, `url`, `first_name`, `last_name`, `set_p_str`, `set_timesdvig`, `set_them`, `browser`, `rating_tmp`, `ban_where`, `money`) VALUES
	(1, 'Admin', 'd2cfda1362dc109c6bf745d2b25b7134', 'ua.lifesheets@gmail.com', NULL, NULL, 2130706433, NULL, NULL, 'Mozilla', 1706329567, 1314, 0, 1706331792, '1995-05-23', 0, 0, 4, 15, '1', 'ru_RU', '/index.php', 'Микола', 'Довгопол', 20, 0, 'default', 'web', 0, NULL, 0);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.user_collision
CREATE TABLE IF NOT EXISTS `user_collision` (
  `id_user` int(11) DEFAULT NULL,
  `id_user2` int(11) DEFAULT NULL,
  `type` set('sess','ip_ua_time') DEFAULT 'sess',
  KEY `id_user` (`id_user`,`id_user2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.user_collision: 0 rows
/*!40000 ALTER TABLE `user_collision` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_collision` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.user_group_access
CREATE TABLE IF NOT EXISTS `user_group_access` (
  `id_group` int(10) unsigned DEFAULT NULL,
  `id_access` varchar(32) DEFAULT NULL,
  KEY `id_group` (`id_group`,`id_access`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.user_group_access: 46 rows
/*!40000 ALTER TABLE `user_group_access` DISABLE KEYS */;
INSERT INTO `user_group_access` (`id_group`, `id_access`) VALUES
	(7, 'adm_panel_show'),
	(7, 'adm_users_list'),
	(7, 'user_ban_set'),
	(7, 'user_ban_unset'),
	(7, 'user_files_delete'),
	(7, 'user_files_edit'),
	(8, 'adm_info'),
	(8, 'adm_panel_show'),
	(8, 'adm_set_sys'),
	(8, 'adm_users_list'),
	(8, 'user_ban_set'),
	(8, 'user_ban_unset'),
	(8, 'user_delete'),
	(8, 'user_edit'),
	(8, 'user_files_delete'),
	(8, 'user_files_edit'),
	(9, 'adm_accesses'),
	(9, 'adm_info'),
	(9, 'adm_panel_show'),
	(9, 'adm_set_sys'),
	(9, 'adm_themes'),
	(9, 'adm_users_list'),
	(9, 'plugins'),
	(9, 'update_core'),
	(9, 'user_ban_set'),
	(9, 'user_ban_unset'),
	(9, 'user_delete'),
	(9, 'user_edit'),
	(9, 'user_files_delete'),
	(9, 'user_files_edit'),
	(9, 'user_group'),
	(15, 'adm_accesses'),
	(15, 'adm_info'),
	(15, 'adm_panel_show'),
	(15, 'adm_set_sys'),
	(15, 'adm_themes'),
	(15, 'adm_users_list'),
	(15, 'plugins'),
	(15, 'update_core'),
	(15, 'user_ban_set'),
	(15, 'user_ban_unset'),
	(15, 'user_delete'),
	(15, 'user_edit'),
	(15, 'user_files_delete'),
	(15, 'user_files_edit'),
	(15, 'user_group');
/*!40000 ALTER TABLE `user_group_access` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.user_log
CREATE TABLE IF NOT EXISTS `user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `method` set('1','0') DEFAULT '0',
  `time` int(11) DEFAULT NULL,
  `ip` bigint(20) DEFAULT 0,
  `ua` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.user_log: 0 rows
/*!40000 ALTER TABLE `user_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_log` ENABLE KEYS */;

-- Dumping structure for таблиця web_wixpornn.user_meta
CREATE TABLE IF NOT EXISTS `user_meta` (
  `meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT 0,
  `meta_key` varchar(512) DEFAULT NULL,
  `meta_value` text DEFAULT NULL,
  `meta_type` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`meta_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.user_meta: ~1 rows (приблизно)
INSERT INTO `user_meta` (`meta_id`, `user_id`, `meta_key`, `meta_value`, `meta_type`) VALUES
	(1, 1, '__location', 'https://wixporn.home/', NULL);

-- Dumping structure for таблиця web_wixpornn.user_options
CREATE TABLE IF NOT EXISTS `user_options` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `setting_id` varchar(128) DEFAULT NULL,
  `options` text DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.user_options: ~0 rows (приблизно)

-- Dumping structure for таблиця web_wixpornn.user_profile
CREATE TABLE IF NOT EXISTS `user_profile` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `profile_key` varchar(512) DEFAULT NULL,
  `profile_value` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Dumping data for table web_wixpornn.user_profile: ~1 rows (приблизно)
INSERT INTO `user_profile` (`id`, `user_id`, `profile_key`, `profile_value`) VALUES
	(1, 1, 'city', 'Суми');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
