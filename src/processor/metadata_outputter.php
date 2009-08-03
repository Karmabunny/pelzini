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
* This file contains the {@link MetadataOutputter} class
*
* @package Outputters
* @author Josh
* @since 0.3
**/

/**
* Outputs the tree to a metadata file
**/
abstract class MetadataOutputter extends Outputter {
  protected $filename;
  
  /**
  * Sets the full filename to output to.
  **/
  public function set_filename ($filename) {
    $this->filename = $filename;
  }
  
  /**
  * Returns the file extension of the outputted file (e.g. 'xml')
  **/
  abstract function get_ext();
  
  /**
  * Returns the mimetype of the outputted file (e.g. 'text/xml')
  **/
  abstract function get_mimetype();
  
}
?>
