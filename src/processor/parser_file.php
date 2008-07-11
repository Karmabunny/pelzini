<?php

class ParserFile {
	public $name;
	public $description;
	public $package;
	public $functions;
	public $classes;
  public $source;
  
	public function __construct() {
		$this->functions = array();
		$this->classes = array();
		$this->package = null;
	}

  /**
  * Applies the contents of a doc-block to this element
  *
  * @param $text The content of the DocBlock
  **/
	public function apply_comment ($text) {
		$comment = parse_doc_comment ($text);
		$this->description = $comment['@summary'];
		
		// set the packages. all packages are forced to have non-space names
		$packages = $comment['@package'];
		if ($packages != null) {
		  $this->package = array_pop($packages);
		}
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
