<?php

class ParserFunction {
	public $name;
	public $params;
	public $visibility;
	public $abstract;
	public $description;
	public $comment;
  public $return_type;
  public $return_description;
  
  
	public function __construct() {
		$this->params = array ();
		$this->visibility = 'public';
	}

	public function apply_comment ($text) {
		$this->comment = parse_doc_comment ($text);
	}

	public function post_load () {
		$this->description = htmlify_text($this->comment['@summary']);

		// Do params
		$params = $this->comment['@param'];
		if ($params != null) {
			foreach ($params as $param_tag) {
				list ($type, $name, $desc) = explode(' ', $param_tag, 3);
				
				// if type was not specified, do some clever stuff
        if ($type[0] == '$') {
          $desc = $name . ' ' . $desc;
          $name = $type;
          $type = null;
        }
        
        // set the details for the param, if one is found that is
				foreach ($this->params as $param) {
					if ($param->name == $name) {
						if ($param->type == null) $param->type = $type;
						$param->description = $desc;
						break;
					}
				}
			}
		}
		
		// Do return value
		$return = $this->comment['@return'];
		if ($return == null) $return = $this->comment['@returns'];
		if ($return != null) {
		  $return = array_pop ($return);
		  list ($this->return_type, $this->return_description) = explode(' ', $return, 2);
		}
	}

	public function dump() {
		echo '<div style="border: 1px red solid;">';
		echo $this->visibility . ' ';
		echo $this->name;
		if ($this->abstract) echo '<br>abstract';
		echo '<br>' . $this->description;
		foreach ($this->params as $a) $a->dump();
		echo '</div>';
	}
}

?>
