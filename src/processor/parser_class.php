<?php

class ParserClass {
	public $name;
	public $functions;

	public function __construct() {
		$this->functions = array ();
	}

	public function dump() {
		echo '<div style="border: 1px blue solid;">';
		echo $this->name;
		foreach ($this->functions as $a) $a->dump();
		echo '</div>';
	}
}

?>
