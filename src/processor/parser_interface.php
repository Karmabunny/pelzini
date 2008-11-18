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
* @author Josh Heidenreich
* @since 0.1
**/

/**
* Represents an interface
**/
class ParserInterface extends ParserItem {
  public $name;
  public $functions;
  public $extends;
  public $visibility;
  public $description;

  public function __construct() {
    parent::__construct();
    
    $this->functions = array ();
    $this->variables = array ();
    $this->visibility = 'public';
  }

  protected function processSpecificDocblockTags($docblock_tags) {
    $this->description = htmlify_text($docblock_tags['@summary']);
  }
  
  /**
  * Cascades Docblock tags into the children that do not have any tags, and then
  * runs processTags() for all of the children items.
  **/
  public function processChildrenItems() {
    foreach ($this->functions as $item) {
      $item->processTags();
      $item->processChildrenItems();
    }
  }
  
  public function dump() {
    echo '<div style="border: 1px orange solid;">';
    echo $this->visibility . ' ';
    echo $this->name;
    echo '<br>' . $this->description;
    foreach ($this->variables as $a) $a->dump();
    foreach ($this->functions as $a) $a->dump();
    
    parent::dump();
    echo '</div>';
  }
}

?>
