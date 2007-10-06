<?php

class ParserParameter {
	public $name;
	public $type;
	public $description;

	public function dump() {
		echo '<div style="border: 1px green solid;">';
		echo 'Name: ' . $this->name;
		echo '<br>Type: ' . $this->type;
		echo '<br>Description: ' . $this->description;
		echo '</div>';
	}
}

?>
