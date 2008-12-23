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
* Some small misc classes
*
* @package Processor
* @author Josh
* @since 0.1
**/

/**
* Represents a function argument
*
* @todo Getters and setters
**/
class ParserArgument extends ParserItem {
  public $name;
  public $type;
  public $description;
  public $default;
  
  public function __construct() {
    parent::__construct();
  }
  
  /**
  * Debugging use only
  **/
  public function dump() {
    echo '<div style="border: 1px green solid;">';
    echo 'Name: ' . $this->name;
    echo '<br>Type: ' . $this->type;
    echo '<br>Default: ' . $this->default;
    echo '<br>Description: ' . $this->description;
    echo '</div>';
  }
}


/**
* Represents an author of a parser item (e.g. a ParserFunction or ParserClass)
*
* @todo Getters and setters
**/
class ParserAuthor extends ParserItem {
  public $name;
  public $email;
  public $description;
  
  public function __construct() {
    parent::__construct();
  }
  
  /**
  * Debugging use only
  **/
  public function dump() {
    echo '<div style="border: 1px green solid;">';
    echo 'Name: ' . $this->name;
    echo '<br>Type: ' . $this->email;
    echo '<br>Description: ' . $this->description;
    echo '</div>';
  }
}


/**
* Represents the contents of a @table tag
* @since 0.2
**/
class ParserTable extends ParserItem {
  public $name;
  public $action;
  public $description;
  
  public function __construct() {
    parent::__construct();
  }
  
  /**
  * Debugging use only
  **/
  public function dump() {
    echo '<div style="border: 1px whitesmoke solid;">';
    echo 'Name: ' . $this->name;
    echo '<br>Action: ' . $this->action;
    echo '<br>Description: ' . $this->description;
    echo '</div>';
  }
}


/**
* This class is used to represent a token that has been tokenised using a Lexer. (e.g. the JavascriptLexer)
* These tokens are used to create various ParserItems, by passing them to an Analyser (e.g. the JavascriptAnalyser)
**/
class Token {
  private $type;
  private $value;
  
  public function __construct($type, $value = null) {
    $this->type = $type;
    $this->value = $value;
  }
  
  /**
  * Gets the type of this token
  **/
  public function getType() {
    return $this->type;
  }
  
  /**
  * Gets the value of this token
  **/
  public function getValue() {
    return $this->value;
  }
  
  /**
  * Uses some PHP cleverness to get the name of the constant
  * that this token referres to.
  * Good for debugging
  **/
  public function getTypeName() {
    $constants = get_defined_constants();
    foreach ($constants as $name => $val) {
      if (strncmp($name, 'TOKEN_', 6) === 0 and $val == $this->type) {
        return $name;
      }
    }
    
    return null;
  }
}


?>
