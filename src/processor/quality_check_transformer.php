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
* @package Transformers
* @author Josh
* @since 0.2
**/

class QualityCheckTransformer extends Transformer {
  private $offending_items;
  
  /**
  * Transforms the data model before outputting.
  *
  * This transformer generates a report of objects that do not have good enough documentation
  *
  * @param array $parser_model The data model to transform
  * @return array The new data model, or null if there was an error
  **/
  public function transform($parser_model) {
    $this->offending_items = array();
    
    foreach ($parser_model as $item) {
      if ($item instanceof CodeParserItem) {
        $this->check_files($item);
      }
    }
    
    if (count($this->offending_items) == 0) {
      return null;
    }
    
    $report = "The following items do not have a description:\n\n";
    $report .= implode("\n", $this->offending_items);
    
    $document = new ParserDocument();
    $document->name = "Quality check report";
    $document->description = htmlify_text($report);
    $parser_model[] = $document;
    
    return $parser_model;
  }
  
  
  /**
  * Checks that a file has high-enough quality documentation
  **/
  private function check_files($item) {
    $tags = $item->getDocblockTags();
    
    if ($tags['@summary'] == '') $failed = true;
    
    if ($failed) {
      $this->offending_items[] = "File: {$item->name}";
    }
    
    foreach ($item->classes as $sub) {
      $this->check_class($sub);
    }
    
    foreach ($item->functions as $sub) {
      $this->check_function($sub);
    }
  }
  
  /**
  * Checks that a class has high-enough quality documentation
  **/
  private function check_class($item) {
    $tags = $item->getDocblockTags();
    
    if ($tags['@summary'] == '') $failed = true;
    
    if ($failed) {
      $this->offending_items[] = "Class: {$item->name}";
    }
    
    foreach ($item->functions as $sub) {
      $this->check_function($sub, $item->name);
    }
  }
  
  /**
  * Checks that a function has high-enough quality documentation
  **/
  private function check_function($item, $class = null) {
    $tags = $item->getDocblockTags();
    
    if ($tags['@summary'] == '') $failed = true;
    
    if ($failed) {
      $this->offending_items[] = "Function: {$item->name}" . ($class ? " from class {$class}" : '');
    }
  }
}

?>
