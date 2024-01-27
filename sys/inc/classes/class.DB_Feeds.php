<?php 

class DB_Feeds 
{
	public $args = array(); 
	public $paged = 1; 
	public $pages = 1; 
	public $total = 0; 
	public $request = ''; 
	public $items; 
	private $config; 

	public function __construct($args = array()) 
	{
		$set = get_settings(); 

		if (!isset($args['p_str'])) {
			$args['p_str'] = $set['p_str']; 
		}

		if (!isset($args['paged'])) {
			$args['paged'] = get_paged(); 
		}

		if (!isset($args['order'])) {
			$args['order'] = 'DESC'; 
		}

		if (!isset($args['orderby'])) {
			$args['orderby'] = 'id'; 
		}

		$this->config = ds_get('ds_feed_setup', array()); 
		$this->paged = $args['paged']; 

		$this->args = array_merge($this->args, $args); 
		$this->query($this->args); 
	}

	public function query($args) 
	{
		if (!isset($args['status'])) {
			$args['status'] = 'feeds'; 
		}

		$SQLConst['%select%'] = "feeds.*"; 
		$SQLConst['%join%'] = "";

		$where = array(); 

		if (isset($args['user_id'])) {
			$SQLConst['%join%'] = "LEFT JOIN subscriptions ON (subscriptions.object_id = feeds.user_id)";
			$where[] = "(subscriptions.user_id = '" . $args['user_id'] . "')";
		}
		
		if (isset($args['author_id'])) {
			$where[] = "(feeds.user_id = '" . $args['author_id'] . "')";
		}
		
		if (isset($args['where'])) {
			if (is_array($args['where'])) { 
				$where[] = db::get_construct_query_where('feeds', $args['where'], ''); 
			} elseif (is_string($args['where'])) {
				$where[] = $args['where']; 
			}
		}
		
		$SQLConst['%where%'] = ($where ? 'AND ' . implode(' AND ', $where) : ''); 
		$SQLConst['%order%'] = "ORDER BY feeds.time_create " . $args['order']; 
		
		$SQLConst['%limit%'] = ''; 
		if ($args['p_str'] != '-1') {
			$start  = $args['p_str'] * $args['paged'] - $args['p_str'];
			$SQLConst['%limit%'] = "LIMIT " . $start . ", " . $args['p_str']; 
		}

		$SQLCount = str_replace(array_keys($SQLConst), array_values($SQLConst), "SELECT COUNT(%select%) FROM feeds %join% WHERE 1=1 %where%"); 
		$this->total = db::count($SQLCount); 
		if ($this->total > $this->args['p_str']) {
			$this->pages = ceil($this->total / $this->args['p_str']); 
		}

		$SQLSelect = str_replace(array_keys($SQLConst), array_values($SQLConst), "SELECT %select% FROM feeds %join% WHERE 1=1 %where% %order% %limit%"); 

		$this->request = $SQLSelect; 
		$items = db::select($SQLSelect); 

		$likes = array(); 
		if ($user_id = get_user_id()) {
			$ids = array(); 
			foreach($items AS $key => $item) {
				$ids[] = $item['id']; 
			}

			$likes = db::get_row("SELECT `object_id` FROM `feeds_likes` WHERE `user_id` = '" . $user_id . "' AND `object_id` IN(" . join(',', $ids) . ")"); 
		}

		foreach($items AS $key => $value) {
			$items[$key]['is_liked'] = (in_array($value['id'], $likes) ? 1 : 0); 
		}

		$this->items = $items; 
	}
}