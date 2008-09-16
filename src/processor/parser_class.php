<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of docu.

Docu is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Docu is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with docu.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
* Stores information about a specific class
**/
class ParserClass {
	public $name;
	public $functions;
	public $variables;
	public $visibility;
	public $extends;
	public $implements;
	public $abstract;
	public $description;

  /**
  * Creates this object
  **/
	public function __construct() {
		$this->functions = array ();
		$this->variables = array ();
		$this->implements = array ();
		$this->visibility = 'public';
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
		echo '<div style="border: 1px blue solid;">';
		echo $this->visibility . ' ';
		echo $this->name;

		if ($this->extends) echo ' extends ' . $this->extends;
		if ($this->implements) echo ' implements ' . implode(',', $this->implements);
  
		if ($this->abstract) echo '<br>abstract';

		echo '<br>' . $this->description;

		foreach ($this->variables as $a) $a->dump();
		foreach ($this->functions as $a) $a->dump();
		echo '</div>';
	}
}

?>
