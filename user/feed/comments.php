<?php 

if (!defined('ROOTPATH')) {
	die('Доступ запрещен'); 
}

only_reg();

$ds_request = ds_get('route_request'); 

$post_id = (isset($ds_request['post_id']) ? $ds_request['post_id'] : 0);
$feed = db::fetch("SELECT * FROM feeds WHERE id = '" . $post_id . "' LIMIT 1"); 

$set['title'] = __('Комментарии к записи'); 
get_header(); 

?>
<div class="ds-feed-single">
	<?php ds_output_feed($feed); ?>
</div>

<div class="box-group-wrap">
	<?php 
	$has_comment = true;  

	if ($has_comment == true) : ?>
	<div class="box-group" data-group="comments"> 
		<div class="box-group-title">
		<?php 
		$hash = get_comments_hash('feeds', $feed['id']); 
		$count = get_comments_count('feeds', $feed['id']); 
		echo use_filters('ds_comments_box_title', __('Комментарии: %s', '<span data-comments-count="' . $hash . '">' . $count . '</span>'), $hash, 'feeds', $feed['id']); 
		?>
		</div>

		<?php if (is_user()) : ?>
		<div class="box-group-block" data-block="comments-form">
		<?
		ds_message('feeds', array(
			'type' => 'comment',  
			'object' => 'feeds', 
			'object_id' => $feed['id'],  
			'comments_title' => __('Запись с вашей страницы'), 
		)); 
		?>
		</div>
		<?php endif; ?>

		<div class="box-group-block" data-block="comments-list">
		<?
		$comments = new DB_Comments(array(
			'object' => 'feeds', 
			'object_id' => $feed['id'],  
		)); 

		$last = array_key_first($comments->items);
		$first = array_key_last($comments->items);
		$last_id = (!empty($comments->items[$last]) ? $comments->items[$last]['id'] : -1); 
		$first_id = (!empty($comments->items[$first]) ? $comments->items[$first]['id'] : -1); 

		echo '<div class="ds-comments" id="ds-comments" data-paged="' . $comments->paged . '" data-last="' . $last_id . '" data-first="' . $first_id . '" data-comments="' . get_comments_hash('feeds', $feed['id']) . '">'; 
		if ($comments->is_posts()) {
			foreach($comments->items() AS $post) {
		        $classes = array(
		            'ds-messages-item', 
		            'comment comment-' . $post['id'], 
		        ); 

		        $args = array(
		        	'classes' => join(' ', $classes), 
		        	'image' => get_avatar($post['user_id']), 
		        	'title' => '<a href="' . get_user_url($post['user_id']) . '">' . get_user_nick($post['user_id']) . '</a>', 
		        	'time' => vremja($post['time']), 
		        	'content' => output_text($post['msg']), 
		        	'reply' => '?reply_to=' . $post['user_id'] . '&comment_id=' . $post['id'], 
		        	'actions' => array(), 
		        ); 

		        $url = get_site_url('/feed/' . $feed['id']); 

		        if (is_user() && get_user_id() != $post['user_id']) {
		        	$args['actions'][] = use_filters('ds_comment_link_reply', array(
	        			'url' => get_query_url(array(
	        				'reply' => $post['id'], 
	        				'user_id' => $post['user_id'], 
	        			), $url), 
	        			'title' => __('Ответ'), 
		        	), $post);
		        }

		        if (is_user_access('user_prof_edit')) {
		        	$args['actions'][] = array(
	        			'url' => get_query_url(array(
	        				'cmt' => 'feeds', 
	        				'cma' => 'remove', 
	        				'cmu' => $post['id'], 
	        			), $url), 
	        			'title' => __('Удалить'), 
		        	); 
		        }

		        echo get_comment_template($args); 
			}
		} else {
			echo '<div class="comments-empty">' . __('Список комментариев пуст') . '</div>'; 
		}
		echo '</div>'; 
		?>
		</div>
	</div>
	<?php endif; ?>
</div>
<?


get_footer(); 