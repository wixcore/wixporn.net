<?php 

require( '../sys/inc/core.php' );

user_access( 'adm_users_list', null, 'index.php?' . SID );

$set['title'] = __('Пользователи');
get_header_admin(); 

$select = use_filters('admin_users_list_select', array('id')); 
$where = use_filters('admin_users_list_where', ''); 

$order__by = (isset($_GET['order_by']) ? my_esc($_GET['order_by']) : 'id'); 
$order_by = use_filters('admin_users_list_order_by', $order__by); 

$order = (isset($_GET['order']) && $_GET['order'] == 'ASC' ? 'ASC' : 'DESC'); 

$k_post = db::count("SELECT COUNT(*) FROM user WHERE 1=1 $where ORDER BY $order_by $order "); 
$k_page = k_page( $k_post, $set['p_str'] );
$page   = page( $k_page );
$start  = $set['p_str'] * $page - $set['p_str'];

$q = db::query("SELECT ".join(',', $select)." FROM user WHERE 1=1 $where ORDER BY $order_by $order LIMIT $start, $set[p_str]"); 

$table_users_list = use_filters('admin_table_users_list', array(
	'id' => array(
		'title' => 'ID', 
		'value' => 'id', 
		'sortable' => 'id', 
	), 
	'login' => array(
		'title' => __('Логин'), 
		'callback' => function($ank) {
			return '<a href="' . get_user_url($ank['id']) . '">' . get_user_nick($ank['id']) . '</a>';  
		}, 
		'sortable' => 'nick', 
	), 
	'group_name' => array(
		'title' => __('Должность'), 
		'value' => 'group_name', 
		'sortable' => 'group_access', 
	), 
)); 
?>
<div class="table">
	<div class="table-tr">
	<?php foreach($table_users_list AS $key => $th) : ?>
		<div class="table-td">
			<?php 
			if (isset($th['sortable'])) {
				?><a href="?order_by=<?php echo $th['sortable']; ?>&order=<?php echo ($order == 'ASC' ? 'DESC' : 'ASC'); ?>"><?php echo $th['title']; ?></a><?
			} else {
				echo $th['title']; 
			}
			?>
		</div>
	<?php endforeach; ?>
	</div>
	<?php while($ank = $q->fetch_assoc()) { $ank = get_user($ank['id']);  ?>
	<div class="table-tr">
	<?php foreach($table_users_list AS $key => $td) : ?>
		<div class="table-td">
		<?php
		if (isset($td['value'])) {
			echo $ank[$td['value']]; 
		} elseif (isset($td['callback']) && is_callable($td['callback'])) {
			echo call_user_func($td['callback'], $ank);
		}
		?>
		</div>
	<?php endforeach; ?>
	</div>
	<?
}
?></div><?php

if ( $k_page > 1 )
    str('?order_by=' . $th['sortable'] . '&order=' . ($order == 'ASC' ? 'DESC' : 'ASC') . '&', $k_page, $page);

get_footer_admin(); 