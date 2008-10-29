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
* Represents a variable
**/
class ParserVariable extends ParserItem {
  public $name;
  public $type;
  public $description;
  public $visibility;
  public $static;
  
  public function __construct() {
    parent::__construct();
    
    $this->visibility = 'private';
    $this->static = false;
  }
  
  /**
  * Applies the contents of a doc-block to this element
  *
  * @param $text The content of the DocBlock
  **/
  public function apply_comment ($text) {
    $comment = parse_doc_comment ($text);
    $this->processDocblockTags ($comment);
    
    $this->description = htmlify_text($comment['@summary']);
  }

  public function dump() {
    echo '<div style="border: 1px purple solid;">';
    echo $this->visibility . ' ';
    echo $this->name;
    if ($this->static) echo '<br>static';
    echo '</div>';
  }
}

?>
