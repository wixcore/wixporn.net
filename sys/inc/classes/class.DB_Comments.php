<?php 

class DB_Comments
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

		if (!isset($args['db_table'])) {
			$args['db_table'] = 'comments'; 
		}

		$this->paged = $args['paged']; 
		$this->args = array_merge($this->args, $args); 
		$this->query($this->args); 
	}

	public function query($args) 
	{
		$SQLConst['%select%'] = "*"; 
		$SQLConst['%join%'] = "";

		$where = array(); 

		$where[] = "(`object` = '" . $args['object'] . "' AND `object_id` = '" . $args['object_id'] . "') "; 

		if ($args['last'] > 0) {
			$where[] = "`id` > '" . $args['last'] . "'";
		}
		
		if (isset($args['where'])) {
			if (is_array($args['where'])) { 
				$where[] = db::get_construct_query_where($args['db_table'], $args['where'], ''); 
			} elseif (is_string($args['where'])) {
				$where[] = $args['where']; 
			}
		}
		
		$SQLConst['%where%'] = ($where ? 'AND ' . implode(' AND ', $where) : ''); 
		$SQLConst['%order%'] = "ORDER BY " . $args['db_table'] . "." . $args['orderby'] . " " . $args['order']; 
		
		$SQLConst['%limit%'] = ''; 
		if ($args['p_str'] != '-1') {
			$start  = $args['p_str'] * $args['paged'] - $args['p_str'];
			$SQLConst['%limit%'] = "LIMIT " . $start . ", " . $args['p_str']; 
		}

		$SQLCount = str_replace(array_keys($SQLConst), array_values($SQLConst), "SELECT COUNT(id) FROM " . $args['db_table'] . " %join% WHERE 1=1 %where%"); 

		$this->total = db::count($SQLCount); 
		if ($this->total > $this->args['p_str']) {
			$this->pages = ceil($this->total / $this->args['p_str']); 
		}

		$SQLSelect = str_replace(array_keys($SQLConst), array_values($SQLConst), "SELECT %select% FROM " . $args['db_table'] . " %join% WHERE 1=1 %where% %order% %limit%"); 

		$this->request = $SQLSelect; 
		$items = db::select($SQLSelect); 
		$this->items = $items; 
	}

	public function items() {
		if (count($this->items) > 0) {
			return $this->items; 
		}
		
		return array(); 
	}

	public function is_posts() {
		if (count($this->items) > 0) {
			return true; 
		}
		return false; 
	}
}