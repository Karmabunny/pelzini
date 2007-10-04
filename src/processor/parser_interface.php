<?php

class ParserInterface {
	public $name;
	public $functions;
	public $extends;
	public $comment;

	public function __construct() {
		$this->functions = array ();
		$this->variables = array ();
	}

	public function apply_comment ($text) {
		$this->comment = parse_doc_comment ($text);
	}

	public function dump() {
		echo '<div style="border: 1px orange solid;">';
		echo $this->visibility . ' ';
		echo $this->name;
		echo '<br>' . $this->comment;
		foreach ($this->variables as $a) $a->dump();
		foreach ($this->functions as $a) $a->dump();
		echo '</div>';
	}
}

?>
