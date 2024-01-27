
CREATE TABLE `user_group_access` (
  `id_group` int(10) UNSIGNED DEFAULT NULL,
  `id_access` varchar(32) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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

ALTER TABLE `user_group_access`
  ADD KEY `id_group` (`id_group`,`id_access`);
COMMIT;
