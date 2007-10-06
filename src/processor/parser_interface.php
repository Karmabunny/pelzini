<?php

class ParserInterface {
	public $name;
	public $functions;
	public $extends;
	public $description;

	public function __construct() {
		$this->functions = array ();
		$this->variables = array ();
	}

	public function apply_comment ($text) {
		$comment = parse_doc_comment ($text);
		$this->description = $comment['@summary'];
	}

	public function dump() {
		echo '<div style="border: 1px orange solid;">';
		echo $this->visibility . ' ';
		echo $this->name;
		echo '<br>' . $this->description;
		foreach ($this->variables as $a) $a->dump();
		foreach ($this->functions as $a) $a->dump();
		echo '</div>';
	}
}

?>
