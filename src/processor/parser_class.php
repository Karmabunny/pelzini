<?php

class ParserClass {
	public $name;
	public $functions;
	public $variables;
	public $visibility;

	public function __construct() {
		$this->functions = array ();
		$this->variables = array ();
		$this->visibility = 'public';
	}

	public function dump() {
		echo '<div style="border: 1px blue solid;">';
		echo $this->visibility . ' ';
		echo $this->name;
		foreach ($this->variables as $a) $a->dump();
		foreach ($this->functions as $a) $a->dump();
		echo '</div>';
	}
}

?>
