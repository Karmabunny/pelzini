<?php

class ParserFunction {
	public $name;
	public $params;
	public $visibility;

	public function __construct() {
		$this->params = array ();
		$this->visibility = 'public';
	}

	public function dump() {
		echo '<div style="border: 1px red solid;">';
		echo $this->visibility . ' ';
		echo $this->name;
		foreach ($this->params as $a) $a->dump();
		echo '</div>';
	}
}

?>
