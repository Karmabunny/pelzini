<?php

class ParserVariable {
	public $name;
  public $type;
  public $description;
	public $visibility;

	public function __construct() {
		$this->visibility = 'private';
	}
	
	/**
  * Applies the contents of a doc-block to this element
  *
  * @param $text The content of the DocBlock
  **/
	public function apply_comment ($text) {
		$comment = parse_doc_comment ($text);
		$this->description = $comment['@summary'];
	}

	public function dump() {
		echo '<div style="border: 1px purple solid;">';
		echo $this->visibility . ' ';
		echo $this->name;
		echo '</div>';
	}
}

?>
