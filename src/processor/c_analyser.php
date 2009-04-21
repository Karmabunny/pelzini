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
* Contains the {@link CAnalyser} class
*
* @package Parsers
* @author Josh
* @since 0.2
**/

/**
* Analyses the C tokens, and creates a set of ParserItem objects.
**/
class CAnalyser extends Analyser {
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
        case TOKEN_C_PREPROCESSOR:
          break;
          
        case TOKEN_DOCBLOCK:
          $parser_file->applyComment($token->getValue());
          break 2;
          
        default:
          break 2;
      }
      $this->movePosForward();
    }
    
    // Functions
    $this->setPos(0);
    while ($function_open_bracket = $this->findTokenForwards(TOKEN_OPEN_NORMAL_BRACKET)) {
      $open_bracket_pos = $this->getTokenPos();
      $this->setPos($open_bracket_pos);
      
      $parser_function = new ParserFunction();
      $parser_file->functions[] = $parser_function;
      
      // Find the name
      $name = $this->findTokenBackwards(TOKEN_IDENTIFIER, array(TOKEN_CLOSE_CURLY_BRACKET, TOKEN_SEMICOLON));
      if ($name == null) return false;
      $this->setPos($this->getTokenPos());
      $parser_function->name = $name->getValue();
      
      // Find the return type
      $return_type = '';
      $return = $this->findTokenBackwards(array(TOKEN_IDENTIFIER, TOKEN_ASTERIX), array(TOKEN_CLOSE_CURLY_BRACKET, TOKEN_SEMICOLON));
      $this->setPos($this->getTokenPos());
      while ($return != null) {
        $return_type = $return->getValue() . ' ' . $return_type;
        
        $return = $this->findTokenBackwards(array(TOKEN_IDENTIFIER, TOKEN_ASTERIX), array(TOKEN_CLOSE_CURLY_BRACKET, TOKEN_SEMICOLON));
        $this->setPos($this->getTokenPos());
      }
      
      // Set the function return value to the value found
      if ($return_type == '') $return_type = 'int';
      $parser_function->return_type = $return_type;
      
      // Look for a docblock before the function
      $docblock = $this->findTokenBackwards(TOKEN_DOCBLOCK, array(TOKEN_CLOSE_CURLY_BRACKET, TOKEN_SEMICOLON));
      if ($docblock != null) {
        $parser_function->applyComment($docblock->getValue());
      }
      
      // Find the end of the arguments by counting regular brackets
      $depth = 0;
      $find_types = array(
        TOKEN_OPEN_NORMAL_BRACKET,
        TOKEN_CLOSE_NORMAL_BRACKET,
        TOKEN_IDENTIFIER,
        TOKEN_COMMA,
        TOKEN_ASTERIX
      );
      $token = $function_open_bracket;
      $this->setPos($open_bracket_pos);
      $arg_tokens = array();
      while ($token) {
        switch ($token->getType()) {
          case TOKEN_OPEN_NORMAL_BRACKET:
            $depth++;
            break;
            
          case TOKEN_CLOSE_NORMAL_BRACKET:
            $depth--;
            break;
            
          case TOKEN_IDENTIFIER:
          case TOKEN_ASTERIX:
            $arg_tokens[] = ' ' . $token->getValue();
            break;
            
          case TOKEN_COMMA:
            $arg = new ParserArgument();
            $parser_function->args[] = $arg;
            $arg->name = array_pop($arg_tokens);
            
            if (count($arg_tokens) == 0) {
              $arg->type = 'int';
            } else {
              $arg->type = implode('', $arg_tokens);
            }
            
            $arg_tokens = array();
            break;
        }
        
        if ($depth == 0) break;
        
        $token = $this->findTokenForwards($find_types);
        $this->setPos($this->getTokenPos());
      }
      
      // Adds the last (or only) argument
      if (count($arg_tokens) > 0) {
        $arg = new ParserArgument();
        $parser_function->args[] = $arg;
        $arg->name = array_pop($arg_tokens);
        
        if (count($arg_tokens) == 0) {
          $arg->type = 'int';
        } else {
          $arg->type = implode('', $arg_tokens);
        }
      }
        
      // Find the end of the function by counting curly brackets
      $depth = 0;
      $token = $this->findTokenForwards(array(TOKEN_OPEN_CURLY_BRACKET, TOKEN_SEMICOLON));
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
  }
}

?>
