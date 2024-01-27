<?php 

class DB_Mail 
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

		if (!isset($args['last'])) {
			$args['last'] = 0; 
		}

		$this->paged = $args['paged']; 
		$this->args = array_merge($this->args, $args); 
		$this->query($this->args); 
	}

	public function query($args) 
	{
		if (!isset($args['status'])) {
			$args['status'] = 'mail'; 
		}

		$SQLConst['%select%'] = "mail.*"; 
		$SQLConst['%join%'] = "";

		$where = array(); 

		$where[] = "((`unlink` != '" . $args['user_id'] . "' AND `user_id` = '" . $args['user_id'] . "' AND `contact_id` = '" . $args['contact_id'] . "') OR (`user_id` = '" . $args['contact_id'] . "' AND `contact_id` = '" . $args['user_id'] . "' AND `unlink` != '" . $args['user_id'] . "')) "; 

		if ($args['last'] > 0) {
			$where[] = "`id` > '" . $args['last'] . "'";
		}
		
		if (isset($args['where'])) {
			if (is_array($args['where'])) { 
				$where[] = db::get_construct_query_where('mail', $args['where'], ''); 
			} elseif (is_string($args['where'])) {
				$where[] = $args['where']; 
			}
		}
		
		$SQLConst['%where%'] = ($where ? 'AND ' . implode(' AND ', $where) : ''); 
		$SQLConst['%order%'] = "ORDER BY mail.time " . $args['order']; 
		
		$SQLConst['%limit%'] = ''; 
		if ($args['p_str'] != '-1') {
			$start  = $args['p_str'] * $args['paged'] - $args['p_str'];
			$SQLConst['%limit%'] = "LIMIT " . $start . ", " . $args['p_str']; 
		}

		$SQLCount = str_replace(array_keys($SQLConst), array_values($SQLConst), "SELECT COUNT(%select%) FROM mail %join% WHERE 1=1 %where%"); 
		$this->total = db::count($SQLCount); 
		if ($this->total > $this->args['p_str']) {
			$this->pages = ceil($this->total / $this->args['p_str']); 
		}

		$SQLSelect = str_replace(array_keys($SQLConst), array_values($SQLConst), "SELECT %select% FROM mail %join% WHERE 1=1 %where% %order% %limit%"); 

		$this->request = $SQLSelect; 
		$items = db::select($SQLSelect); 
		$this->items = $items; 
	}

	public function is_posts() {
		if (count($this->items) > 0) {
			return true; 
		}
		return false; 
	}
}