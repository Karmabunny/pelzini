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
* @package Parser model
**/

/**
* Represents a file
**/
class ParserFile extends ParserItem {
  public $name;
  public $description;
  public $package;
  public $functions;
  public $classes;
  public $constants;
  public $source;
  private $comment_applied;
  
  public function __construct() {
    parent::__construct();
    
    $this->functions = array();
    $this->classes = array();
    $this->constants = array();
    $this->package = null;
  }

  /**
  * Applies the contents of a doc-block to this element
  *
  * @param $text The content of the DocBlock
  **/
  public function apply_comment ($text) {
    if ($this->comment_applied) return;
    
    $comment = parse_doc_comment ($text);
    $this->processDocblockTags ($comment);
    
    $this->description = htmlify_text($comment['@summary']);
    
    // set the packages. all packages are forced to have non-space names
    $packages = $comment['@package'];
    if ($packages != null) {
      $this->package = array_pop($packages);
    }
    
    $this->comment_applied = true;
  }
  
  public function dump() {
    echo '<div style="border: 1px black solid;">';
    echo $this->name;
    foreach ($this->functions as $a) $a->dump();
    foreach ($this->classes as $a) $a->dump();
    foreach ($this->constants as $a) $a->dump();
    echo '</div>';
  }
}

?>
