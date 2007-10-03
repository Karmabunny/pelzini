<?php

class ParserFile {
	public $name;
	public $functions;
	public $classes;

	public function __construct() {
		$this->functions = array ();
		$this->classes = array ();
	}

	public function dump() {
		echo '<div style="border: 1px black solid;">';
		echo $this->name;
		foreach ($this->functions as $a) $a->dump();
		foreach ($this->classes as $a) $a->dump();
		echo '</div>';
	}
}

?>
