<?php

class ParserVariable {
	public $name;
	public $visibility;

	public function __construct() {
		$this->visibility = 'private';
	}

	public function dump() {
		echo '<div style="border: 1px purple solid;">';
		echo $this->visibility . ' ';
		echo $this->name;
		echo '</div>';
	}
}

?>
