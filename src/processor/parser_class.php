<?php

class ParserClass {
	public $name;
	public $functions;
	public $variables;
	public $visibility;
	public $extends;
	public $implements;
	public $abstract;
	public $comment;

	public function __construct() {
		$this->functions = array ();
		$this->variables = array ();
		$this->implements = array ();
		$this->visibility = 'public';
	}

	public function apply_comment ($text) {
		$this->comment = parse_doc_comment ($text);
	}

	public function dump() {
		echo '<div style="border: 1px blue solid;">';
		echo $this->visibility . ' ';
		echo $this->name;

		if ($this->extends) echo ' extends ' . $this->extends;
		if ($this->implements) echo ' implements ' . implode(',', $this->implements);
  
		if ($this->abstract) echo '<br>abstract';

		echo '<br>' . $this->comment;

		foreach ($this->variables as $a) $a->dump();
		foreach ($this->functions as $a) $a->dump();
		echo '</div>';
	}
}

?>
