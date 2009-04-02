<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of Pelzini.

Pelzini is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Pelzini is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Pelzini.  If not, see <http://www.gnu.org/licenses/>.
*/


/**
* This file contains the {@link DebugOutputter} class
*
* @package Outputters
* @author Josh
* @since 0.1
**/

/**
* Outputs the tree to the screen
**/
class DebugOutputter extends Outputter {
  
  /**
  * Outputs the entire tree to the screen
  **/
  public function output ($files) {
    global $dpgProjectName;
    
    echo '<style>';
    echo 'div {padding: 5px; margin: 5px;}';
    echo '</style>';
    
    echo "<h1>{$dpgProjectName}</h1>";
    foreach ($files as $file) {
      $file->dump();
    }
    
    return true;
  }
}

?>
