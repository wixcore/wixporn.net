<?php 

function add_mail_contact($user_id, $contact_id, $title = 'auto', $status = 'new') 
{
	$contact = db::fetch("SELECT * FROM `mail_contacts` WHERE `user_id` = '" . $user_id . "' AND `contact_id` = '" . $contact_id . "' LIMIT 1");

	if (!$contact) {
		db::insert('mail_contacts', array(
			'user_id' => $user_id, 
			'contact_id' => $contact_id, 
		)); 

		add_event('ds_add_mail_contact', $user_id, $contact_id, $title, $status); 
	}
}

function get_mail_contact($user_id, $contact_id) 
{
	$contact = db::fetch("SELECT * FROM `mail_contacts` WHERE `user_id` = '" . $user_id . "' AND `contact_id` = '" . $contact_id . "' LIMIT 1");

	if ($contact) { 
		if ($contact['title'] == 'auto') {
			$contact['title'] = get_user_nick($contact_id); 
		}
		return use_filters('ds_get_mail_contact', $contact); 
	}

	return false; 
}

function get_mail_message($post_id) 
{
	$post = db::fetch("SELECT * FROM `mail` WHERE `id` = '" . (int) $post_id . "' LIMIT 1");
	return use_filters('ds_mail_message', $post); 
}

function get_mail_contacts($user_id = 0) 
{
	if (!$user_id) $user_id = get_user_id(); 

	$contacts = array(); 

	return use_filters('ds_mail_contacts', $contacts); 
}

function get_mail_url($user_id) 
{
	$url = get_site_url('/mail.php?id=' . $user_id); 
	return use_filters('ds_get_mail_url', $url, $user_id); 
}

function update_mail_read($user_id, $contact_id) 
{
	if (use_filters('ds_mail_read', true, $user_id, $contact_id) === true) {
		db::query("UPDATE `mail` SET `read` = '1' WHERE `contact_id` = '" . $contact_id . "' AND `user_id` = '" . $user_id . "' AND `read` = '0'");
	}
}