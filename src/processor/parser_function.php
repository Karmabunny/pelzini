<?php

class ParserFunction {
	public $name;
	public $params;
	public $visibility;
	public $abstract;
	public $comment;

	public function __construct() {
		$this->params = array ();
		$this->visibility = 'public';
	}

	public function apply_comment ($text) {
		$this->comment = parse_doc_comment ($text);
	}

	public function dump() {
		echo '<div style="border: 1px red solid;">';
		echo $this->visibility . ' ';
		echo $this->name;
		if ($this->abstract) echo '<br>abstract';
		echo '<br>' . $this->comment;
		foreach ($this->params as $a) $a->dump();
		echo '</div>';
	}
}

?>
