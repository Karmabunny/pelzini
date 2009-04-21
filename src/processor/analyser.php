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
* Contains the {@link Analyser} class
*
* @package Parsers
* @author Josh
* @since 0.2
**/

/**
* Generic language analyser. Analysers are used to tranform the language-specific tokens into a set of {@link CodeParserItem ParserItems}
**/
class Analyser {
  private $tokens;
  private $pos;
  private $token_pos;
  
  /**
  * Resets the analyser ready for more parsing work
  **/
  public function resetState() {
    $this->tokens = array();
    $this->pos = 0;
    $this->token_pos = 0;
  }
  
  
  /**
  * Tells the analyser what tokens it should use
  **/
  protected function setTokens($tokens) {
    $this->tokens = $tokens;
  }
  
  /**
  * Sets the current position
  **/
  protected function setPos($pos) {
    $this->pos = $pos;
  }
  
  /**
  * Moves the internal token pointer forwards
  *
  * @param $num integer The number of positions to move the pointer forwards
  **/
  protected function movePosForward($num = 1) {
    $this->pos += $num;
  }
  
  /**
  * Moves the internal token pointer backwards
  *
  * @param $num integer The number of positions to move the pointer backwards
  **/
  protected function movePosBackward($num = 1) {
    $this->pos -= $num;
  }
  
  
  /**
  * Returns a token at a specific position
  * If no position is specified, uses the current position
  **/
  protected function getToken($pos = null) {
    if ($pos === null) $pos = $this->pos;
    return $this->tokens[$pos];
  }
  
  /**
  * Returns the current position
  **/
  protected function getPos() {
    return $this->pos;
  }
  
  
  /**
  * Finds a token looking forward from the current position.
  * Searching starts after the current token.
  * The token must be of the type specified
  *
  * @param mixed $token_types A token type constant, or an array of token type constants
  * @param mixed $stop_list Token(s) that should stop the search process
  * @return Token The found token, or null if nothing was found
  **/
  protected function findTokenForwards($token_types, $stop_list = null) {
    if (! is_array($token_types)) $token_types = array($token_types);
    if (! is_array($stop_list)) $stop_list = array($stop_list);
    
    $pos = $this->pos + 1;
    while (true) {
      $tok = $this->tokens[$pos];
      
      if (! $tok or in_array($tok->getType(), $stop_list)) {
        break;
      }
      
      if (in_array($tok->getType(), $token_types)) {
        $this->token_pos = $pos;
        return $tok;
      }
      
      ++$pos;
    }
    
    return null;
  }
  
  /**
  * Finds a token looking backwards from the current position.
  * Searching starts before the current token.
  * The token must be of the type specified
  *
  * @param mixed $token_types A token type constant, or an array of token type constants
  * @param mixed $stop_list Token(s) that should stop the search process
  * @return Token The found token, or null if nothing was found
  **/
  protected function findTokenBackwards($token_types, $stop_list = null) {
    if (! is_array($token_types)) $token_types = array($token_types);
    if (! is_array($stop_list)) $stop_list = array($stop_list);
    
    $pos = $this->pos - 1;
    while (true) {
      $tok = $this->tokens[$pos];
      
      if (! $tok or in_array($tok->getType(), $stop_list)) {
        break;
      }
      
      if (in_array($tok->getType(), $token_types)) {
        $this->token_pos = $pos;
        return $tok;
      }
      
      --$pos;
    }
    
    return null;
  }
  
  /**
  * Gets the position of the last token found using one of the search functions
  **/
  protected function getTokenPos() {
    return $this->token_pos;
  }
}
?>
