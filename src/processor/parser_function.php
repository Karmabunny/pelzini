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
* Represents a function
**/
class ParserFunction {
  public $name;
  public $params;
  public $visibility;
  public $abstract;
  public $description;
  public $comment;
  public $return_type;
  public $return_description;
  public $static;
  public $final;
  
  
  public function __construct() {
    $this->params = array ();
    $this->visibility = 'public';
    $this->static = false;
    $this->final = false;
  }
  
  public function apply_comment ($text) {
    $this->comment = parse_doc_comment ($text);
  }
  
  public function post_load () {
    $this->description = htmlify_text($this->comment['@summary']);
    
    // Do params
    $params = $this->comment['@param'];
    if ($params != null) {
      foreach ($params as $param_tag) {
        list ($type, $name, $desc) = explode(' ', $param_tag, 3);
        
        // if type was not specified, do some clever stuff
        if ($type[0] == '$') {
          $desc = $name . ' ' . $desc;
          $name = $type;
          $type = null;
        }
        
        // set the details for the param, if one is found that is
        foreach ($this->params as $param) {
          if ($param->name == $name) {
            if ($param->type == null) $param->type = $type;
            $param->description = htmlify_text($desc);
            break;
          }
        }
      }
    }
    
    // Do return value
    $return = $this->comment['@return'];
    if ($return == null) $return = $this->comment['@returns'];
    if ($return != null) {
      $return = array_pop ($return);
      list ($this->return_type, $this->return_description) = explode(' ', $return, 2);
    }
  }

  public function dump() {
    echo '<div style="border: 1px red solid;">';
    echo $this->visibility . ' ';
    echo $this->name;
    if ($this->abstract) echo '<br>abstract';
    if ($this->static) echo '<br>static';
    if ($this->final) echo '<br>static';
    echo '<br>' . $this->description;
    foreach ($this->params as $a) $a->dump();
    echo '</div>';
  }
}

?>
