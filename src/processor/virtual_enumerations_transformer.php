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
* Contains the {@link VirtualEnumerationsTransformer} class
*
* @package Transformers
* @author Josh
* @since 0.3
**/

/**
* This transformer converts constants that start with the same name into an enumeration of that name.
*
* So if a file has the following constants:
*   APP_VERSION
*   ITEM_TYPE_APPLE
*   ITEM_TYPE_ORANGE
*
* The APPLE and ORANGE constants will become a part of the virtual enumeration ITEM_TYPE.
**/
class VirtualEnumerationsTransformer extends Transformer {
  public function __construct () {
    
  }
  
  /**
  * Transforms the data model before outputting.
  *
  * This transformer converts constants that start with the same name into an enumeration of that name.
  *
  * @param array $parser_model The data model to transform
  * @return array The new data model, or null if there was an error
  **/
  public function transform($parser_model) {
    foreach ($parser_model as $item) {
      if ($item instanceof ParserFile) {
        $this->processConstants ($item);
      }
    }
    
    return $parser_model;
  }
  
  /**
  * Processes constants for a specified file
  **/
  private function processConstants (ParserFile $file) {
    usort ($file->constants, 'constant_name_sorter');
    
    reset ($file->constants);
    list ($last_const_id, $last_const) = each ($file->constants);
    
    $enum = null;
    while (list ($constant_id, $constant) = each ($file->constants)) {
      if ($enum) {
        // If the constant starts with the same stuff as the current enum, add it
        if (strncmp ($enum->name, $constant->name, strlen($enum->name)) == 0) {
          $enum->constants[] = $constant;
          unset ($file->constants[$constant_id]);
          
        } else {
          // It doesn't match, so do a check that the constand begins with mostly
          // the same stuff - it can have the last two chars wrong.
          // If it is close, the enum is shrunk to what matches
          $num = $this->numSimilarChars($enum->name, $constant->name);
          if ($num >= 3 and $num >= (strlen($enum->name) - 2)) {
            if ($constant->name[$num - 1] == '_') $num--;
            $enum->name = substr($constant->name, 0, $num);
            $enum->constants[] = $constant;
            unset ($file->constants[$constant_id]);
            
          // Of course if there is no match, we cannot force a match.
          } else {
            $enum = null;
          }
        }
        
      } else {
        // There is no current enum, so check if one can be created by looking at the
        // last constant and the current constant
        $num = $this->numSimilarChars($last_const->name, $constant->name);
        if ($num >= 3) {
          if ($constant->name[$num - 1] == '_') $num--;
          $enum = $this->createVirtualEnum (substr($constant->name, 0, $num));
          $file->enumerations[] = $enum;
          $enum->constants[] = $last_const;
          $enum->constants[] = $constant;
          unset ($file->constants[$last_const_id]);
          unset ($file->constants[$constant_id]);
        }
      }
      
      $last_const = $constant;
      $last_const_id = $constant_id;
    }
  }
  
  /**
  * Creates an enum
  **/
  private function createVirtualEnum ($name) {
    $enum = new ParserEnumeration();
    $enum->name = $name;
    $enum->virtual = 1;
    return $enum;
  }
  
  /**
  * Returns the number of chars in $a that are the same as the chars in $b
  **/
  private function numSimilarChars ($a, $b) {
    $i = 0;
    
    while ($a[$i] == $b[$i]) $i++;
    
    return $i;
  }
}

function constant_name_sorter ($a, $b) {
  return strcasecmp ($a->name, $b->name);
}
?>
