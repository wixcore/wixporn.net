
CREATE TABLE IF NOT EXISTS `all_accesses` (
  `type` varchar(32),
  `name` varchar(64)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `all_accesses` (`type`, `name`) VALUES
('adm_panel_show', 'Админка - доступ к разделам админки'),
('adm_info', 'Админка - общая информация'),
('adm_statistic', 'Админка - статистика'),
('adm_banlist', 'Админка - список забаненых'),
('adm_set_sys', 'Админка - настройки системы'),
('adm_set_user', 'Админка - пользовательские настройки'),
('adm_set_foto', 'Админка - настройки фотогалереи'),
('adm_forum_sinc', 'Админка - синхронизация таблиц форума'),
('adm_themes', 'Админка - темы оформления'),
('adm_log_read', 'Админка - лог действий администрации'),
('adm_log_delete', 'Админка - удаление лога'),
('adm_mysql', 'Админка - MySQL запросы'),
('adm_ref', 'Админка - рефералы'),
('adm_show_adm', 'Админка - список администрации'),
('adm_ip_edit', 'Админка - редактирование IP операторов'),
('adm_ban_ip', 'Админка - бан по IP'),
('adm_accesses', 'Привилегии групп пользователей'),
('user_delete', 'Пользователи - удаление'),
('user_mass_delete', 'Пользователи - массовое удаление'),
('user_ban_set', 'Пользователи - бан'),
('user_ban_unset', 'Пользователи - снятие бана'),
('user_prof_edit', 'Пользователи - редактирование профиля'),
('user_collisions', 'Пользователи - совпадения ников'),
('user_show_ip', 'Пользователи - показывать IP'),
('user_show_ua', 'Пользователи - показ USER-AGENT'),
('user_show_add_info', 'Пользователи - показ доп. информации'),
('guest_show_ip', 'Гости - показ IP'),
('user_change_group', 'Пользователи - смена группы привилегий'),
('user_ban_set_h', 'Пользователи - бан (max 1 сутки)'),
('user_change_nick', 'Пользователи - смена ника'),
('update_core', 'Обновление системы'),
('plugins', 'Админка - Плагины');

ALTER TABLE `all_accesses`
  ADD KEY `type` (`type`);
