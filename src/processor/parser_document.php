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
* Contains the {@link ParserDocument} class
*
* @package Parser model
* @author Josh Heidenreich
* @since 0.2
**/

/**
* Represents a function
**/
class ParserDocument extends ParserItem {
  public $name;
  public $description;
  
  
  public function __construct() {
    parent::__construct();
  }
  
  /**
  * Debugging use only
  **/
  public function dump() {
    echo '<div style="border: 1px navy solid;">';
    echo $this->name;
    echo '<br>' . $this->description;
    
    parent::dump();
    echo '</div>';
  }
}

?>
