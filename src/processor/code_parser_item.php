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
* Contains the {@link CodeParserItem} class
*
* @package Parser model
* @author Josh Heidenreich
* @since 0.1
**/

/**
* Stores information about parser items that are actual source code.
* Typically these parser items have authors and versions, so all of that information
* is stored in this class.
*
* @todo Add get/set methods instead of using public variables
**/
abstract class CodeParserItem extends ParserItem {
  public $authors;
  public $since;
  public $tables;
  
  protected $docblock_tags;
  
  
  /**
  * Processes the docblock tags for a specific item
  **/
  abstract protected function processSpecificDocblockTags($docblock_tags);
  
  
  /**
  * Executes a function for this ParserItem, and all of its children ParserItems
  *
  * The function will be called with a single argument, the ParserItem that it should operate on
  **/
  public function treeWalk($function_name) {
    call_user_func ($function_name, $this);
  }
  
  
  /**
  * This constructor must be called by extending classes
  **/
  protected function __construct () {
    parent::__construct();
    
    $this->docblock_tags = array();
    $this->authors = array();
    $this->since = null;
    $this->tables = array();
  }
  
  
  /**
  * This parses a comment for a specific item
  **/
  public function applyComment ($comment) {
    $this->docblock_tags = parse_doc_comment ($comment);
  }
  
  /**
  * Processes the tags for a specific item
  **/
  public function processTags() {
    $this->processGenericDocblockTags($this->docblock_tags);
    $this->processSpecificDocblockTags($this->docblock_tags);
  }
  
  /**
  * Cascades parent Docblock tags into a child item
  * Only cascades the tags specified in the config
  *
  * @param ParserItem $child The child item to cascade the tags into
  **/
  protected function cascadeTags ($child) {
    global $dpgCascaseDocblockTags;
    if (! isset ($dpgCascaseDocblockTags)) return;
    
    $child_tags = $child->getDocblockTags();
    
    foreach ($dpgCascaseDocblockTags as $cascade_tag) {
      if (! in_array ($cascade_tag, array_keys ($child_tags))) {
        $child_tags[$cascade_tag] = $this->docblock_tags[$cascade_tag];
      }
    }
    
    $child->setDocblockTags($child_tags);
  }
  
  /**
  * Gets the Docblock tags of this item
  **/
  public function getDocblockTags() {
    return $this->docblock_tags;
  }
  
  /**
  * Sets the Docblock tags for this item
  *
  * @param array $tags The new docblock tags.
  **/
  public function setDocblockTags($tags) {
    $this->docblock_tags = $tags;
  }
  
  /**
  * Processes general DocBlock tags that should apply to everything
  **/
  protected function processGenericDocblockTags($docblock_tags) {
    // @author
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
    
    // @since
    if (@count ($docblock_tags['@since']) > 0) {
      $this->since = $docblock_tags['@since'][0];
    }
    
    // @table
    if (@count ($docblock_tags['@table']) > 0) {
      $valid_actions = array('select', 'insert', 'update', 'delete');
      
      foreach ($docblock_tags['@table'] as $table_def) {
        if ($table_def == '') continue;
        
        $parts = explode (' ', $table_def, 3);
        
        $found_action = false;
        foreach ($valid_actions as $action) {
          if (strcasecmp($parts[0], $action) == 0) {
            $found_action = true;
            break;
          }
        }
        
        
        $table = new ParserTable();
        
        if ($found_action) {
          $table->action = strtoupper($parts[0]);
          $table->name = $parts[1];
          $table->description = $parts[2];
          
        } else {
          $table->name = $parts[0];
          $table->description = $parts[1] . ' ' . $parts[2];
        }
        
        $this->tables[] = $table;
      }
    }
  }
  
  /**
  * Debugging use only
  **/
  protected function dump () {
    echo '<br>Authors: '; foreach ($this->authors as $a) $a->dump();
    echo '<br>Since: ', $this->since;
    
    parent::dump();
  }
}

?>
