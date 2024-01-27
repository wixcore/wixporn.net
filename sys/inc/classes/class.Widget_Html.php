<?php 

class Widget_Html extends Widget
{
	public function __construct() {
		parent::__construct(array(
			'title' => __('HTML код'), 
			'icon' => '<i class="fa fa-html5" aria-hidden="true"></i>', 
		));
	}
	
	public function form($instance) 
	{
		?>
		<p><textarea type="text" name="html" placeholder="<?php echo __('HTML код'); ?>"><?php echo text($this->get_field('html')); ?></textarea></p>
		<?
	}

	public function widget($instance) 
	{
		$html = $this->get_field('html'); 
		echo $this->html_decode($html); 
	}

	public function html_decode($string) {
	    $characters = array('x00', 'n', 'r', '\\', '\'', '"','x1a');
	    $o_chars = array("\x00", "\n", "\r", "\\", "'", "\"", "\x1a");

	    for ($i = 0; $i < strlen($string); $i++) {
	        if (substr($string, $i, 1) == '\\') {
	            foreach ($characters as $index => $char) {
	                if ($i <= strlen($string) - strlen($char) && substr($string, $i + 1, strlen($char)) == $char) {
	                    $string = substr_replace($string, $o_chars[$index], $i, strlen($char) + 1);
	                    break;
	                }
	            }
	        }
	    }
	    
	    return $string;
	}
}