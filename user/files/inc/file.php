<?php 

$file = get_file($file_id); 

if (isset($_GET['t'])) {
	$term_id = (int) $_GET['t']; 
	$term = get_files_term($term_id);
}

if ($file_id && !$file) {
    p404();
}

$extension = preg_replace('/.*\./', '', $file['name']); 
$download = get_file_download_url($file); 

$public = true; 
if ($ds_files_config['public'] === false) {
	if ($term['user_id'] != get_user_id()) {
		$public = false; 
	}
}

$set['title'] = text($file['title']); 

get_header(); 

// Хлебные крошки 
ds_files_breadcrumb($term_id, true, $mask);
?>
<div class="box-group-wrap <?php echo get_file_classes($file); ?>">
	<div class="box-group">
		<div class="box-group-title"><?php echo get_file_icon($file); ?> <?php echo text($file['title']); ?>.<?php echo get_file_ext($file); ?></div>
		<?php 
		if (strpos($file['mimetype'], 'video') !== false) {
			echo use_filters('ds_files_player_video', '<div class="box-group-block"><video style="width: 100%; height: 300px;" src="' . $download . '" controls="" preload="none"></video></div>'); 
		} 

		$thumbnail = get_file_thumbnail($file['id'], 'medium'); 

		if ($thumbnail) {
			echo '<div class="box-group-block"><img class="ds-image-preview" src="' . $thumbnail['file'] . '" /></div>'; 
		}

		$filename = ROOTPATH . $file['path'] . $file['name']; 
	    $atts = use_filters('ds_get_output_files_meta', getId3Tags($file)); 

	    if (is_array($atts)) {
		    foreach($atts AS $key => $value) {
		    	if ($value) {
		    		echo '<div class="box-meta"><span class="box-meta-key">' . get_files_meta_name($key) . '</span> <span class="box-meta-value">' . text($value) . '</span></div>'; 
		    	}
		    }
	    }
		?>
		<?php if (!empty($file['description'])) : ?>
		<div class="box-group-description">
			<?php echo output_text($file['description']); ?>
		</div>
		<?php endif; ?>

		<?php 
		if (strpos($file['mimetype'], 'audio') !== false) {
			echo use_filters('ds_files_player_audio', '<div class="box-group-block">' . get_audio_player($file) . '</div>', $file); 
		} 
		?>

		<?php if ($public == true) : ?>
		<div class="box-group-links box-group-center box-group-nav">
			<?php 
			$prev = get_file_prev(use_filters('ds_get_prev_file', array(
				'file_id' => $file_id, 
				'term_id' => $term_id, 
				'files_type' => $ds_request['files_type'],
			))); 

			$next = get_file_next(use_filters('ds_get_next_file', array(
				'file_id' => $file_id, 
				'term_id' => $term_id, 
				'files_type' => $ds_request['files_type'],
			))); 

			$link_prev_title = '<i class="fa fa-angle-left"></i> <span class="box-link-text">' . __('Назад') . '</span>';
			$link_prev_template = use_filters('ds_file_link_prev_template', $prev ? '<a class="box-link box-link-prev" href="' . get_file_link($prev) . '">' . $link_prev_title . '</a>' : '<span class="box-link box-link-prev disabled">' . $link_prev_title . '</span>', $prev); 

			$link_next_title = '<span class="box-link-text">' . __('Вперёд') . '</span> <i class="fa fa-angle-right"></i>';
			$link_next_template = use_filters('ds_file_link_next_template', $next ? '<a class="box-link box-link-next" href="' . get_file_link($next) . '">' . $link_next_title . '</a>' : '<span class="box-link box-link-next disabled">' . $link_next_title . '</span>', 
				$next); 
			?>

			<?php echo $link_prev_template; ?>
			<?php echo $link_next_template; ?>
		</div>
		<?php endif; ?>
	</div>

	<?php if ($public == true) : ?>
	<div class="box-group">
		<div class="box-group-links box-group-center">
			<?php if (is_user_access('loads_file_edit') || get_user_id() == $file['user_id']) : ?>
				<a class="box-link" href="<?php echo str_replace(array_keys($mask), array_values($mask), $ds_files_config['permalinks']['edit_file']); ?>" title="<?php echo __('Редактировать'); ?>">
					<i class="fa fa-edit"></i></a>
			<?php endif; ?>

			<a class="box-link" data-ajax="1"  title="<?php echo __('Поделиться'); ?>">
				<i class="fa fa-share-square-o"></i></a>

			<a class="box-link" data-ajax="1" href="<?php echo get_query_url('module=votes&action=like&object=files&object_id=' . $file['id']); ?>" title="<?php echo __('Мне нравится'); ?>">
				<i class="fa fa-thumbs-o-up"></i></a>

			<a class="box-link" data-ajax="1" href="<?php echo get_query_url('module=votes&action=dislike&object=files&object_id=' . $file['id']); ?>" title="<?php echo __('Мне не нравится'); ?>">
				<i class="fa fa-thumbs-o-down"></i></a>
		</div>
	</div>
	<?php endif; ?>

	<div class="box-group">
		<div class="box-group-links">
			<a class="box-link" href="<?php echo $download; ?>"><i class="fa fa-download"></i> <?php echo __('Скачать %s', size_file($file['size'])); ?></a>
		</div>
	</div>

	<?php 
	$has_comment = true;  

	if ($public == false) {
		$has_comment = false; 
	} elseif (is_user() && $file['comment'] == 'friends' && !is_friend($user['id'], $file['user_id'])) {
		$has_comment = false; 		
	} elseif ($file['comment'] == 'private') {
		$has_comment = false; 
	}

	if (is_user() && $file['user_id'] == $user['id']) {
		$has_comment = true; 
	}

	if ($has_comment == true) : ?>
	<div class="box-group" data-group="comments"> 
		<div class="box-group-title">
		<?php 
		$hash = get_comments_hash('files', $file['id']); 
		$count = get_comments_count('files', $file['id']); 
		echo use_filters('ds_comments_box_title', __('Комментарии: %s', '<span data-comments-count="' . $hash . '">' . $count . '</span>'), $hash, 'files', $file['id']); 
		?>
		</div>

		<?php if (is_user()) : ?>
		<div class="box-group-block" data-block="comments-form">
		<?
		ds_message('files', array(
			'type' => 'comment',  
			'object' => 'files', 
			'object_id' => $file['id'],  
			'comments_title' => $ds_files_config['labels']['root_term_name'] . ' / ' . text($file['title']), 
		)); 
		?>
		</div>
		<?php endif; ?>

		<div class="box-group-block" data-block="comments-list">
		<?
		$comments = new DB_Comments(array(
			'object' => 'files', 
			'object_id' => $file['id'],  
		)); 

		$last = array_key_first($comments->items);
		$first = array_key_last($comments->items);
		$last_id = (!empty($comments->items[$last]) ? $comments->items[$last]['id'] : -1); 
		$first_id = (!empty($comments->items[$first]) ? $comments->items[$first]['id'] : -1); 

		echo '<div class="ds-comments" id="ds-comments" data-paged="' . $comments->paged . '" data-last="' . $last_id . '" data-first="' . $first_id . '" data-comments="' . get_comments_hash('files', $file['id']) . '">'; 
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

		        $url = get_file_link($file); 

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
	        				'cmt' => 'files', 
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