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
* Contains the {@link JavascriptAnalyser} class
*
* @package Parsers
* @author Josh
* @since 0.2
**/

/**
* Analyses the javascript tokens, and creates a set of ParserItem objects.
**/
class JavascriptAnalyser extends Analyser {
  /**
  * Resets any state variables used by this class back to their initial state
  **/
  public function resetState() {
    parent::resetState();
  }
  
  /**
  * Should create ParserItem objects that represent the provided tokens
  * and apply those objects to the ParserFile specified.
  * @return boolean True on success, false on failure
  **/
  public function process($tokens, $parser_file) {
    $this->setTokens($tokens);
    
    // File docblock
    $this->setPos(0);
    while ($token = $this->getToken()) {
      switch ($token->getType()) {
        case TOKEN_COMMENT:
          break;
          
        case TOKEN_DOCBLOCK:
          $parser_file->applyComment($token->getValue());
          break 2;
          
        default:
          break 2;
      }
      $this->movePosForward();
    }
    
    // Process functions
    $this->setPos(0);
    while ($function = $this->findTokenForwards(TOKEN_FUNCTION)) {
      $this->setPos($this->getTokenPos());
      
      $parser_function = new ParserFunction();
      $parser_file->functions[] = $parser_function;
      
      // Find the name
      $name = $this->findTokenForwards(TOKEN_IDENTIFIER);
      if ($name == null) return false;
      $parser_function->name = $name->getValue();
      
      // Look for a docblock
      $docblock = $this->findTokenBackwards(TOKEN_DOCBLOCK, array(TOKEN_CLOSE_CURLY_BRACKET));
      if ($docblock != null) {
        $parser_function->applyComment($docblock->getValue());
      }
      
      // Find the end of the arguments by counting regular brackets
      $depth = 0;
      $find_types = array(
        TOKEN_OPEN_NORMAL_BRACKET,
        TOKEN_CLOSE_NORMAL_BRACKET,
        TOKEN_IDENTIFIER
      );
      $token = $this->findTokenForwards(TOKEN_OPEN_NORMAL_BRACKET);
      $this->setPos($this->getTokenPos());
      while ($token) {
        switch ($token->getType()) {
          case TOKEN_OPEN_NORMAL_BRACKET:
            $depth++;
            break;
            
          case TOKEN_CLOSE_NORMAL_BRACKET:
            $depth--;
            break;
            
          case TOKEN_IDENTIFIER:
            $arg = new ParserArgument();
            $arg->name = $token->getValue();
            $parser_function->args[] = $arg;
            break;
            
        }
        
        if ($depth == 0) break;
        
        $token = $this->findTokenForwards($find_types);
        $this->setPos($this->getTokenPos());
      }
      
      // Find the end of the function by counting curly brackets
      $depth = 0;
      $token = $this->findTokenForwards(TOKEN_OPEN_CURLY_BRACKET);
      $this->setPos($this->getTokenPos());
      while ($token) {
        if ($token->getType() == TOKEN_OPEN_CURLY_BRACKET) $depth++;
        if ($token->getType() == TOKEN_CLOSE_CURLY_BRACKET) $depth--;
        
        if ($depth == 0) break;
        
        $token = $this->findTokenForwards(array(TOKEN_OPEN_CURLY_BRACKET, TOKEN_CLOSE_CURLY_BRACKET));
        $this->setPos($this->getTokenPos());
      }
      
      $parser_function->post_load();
    }
    // End of function processing
    
  }
}

?>
