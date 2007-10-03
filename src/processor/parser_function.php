<?php

class ParserFunction {
	public $name;
	public $params;

	public function __construct() {
		$this->params = array ();
	}

	public function dump() {
		echo '<div style="border: 1px red solid;">';
		echo $this->name;
		foreach ($this->params as $a) $a->dump();
		echo '</div>';
	}
}

?>
