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
* Stores information about parser items in general
* Stores such information as authors, etc.
*
* @todo Add get/set methods instead of using public variables
**/
abstract class ParserItem {
  public $authors;
  
  /**
  * This constructor must be called by extending classes
  **/
  protected function __construct () {
    $this->authors = array ();
  }
  
  /**
  * Processes general DocBlock tags that should apply to everything
  **/
  protected function processDocblockTags($docblock_tags) {
    
    if (@count ($docblock_tags['@author']) > 0) {
      
      // This regex is for taking an author string, and getting the name (required),
      // email address (optional) and description (optional).
      // The format, simply put, is:
      //    {Name} (<{Email}>)? ({Description})?
      // There is also some extra cleverness, such as you can use a comma, colon or semi-colon
      // to seperate the name part and the description part
      //
      //               | name part  || email address part         || desc part         |
      $expression = '/^((?:[a-z] ?)+)(?:\s*<([-a-z._]+@[-a-z.]+)>)?(?:\s*[,:;]?\s*(.*))?$/si';
      
      foreach ($docblock_tags['@author'] as $author) {
        if (preg_match ($expression, $author, $matches)) {
          $author = new ParserAuthor();
          $author->name = $matches[1];
          $author->email = $matches[2];
          $author->description = $matches[3];
          
          $this->authors[] = $author;
        }
      }
    }
  }
}

?>
