<?php

class ParserInterface {
	public $name;
	public $functions;
	public $extends;

	public function __construct() {
		$this->functions = array ();
		$this->variables = array ();
	}

	public function dump() {
		echo '<div style="border: 1px orange solid;">';
		echo $this->visibility . ' ';
		echo $this->name;
		foreach ($this->variables as $a) $a->dump();
		foreach ($this->functions as $a) $a->dump();
		echo '</div>';
	}
}

?>
