<?php

class ParserFile {
	public $name;
	public $description;
	public $packages;
	public $functions;
	public $classes;
  public $source;
  
	public function __construct() {
		$this->functions = array();
		$this->classes = array();
		$this->packages = null;
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
		$package_tags = $comment['@package'];
		if ($package_tags != null) {
		  $this->packages = array();
		  foreach ($package_tags as $package) {
		    $this->packages[] = str_replace(' ', '_', $package);
		  }
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
