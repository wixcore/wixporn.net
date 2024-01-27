<?php 

class DB_Files 
{
	public $args = array(
		'files_type' => 'files',
		'mimetype' => '*/*',
	); 

	public $paged = 1; 
	public $pages = 1; 
	public $total = 0; 
	public $files = array(); 

	public $request = '';  


    public $mime_types = array(
		'image/jpeg', 
		'image/gif', 
		'image/png', 
		'image/bmp', 
		'video/x-flv', 
		'application/x-javascript', 
		'application/json', 
		'image/tiff', 
		'text/css', 
		'application/xml', 
		'application/msword', 
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
		'application/vnd.ms-excel', 
		'application/vnd.ms-powerpoint', 
		'application/rtf', 
		'application/pdf', 
		'text/html', 
		'text/plain', 
		'video/mpeg', 
		'audio/basic',  
		'audio/mpeg', 
		'audio/wav', 
		'audio/L24', 
		'audio/aiff', 
		'audio/mp4', 
		'audio/aac', 
		'audio/mpeg', 
		'audio/ogg', 
		'audio/vorbis', 
		'audio/x-ms-wma', 
		'audio/x-ms-wax', 
		'audio/vnd.rn-realaudio', 
		'audio/vnd.wave', 
		'audio/webm', 
		'video/msvideo', 
		'video/x-ms-wmv', 
		'video/quicktime', 
		'application/zip', 
		'application/x-tar', 
		'application/x-shockwave-flash', 
		'application/vnd.oasis.opendocument.text', 
		'application/vnd.oasis.opendocument.text-template', 
		'application/vnd.oasis.opendocument.text-web', 
		'application/vnd.oasis.opendocument.text-master', 
		'application/vnd.oasis.opendocument.graphics', 
		'application/vnd.oasis.opendocument.graphics-template', 
		'application/vnd.oasis.opendocument.presentation', 
		'application/vnd.oasis.opendocument.presentation-template', 
		'application/vnd.oasis.opendocument.spreadsheet', 
		'application/vnd.oasis.opendocument.spreadsheet-template', 
		'application/vnd.oasis.opendocument.chart', 
		'application/vnd.oasis.opendocument.formula', 
		'application/vnd.oasis.opendocument.database', 
		'application/vnd.oasis.opendocument.image', 
		'application/vnd.openofficeorg.extension', 
		'application/vnd.ms-word.document.macroEnabled.12', 
		'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 
		'application/vnd.ms-word.template.macroEnabled.12', 
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
		'application/vnd.ms-excel.sheet.macroEnabled.12', 
		'application/vnd.openxmlformats-officedocument.spreadsheetml.template', 
		'application/vnd.ms-excel.template.macroEnabled.12', 
		'application/vnd.ms-excel.sheet.binary.macroEnabled.12', 
		'application/vnd.ms-excel.addin.macroEnabled.12', 
		'application/vnd.openxmlformats-officedocument.presentationml.presentation', 
		'application/vnd.ms-powerpoint.presentation.macroEnabled.12', 
		'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 
		'application/vnd.ms-powerpoint.slideshow.macroEnabled.12', 
		'application/vnd.openxmlformats-officedocument.presentationml.template', 
		'application/vnd.ms-powerpoint.template.macroEnabled.12', 
		'application/vnd.ms-powerpoint.addin.macroEnabled.12', 
		'application/vnd.openxmlformats-officedocument.presentationml.slide', 
		'application/vnd.ms-powerpoint.slide.macroEnabled.12', 
		'application/vnd.ms-officetheme', 
		'application/onenote', 
		'text/csv', 
    );

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

	public function have_files() {
		return $this->total ? 1 : 0; 
	}

	public function query($args) 
	{
		$SQLConst['%join%'] = "LEFT JOIN files_relation ON (files.id = files_relation.file_id)";
		$SQLConst['%select%'] = "files.id"; 

		$where = array(); 

		if (isset($args['term_id'])) {
			$where[] = "(files_relation.term_id IN(" . $args['term_id'] . "))"; 
		}

		/**
		* ID каталогов которые небходимо исключить
		* Допускается строка через запятую или массив 
		*/ 
		if (isset($args['term_not_in'])) {
			$terms_not_ids = (is_array($args['term_not_in']) ? implode(',', $args['term_not_in']) : $args['term_not_in']); 
			$where[] = "(files.id NOT IN (SELECT file_id FROM files_relation WHERE term_id IN (" . $terms_not_ids . ")))"; 
		}

		if (isset($args['file_type'])) {
			$where[] = "(files.file_type = '" . $args['file_type'] . "')"; 
		}

		if (isset($args['user_id'])) {
			$where[] = "(files.user_id = '" . $args['user_id'] . "')"; 
		}

		if (isset($args['where'])) {
			if (is_array($args['where'])) {
				$where[] = db::get_construct_query_where('files', $args['where'], ''); 
			} elseif (is_string($args['where'])) {
				$where[] = $args['where']; 
			}
		}
		
		if ($args['mimetype'] != '*/*') {
			$mimeexp = explode('/', $args['mimetype']); 
			$allowed = array(); 

			foreach($this->mime_types AS $mimetype) {
				if (strpos($mimetype, $mimeexp[0]) !== false) {
					$allowed[] = "files.mimetype = '" . $mimetype . "'"; 
				}
			}

			if ($allowed) {
				$where[] = "(" . implode(' OR ', $allowed) . ")"; 
			}	
		}

		$SQLConst['%where%'] = ($where ? 'AND ' . implode(' AND ', $where) : ''); 
		$SQLConst['%order%'] = "ORDER BY files." . $args['orderby'] . " " . $args['order']; 
		
		$SQLConst['%limit%'] = ''; 
		if ($args['p_str'] != '-1') {
			$start  = $args['p_str'] * $args['paged'] - $args['p_str'];
			$SQLConst['%limit%'] = "LIMIT " . $start . ", " . $args['p_str']; 
		}

		$SQLCount = str_replace(array_keys($SQLConst), array_values($SQLConst), "SELECT COUNT(%select%) FROM files %join% WHERE 1=1 %where%"); 
		$this->total = ceil(db::count($SQLCount)); 
		if ($this->total > $this->args['p_str']) {
			$this->pages = ceil($this->total / $this->args['p_str']); 
		}
		
		$SQLSelect = str_replace(array_keys($SQLConst), array_values($SQLConst), "SELECT %select% FROM files %join% WHERE 1=1 %where% GROUP BY files.id %order% %limit%"); 

		$this->request = $SQLSelect; 

		$ids_files = db::select($SQLSelect); 

		$files = array(); 
		foreach($ids_files AS $file) {
			$files[] = get_file($file['id']); 
		}

		$this->files = $files; 
	}

}