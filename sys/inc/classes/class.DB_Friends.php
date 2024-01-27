<?php 

class DB_Friends 
{
	public $args = array(); 
	public $paged = 1; 
	public $pages = 1; 
	public $total = 0; 
	public $request = ''; 
	public $items; 

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

		$this->paged = $args['paged']; 

		$this->args = array_merge($this->args, $args); 
		$this->query($this->args); 
	}

	public function query($args) 
	{
		if (!isset($args['status'])) {
			$args['status'] = 'friends'; 
		}

		$SQLConst['%select%'] = "user.id"; 
		$SQLConst['%join%'] = "LEFT JOIN user ON (friends.friend_id = user.id)";

		$where = array(); 

		if (isset($args['friend_id'])) {
			$where[] = "(user.friend_id IN(" . $args['friend_id'] . "))"; 
		}

		// Друзья
		if ($args['status'] == 'friends') {
			$where[] = "(friends.user_id = '" . $args['user_id'] . "' AND friends.status = '1')"; 
		}

		// На кого подписан
		if ($args['status'] == 'subscriptions') {
			$where[] = "(friends.user_id = '" . $args['user_id'] . "' AND friends.status IN('0', 2))"; 
		}

		// Подписчики
		if ($args['status'] == 'subscribers') { 
			$SQLConst['%join%'] = "LEFT JOIN user ON (friends.user_id = user.id)";
			$where[] = "(friends.friend_id = '" . $args['user_id'] . "' AND friends.status IN('0', 2))"; 
		}

		// Заявки в друзья
		if ($args['status'] == 'requests') { 
			$SQLConst['%join%'] = "LEFT JOIN user ON (friends.user_id = user.id)";
			$where[] = "(friends.friend_id = '" . $args['user_id'] . "' AND friends.status = '0')"; 
		}

		// Исходящие заявки в друзья
		if ($args['status'] == 'out_requests') { 
			$SQLConst['%join%'] = "LEFT JOIN user ON (friends.friend_id = user.id)";
			$where[] = "(friends.user_id = '" . $args['user_id'] . "' AND friends.status = '0')"; 
		}

		if (isset($args['where'])) {
			if (is_array($args['where'])) { 
				$where[] = db::get_construct_query_where('friends', $args['where'], ''); 
			} elseif (is_string($args['where'])) {
				$where[] = $args['where']; 
			}
		}
		
		$SQLConst['%where%'] = ($where ? 'AND ' . implode(' AND ', $where) : ''); 
		$SQLConst['%order%'] = "ORDER BY user." . $args['orderby'] . " " . $args['order']; 
		
		$SQLConst['%limit%'] = ''; 
		if ($args['p_str'] != '-1') {
			$start  = $args['p_str'] * $args['paged'] - $args['p_str'];
			$SQLConst['%limit%'] = "LIMIT " . $start . ", " . $args['p_str']; 
		}

		$SQLCount = str_replace(array_keys($SQLConst), array_values($SQLConst), "SELECT COUNT(%select%) FROM friends %join% WHERE 1=1 %where%"); 
		$this->total = db::count($SQLCount); 
		if ($this->total > $this->args['p_str']) {
			$this->pages = ceil($this->total / $this->args['p_str']); 
		}

		$SQLSelect = str_replace(array_keys($SQLConst), array_values($SQLConst), "SELECT %select% FROM friends %join% WHERE 1=1 %where% GROUP BY user.id %order% %limit%"); 

		$this->request = $SQLSelect; 

		$ids_friends = db::select($SQLSelect); 

		$items = array(); 
		foreach($ids_friends AS $friend) {
			$items[] = $friend['id']; 
		}

		$this->items = $items; 
	}
}